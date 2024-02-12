<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Canvas
{
    private string $apiKey;
    private string $apiUrl;
    public function __construct()
    {
        $this->apiKey = config('canvas.token');
        $this->apiUrl = config('canvas.host');
    }

    public function get()
    {
        return $this->apiKey;
    }

    public function getCourses(): Response
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Accept' => 'application/json',
        ])->get($this->apiUrl.'/api/v1/courses?per_page=1000');

    }
}
