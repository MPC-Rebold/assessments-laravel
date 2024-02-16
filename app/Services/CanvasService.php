<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CanvasService
{
    private string $apiToken;

    private string $apiUrl;

    public function __construct()
    {
        $this->apiToken = config('canvas.token');
        $this->apiUrl = config('canvas.host');
    }

    /**
     * Send a GET request to the Canvas API
     *
     * @param  $path  string the path to the API endpoint
     * @param  $query  array the query parameters
     * @return Response the response from the API
     */
    public function get(string $path, array $query = []): Response
    {
        return Http::withToken($this->apiToken)
            ->withHeaders([
                'Accept' => 'application/json',
            ])->withQueryParameters(
                ['per_page' => 1000] + $query
            )->get($this->apiUrl . '/api/v1/' . $path);
    }

    /**
     * Get the courses from Canvas
     *
     * @return Response
     */
    public function getCourses(): Response
    {
        return $this->get('courses', ['enrollment_type' => 'teacher']);
    }

    /**
     * Get the enrollments for a course
     *
     * @param int $courseId
     * @return Response
     */
    public function getCourseEnrollments(int $courseId): Response
    {
        return $this->get("courses/{$courseId}/enrollments");
    }

    /**
     * Get the assignments for a course
     *
     * @param int $courseId
     * @return Response
     */
    public function getCourseAssignments(int $courseId): Response
    {
        return $this->get("courses/{$courseId}/assignments");
    }
}
