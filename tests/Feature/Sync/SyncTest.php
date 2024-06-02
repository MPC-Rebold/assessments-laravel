<?php

use App\Services\CanvasService;

uses()->group('sync');

test('CANVAS_API_TOKEN is valid', function () {
    expect(CanvasService::isTokenValid())->toBeTrue();
});
