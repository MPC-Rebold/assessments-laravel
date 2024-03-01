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

Route::view('admin/masters/{masterId}', 'master.edit')
    ->middleware(['auth', 'admin'])
    ->name('master.edit');

Route::view('admin/masters/{masterId}/courses/{courseId}/edit', 'course.edit')
    ->middleware(['auth', 'admin'])
    ->name('course.edit');


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('courses', 'course.index')
    ->middleware(['auth'])
    ->name('course.index');

Route::view('courses/{courseId}', 'course.show')
    ->middleware(['auth', 'enrolled'])
    ->name('course.show');

Route::view('courses/{courseId}/assessment/{assessmentId}', 'assessment.show')
    ->middleware(['auth', 'enrolled'])
    ->name('assessment.show');

Route::view('admin/masters/{masterId}', 'master.edit')
    ->middleware(['auth', 'admin'])
    ->name('master.edit');

Route::view('admin/students', 'student.index')
    ->middleware(['auth', 'admin'])
    ->name('student.index');

require __DIR__.'/auth.php';
