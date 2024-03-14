<?php

namespace App\Services;

use App\Models\AssessmentCourse;
use App\Models\User;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

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
     * @param  $path  string the path to the API endpoint
     * @param  $query  array the query parameters
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
     * Get all the courses from Canvas where the user is a teacher
     *
     * @return Response
     */
    public static function getCourses(): Response
    {
        return self::get('courses', ['enrollment_type' => 'teacher']);
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
     * @param int $courseId
     * @param int $assignmentId
     * @param array $data
     * @return Response
     */
    public static function editAssignment(int $courseId, int $assignmentId, array $data): Response
    {
        return self::put("courses/$courseId/assignments/$assignmentId", ['assignment' => $data]);
    }

    /**
     * @throws Exception if the AssessmentCourse is not found
     */
    public static function gradeAssignment(int $courseId, int $assignmentId, ?int $userId): Response
    {
        $assessmentCourse = AssessmentCourse::where('course_id', $courseId)->where('assessment_canvas_id', $assignmentId)->first();

        if (! $assessmentCourse) {
            throw new Exception("AssessmentCourse not found for course $courseId and assignment $assignmentId");
        }

        $is_specification = $assessmentCourse->course->specification_grading;
        $threshold = $assessmentCourse->course->specification_grading_threshold;

        if (isset($userId)) {
            $user = User::find($userId);
            $grade = $assessmentCourse->gradeForUser($user);

            return self::put(
                "courses/$courseId/assignments/$assignmentId/submissions/$userId",
                ['submission' => [
                    'posted_grade' => $grade,
                ]]
            );
        }

        $users = $assessmentCourse->course->users;
        $gradeData = [];

        foreach ($users as $user) {
            $grade = $assessmentCourse->gradeForUser($user);

            if ($is_specification) {
                $grade = $grade >= $threshold ? 1 : 0;
            }
        }

        return self::put(
            "courses/$courseId/assignments/$assignmentId/submissions/update_grades",
            ['grade_data' => $gradeData]
        );
    }

    public function assignmentGradingOption(int $courseId, int $assignmentId, string $option): Response
    {
        return self::put("courses/$courseId/assignments/$assignmentId", ['assignment' => ['grading_type' => $option]]);
    }
}
