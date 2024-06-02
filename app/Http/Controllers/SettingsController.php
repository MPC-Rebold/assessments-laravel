<?php

namespace App\Http\Controllers;

use App\Models\Settings;

class SettingsController extends Controller
{
    public function __invoke()
    {
        $settings = Settings::first();

        if (! $settings) {
            abort(404);
        }

        return response()->json($settings->toArray());
    }
}
