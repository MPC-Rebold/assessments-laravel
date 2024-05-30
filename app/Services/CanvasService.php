<?php

namespace App\Services;

use App\Models\AssessmentCourse;
use App\Models\QuestionUser;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * See https://canvas.instructure.com/doc/api/
 */
class CanvasService
{
    private static string $apiToken;

    private static string $apiUrl;

    public static function initialize(): void
    {
        self::$apiToken = config('canvas.token');
        self::$apiUrl = config('canvas.host');
    }

    /**
     * Send a GET request to the Canvas API
     *
     * @param $path string the path to the API endpoint
     * @param $query array the query parameters
     * @return Response the response from the API
     */
    public static function get(string $path, array $query = []): Response
    {
        self::initialize();

        return Http::withToken(self::$apiToken)
            ->withHeaders([
                'Accept' => 'application/json',
            ])->withQueryParameters(
                ['per_page' => 1000] + $query
            )->get(self::$apiUrl . '/api/v1/' . $path);
    }

    /**
     * Send a PUT request to the Canvas API
     *
     * @param string $path
     * @param array $data
     * @return Response
     */
    public static function put(string $path, array $data): Response
    {
        self::initialize();

        return Http::withToken(self::$apiToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->put(self::$apiUrl . '/api/v1/' . $path, $data);
    }

    /**
     * Send a POST request to the Canvas API
     *
     * @param string $path
     * @param array $data
     * @return Response
     */
    public static function post(string $path, array $data): Response
    {
        self::initialize();

        return Http::withToken(self::$apiToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(self::$apiUrl . '/api/v1/' . $path, $data);
    }

    /**
     * Get the API_TOKEN holder's information from Canvas
     *
     * @return Response
     */
    public static function getSelf(): Response
    {
        return self::get('users/self');
    }

    /**
     * Get all the courses from Canvas where the user is a teacher and the course is a favorite
     *
     * @return array of Canvas courses
     */
    public static function getCourses(): array
    {
        $teacherCourses = self::get(
            'courses',
            ['enrollment_type' => 'teacher', 'include[]' => 'favorites']
        );

        return array_filter($teacherCourses->json(), (fn ($course) => $course['is_favorite']));
    }

    /**
     * Get the enrollments for a course
     *
     * @param int $courseId
     * @return Response
     */
    public static function getCourseEnrollments(int $courseId): Response
    {
        return self::get("courses/$courseId/enrollments");
    }

    /**
     * Get the assignments for a course
     *
     * @param int $courseId
     * @return Response
     */
    public static function getCourseAssignments(int $courseId): Response
    {
        return self::get("courses/$courseId/assignments");
    }

    /**
     * Edit an assignment
     *
     * @param AssessmentCourse $assessmentCourse
     * @param array $data
     * @return Response
     */
    public static function editAssignment(AssessmentCourse $assessmentCourse, array $data): Response
    {
        $courseId = $assessmentCourse->course->id;
        $assignmentId = $assessmentCourse->assessment_canvas_id;

        return self::put("courses/$courseId/assignments/$assignmentId", ['assignment' => $data]);
    }

    /**
     * Grade an assessment for a specific user
     *
     * @param AssessmentCourse $assessmentCourse the AssessmentCourse to grade
     * @param User $user the user to grade the assessment for
     * @return Response the response from the API
     */
    public static function gradeAssessmentForUser(AssessmentCourse $assessmentCourse, User $user): Response
    {
        $grade = $assessmentCourse->gradeForUser($user);
        $courseId = $assessmentCourse->course->id;
        $assignmentId = $assessmentCourse->assessment_canvas_id;

        return self::put(
            "courses/$courseId/assignments/$assignmentId/submissions/{$user->canvas->canvas_id}",
            [
                'submission' => [
                    'posted_grade' => $grade,
                ],
                'comment' => [
                    'text_comment' => self::assessmentGradeCommentForUser($assessmentCourse, $user, $grade),
                ],
            ]
        );
    }

    /**
     * Return the comment for the grade of an assessment for a specific user
     *
     * @param AssessmentCourse $assessmentCourse the AssessmentCourse to grade
     * @param User $user the user to grade the assessment for
     * @param int|string $grade the grade for the user
     * @return string the comment for the grade
     */
    private static function assessmentGradeCommentForUser(AssessmentCourse $assessmentCourse, User $user, int|string $grade): string
    {
        $comment = '';
        foreach ($assessmentCourse->assessment->questions as $question) {
            $isCorrect = QuestionUser::where(
                [
                    'user_id' => $user->id,
                    'question_id' => $question->id,
                    'course_id' => $assessmentCourse->course->id,
                    'is_correct' => true,
                ]
            )->exists();
            $comment .= "$question->number: " . ($isCorrect ? '✔' : '✘') . ', ';
        }

        $comment .= "Grade: $grade";

        return $comment;
    }

    /**
     * Grade an assessment for all users in the course
     *
     * @param AssessmentCourse $assessmentCourse the AssessmentCourse to grade
     * @return Response the response from the API
     */
    public static function gradeAssessment(AssessmentCourse $assessmentCourse): Response
    {
        $users = $assessmentCourse->course->users;

        $courseId = $assessmentCourse->course->id;
        $assignmentId = $assessmentCourse->assessment_canvas_id;

        $gradeData = [];
        foreach ($users as $user) {
            $grade = $assessmentCourse->gradeForUser($user);
            $userId = $user->canvas->canvas_id;

            $gradeData[$userId] = [
                'posted_grade' => $grade,
            ];
        }

        return self::post(
            "courses/$courseId/assignments/$assignmentId/submissions/update_grades",
            [
                'grade_data' => $gradeData,
            ]
        );
    }

    /**
     * Set the maximum points for an assessment depending on the course's grading type
     *
     *
     * @param AssessmentCourse $assessmentCourse the AssessmentCourse to set the maximum points for
     */
    public static function setMaxPoints(AssessmentCourse $assessmentCourse): Response
    {
        $is_specification = $assessmentCourse->course->specification_grading;

        return self::editAssignment($assessmentCourse,
            [
                'points_possible' => $is_specification ? 0 : $assessmentCourse->assessment->questionCount(),
                'grading_type' => $is_specification ? 'pass_fail' : 'points',
            ]
        );
    }

    /**
     * Regrades the given assessment courses
     *
     * @param Collection $assessmentCourses the assessment courses to regrade
     *
     * @throws Exception if any regrade fails
     */
    public static function regradeAssessmentCourses(Collection $assessmentCourses): void
    {
        foreach ($assessmentCourses as $assessmentCourse) {
            if ($assessmentCourse->assessment_canvas_id === -1 || ! $assessmentCourse->course->master_id) {
                continue;
            }

            self::regradeAssessmentCourse($assessmentCourse, false);
        }
    }

    /**
     * Regrades the given assessment course
     *
     * @param AssessmentCourse $assessmentCourse
     * @param bool $regradePastDue whether to regrade past due assessments
     * @return void
     *
     * @throws Exception if regrade fails
     */
    public static function regradeAssessmentCourse(AssessmentCourse $assessmentCourse, bool $regradePastDue = true): void
    {
        if (! $regradePastDue && $assessmentCourse->due_at && Carbon::parse($assessmentCourse->due_at)->isPast()) {
            return;
        }

        CanvasService::setMaxPoints($assessmentCourse);
        $gradeAssessmentResponse = CanvasService::gradeAssessment($assessmentCourse);

        if ($gradeAssessmentResponse->status() !== 200) {
            throw new Exception('Failed to grade assessment');
        }
    }
}
