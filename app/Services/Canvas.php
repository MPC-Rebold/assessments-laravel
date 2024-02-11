<?php

namespace App\Services;

class Canvas
{
    private string $apiKey;

    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('canvas.token');
        $this->apiUrl = config('canvas.host');
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }
}
