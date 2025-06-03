<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Models\AssessmentCourse;
use App\Models\QuestionUser;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Promise\Promise;
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
     * @param bool $async
     * @return Response|Promise the response from the API
     */
    public static function get(string $path, array $query = [], bool $async = false): Response|Promise
    {
        self::initialize();

        if ($async) {
            $http = Http::async()->withToken(self::$apiToken);
        } else {
            $http = Http::withToken(self::$apiToken);
        }

        return $http
            ->withHeaders([
                'Accept' => 'application/json',
            ])->withQueryParameters(
                ['per_page' => 10_000] + $query
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
    public static function post(string $path, array $data = []): Response
    {
        self::initialize();

        return Http::withToken(self::$apiToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post(self::$apiUrl . '/api/v1/' . $path, $data);
    }

    /**
     * Awaits an async Canvas API request
     *
     * @throws Exception
     */
    public static function await(Response $response): Response
    {
        if ($response->status() !== 200) {
            throw new Exception('Failed to await non OK response');
        }

        $url = $response->json()['url'];
        $urlPath = str_replace('/api/v1/', '', parse_url($url, PHP_URL_PATH));

        $startTimer = microtime(true);
        $timeout = 30;

        while (microtime(true) - $startTimer < $timeout) {
            $response = self::get($urlPath);

            if ($response->json()['workflow_state'] === 'completed') {
                return $response;
            }
        }

        throw new UserException('Timeout');
    }

    /**
     * Send a DELETE request to the Canvas API
     *
     * @param string $path
     * @return Response
     */
    private static function delete(string $path): Response
    {
        self::initialize();

        return Http::withToken(self::$apiToken)
            ->withHeaders([
                'Accept' => 'application/json',
            ])->delete(self::$apiUrl . '/api/v1/' . $path);
    }

    /**
     * @return bool whether the CANVAS_API_TOKEN is valid
     */
    public static function isTokenValid(): bool
    {
        return self::get('users/self')->status() === 200;
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
        $courses = [];

        $params = [
            'enrollment_type' => 'teacher',
            'include[]' => 'favorites',
            'page' => 1,
        ];

        while (true) {
            $response = self::get('courses', $params);
            $courses = array_merge($courses, $response->json());

            $link = $response->header('link') ?: '';
            if (str_contains($link, 'rel="next"')) {
                $params['page']++;
            } else {
                break;
            }
        }

        return array_filter($courses, fn ($c) => $c['is_favorite']);
    }

    /**
     * Gets an assignment from a course
     *
     * @param int $courseId
     * @param int $assessmentId
     * @return Response
     */
    public static function getAssignment(int $courseId, int $assessmentId): Response
    {
        return self::get("courses/$courseId/assignments/$assessmentId");
    }

    /**
     * Get the enrollments for a course
     *
     * @param int $courseId
     * @param bool $async
     * @return Response|Promise
     */
    public static function getCourseEnrollments(int $courseId, bool $async = false): Response|Promise
    {
        return self::get("courses/$courseId/enrollments", async: $async);
    }

    /**
     * Get the assignments for a course
     *
     * @param int $courseId
     * @param bool $async
     * @return Response|Promise
     */
    public static function getCourseAssignments(int $courseId, bool $async = false): Response|Promise
    {
        return self::get("courses/$courseId/assignments", async: $async);
    }

    /**
     * Edit an assignment
     *
     * @param AssessmentCourse|array $assessmentCourse the AssessmentCourse to edit or an array with the course id and assignment id
     * @param array $data
     * @return Response
     */
    public static function editAssignment(AssessmentCourse|array $assessmentCourse, array $data): Response
    {
        if (is_array($assessmentCourse)) {
            $courseId = $assessmentCourse[0];
            $assignmentId = $assessmentCourse[1];
        } else {
            $courseId = $assessmentCourse->course->id;
            $assignmentId = $assessmentCourse->assessment_canvas_id;
        }

        return self::put("courses/$courseId/assignments/$assignmentId", ['assignment' => $data]);
    }

    public static function favoriteCourse(int $courseId, bool $unfavorite = false): Response
    {
        if ($unfavorite) {
            return self::delete("users/self/favorites/courses/$courseId");
        }

        return self::post("users/self/favorites/courses/$courseId");
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
     * Get the grade for a specific user in an assessment of a course
     *
     * @param int $courseId the course id
     * @param int $assignmentId the assignment id
     * @param int $userId the user id
     * @return Response the response from the API
     */
    public static function getGrade(int $courseId, int $assignmentId, int $userId): Response
    {
        return self::get("courses/$courseId/assignments/$assignmentId/submissions/$userId");
    }

    /**
     * Grade an assessment for all users in the course
     *
     * @param AssessmentCourse $assessmentCourse the AssessmentCourse to grade
     * @param bool $reset whether to reset the grades
     * @return Response the response from the API
     */
    public static function gradeAssessmentCourseForAllUsers(AssessmentCourse $assessmentCourse, bool $reset = false): Response
    {
        $users = $assessmentCourse->course->users;

        $courseId = $assessmentCourse->course->id;
        $assignmentId = $assessmentCourse->assessment_canvas_id;

        $gradeData = [];
        foreach ($users as $user) {
            if ($reset) {
                $grade = -1;
            } else {
                $grade = $assessmentCourse->gradeForUser($user);
            }

            $userId = $user->canvas->canvas_id;

            $gradeData[$userId] = [
                'posted_grade' => $grade,
            ];
        }

        return self::post("courses/$courseId/assignments/$assignmentId/submissions/update_grades",
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
                'points_possible' => $is_specification ? 1 : $assessmentCourse->assessment->questionCount(),
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

            if ($assessmentCourse->course->users->isEmpty()) {
                continue;
            }

            if ($assessmentCourse->due_at && Carbon::parse($assessmentCourse->due_at)->isPast()) {
                continue;
            }

            self::regradeAssessmentCourse($assessmentCourse);
        }
    }

    /**
     * Regrades the given assessment course
     *
     * @param AssessmentCourse $assessmentCourse
     * @return void
     *
     * @throws Exception if regrade fails
     */
    public static function regradeAssessmentCourse(AssessmentCourse $assessmentCourse): void
    {
        CanvasService::setMaxPoints($assessmentCourse);

        try {
            $resetRequest = CanvasService::gradeAssessmentCourseForAllUsers($assessmentCourse, reset: true);
            CanvasService::await($resetRequest);
        } catch (UserException) {
        } catch (Exception $e) {
            CanvasService::gradeAssessmentCourseForAllUsers($assessmentCourse);
            throw new Exception("Failed to reset grades for assessment course: $e");
        }

        try {
            $regradeRequest = CanvasService::gradeAssessmentCourseForAllUsers($assessmentCourse);
            CanvasService::await($regradeRequest);
        } catch (UserException) {
        }
    }
}
