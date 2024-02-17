<?php

use App\Http\Controllers\Auth\ProviderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'dashboard')
    ->middleware(['auth']);

Route::get('/auth/{provider}/redirect', [ProviderController::class, 'redirect'])->name('auth.redirect');

Route::get('auth/{provider}/callback', [ProviderController::class, 'callback']);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('admin', 'admin')
    ->middleware(['auth', 'admin'])
    ->name('admin');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('courses', 'courses')
    ->middleware(['auth'])
    ->name('courses');

Route::view('courses/{courseId}', 'course-show')
    ->middleware(['auth', 'enrolled'])
    ->name('course');

Route::view('courses/{courseId}/assessment/{assessmentId}', 'assessment')
    ->middleware(['auth', 'enrolled'])
    ->name('assessment');

require __DIR__.'/auth.php';
