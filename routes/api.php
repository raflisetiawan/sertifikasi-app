<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'getUserWithRole']);

Route::middleware('auth:sanctum', 'can:create,App\Models\Course')->group(function () {
    Route::post('/course', [CourseController::class, 'store']);
    Route::delete('/course/{id}', [CourseController::class, 'destroy']);
    Route::patch('/course/{id}', [CourseController::class, 'update']);
});

Route::get('/course', [CourseController::class, 'index']);
Route::get('/course/{id}', [CourseController::class, 'show']);

// Route::apiResource('/course', CourseController::class);
Route::post('/signup', [AuthController::class, 'sign_up']);
Route::post('/signin', [AuthController::class, 'sign_in']);
Route::post('/signout', [AuthController::class, 'sign_out']);
Route::apiResource('contacts', ContactController::class);

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Resend link to verify email
Route::post('/email/verify/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');
