<?php

use App\Http\Controllers\Admin\ModuleManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CodeCheckController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ResendEmailVerificationController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\ZoomLinkController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/signup', [AuthController::class, 'sign_up']);
Route::post('/signin', [AuthController::class, 'sign_in']);
Route::post('/signout', [AuthController::class, 'sign_out']);

Route::post('password/email', ForgotPasswordController::class);
Route::post('password/code/check', CodeCheckController::class);
Route::post('password/reset', ResetPasswordController::class);
Route::post('password/change', ChangePasswordController::class);

// Email verification
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verify/resend', [ResendEmailVerificationController::class, 'verify'])
    ->middleware(['throttle:6,1'])
    ->name('verification.send');

// Routes that require admin authentication
Route::middleware(['auth:sanctum', AdminMiddleware::class])
    ->group(base_path('routes/admin/api.php'));

// Routes that require authentication
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'getUserWithRole']);

    Route::get('/materials', [MaterialController::class, 'index']);
    Route::get('/materials/{material}', [MaterialController::class, 'show']);


    Route::post('/registration', [RegistrationController::class, 'store']);
    Route::get('/user/course/{id}', [RegistrationController::class, 'getUserCourses']);

    Route::get('/courses/{id}/with-materials', [CourseController::class, 'getCourseWithMaterials']);
    Route::get('/courses/with-zoom-link', [CourseController::class, 'getCourseTableWithZoomLink']);
    Route::get('/courses/name-and-id', [CourseController::class, 'getIdAndNameCourse']);


    Route::get('/user-profile/{id}', [UserProfileController::class, 'show']);
    Route::patch('/user-profile/{id}', [UserProfileController::class, 'update']);
    Route::delete('/user-profile/{id}/remove-image', [UserProfileController::class, 'removeImage']);

    Route::post('/payments/create', [PaymentController::class, 'createTransaction']);
    Route::post('payments/update-status', [PaymentController::class, 'updatePaymentStatus']);
});
Route::post('/payments/callback', [PaymentController::class, 'handleCallback']);

// Routes that do not require authentication
Route::apiResource('contacts', ContactController::class)->except(['destroy']);
Route::get('/course', [CourseController::class, 'index']);
Route::get('courses/name/{id}', [CourseController::class, 'getCourseNameById']);
Route::get('/course/{id}', [CourseController::class, 'show']);
Route::get('courses/{id}/related', [CourseController::class, 'relatedCourse']);

Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/faqs/{faq}', [FaqController::class, 'show']);

Route::get('/trainers', [TrainerController::class, 'index']);
Route::get('/starred-trainers', [TrainerController::class, 'starredTrainers']);
Route::get('/trainers/{trainer}', [TrainerController::class, 'show']);
Route::get('/trainers/qualification/{qualification}/{id}', [TrainerController::class, 'trainersWithSameQualification']);


Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'can:delete-contact,contact']);
