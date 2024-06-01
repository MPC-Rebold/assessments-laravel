<?php

use App\Services\CanvasService;

test('CANVAS_API_TOKEN is valid', function () {
    $status = CanvasService::getSelf()->status();
    expect($status)->toBe(200);
});
