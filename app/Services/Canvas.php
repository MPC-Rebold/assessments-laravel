<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Canvas
{
    private string $apiToken;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiToken = config('canvas.token');
        $this->apiUrl = config('canvas.host');
    }

    /**
     * Get the courses from Canvas
     *
     * @return array
     */
    public function getCourses(): array
    {
        $response =  Http::withToken($this->apiToken)
            ->withHeaders([
                'Accept' => 'application/json',
            ])->withQueryParameters([
                'per_page' => 1000,
                'enrollment_type' => 'teacher',
            ])->get($this->apiUrl . '/api/v1/courses');

        return $response->json();
    }
}
