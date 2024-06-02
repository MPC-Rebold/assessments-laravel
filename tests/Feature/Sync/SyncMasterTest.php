<?php

use App\Services\CanvasService;

test('CANVAS_API_TOKEN is valid', function () {
    expect(CanvasService::isTokenValid())->toBeTrue();
});
