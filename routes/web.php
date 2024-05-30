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

Route::prefix('auth')->group(function () {
    Route::get('/{provider}/redirect', [ProviderController::class, 'redirect'])->name('auth.redirect');
    Route::get('/{provider}/callback', [ProviderController::class, 'callback']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect('/dashboard');
    });
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Route::view('courses', 'course.index')->name('course.index');

    Route::middleware('enrolled')->group(function () {
        Route::view('courses/{courseId}', 'course.show')->name('course.show');
        Route::view('courses/{courseId}/assessment/{assessmentId}', 'assessment.show')->middleware('active')->name('assessment.show');
    });

    Route::middleware('admin')->group(function () {
        Route::view('admin', 'admin')->name('admin');
        Route::view('admin/masters/{masterId}', 'master.edit')->name('master.edit');
        Route::view('admin/masters/{masterId}/courses/{courseId}', 'course.edit')->name('course.edit');
        Route::view('admin/masters/{masterId}/assessments/{assessmentId}', 'assessment.edit')->name('assessment.edit');
        Route::view('admin/users', 'user.index')->name('user.index');
        Route::view('admin/users/{userId}', 'user.show')->name('user.show');
        Route::view('admin/users/{userId}/grades/{assessmentId}', 'user.grade.show')->name('user.grade.show');
    });
});

require __DIR__ . '/auth.php';
