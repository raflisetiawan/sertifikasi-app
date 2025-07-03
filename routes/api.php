<?php

use App\Http\Controllers\Admin\CourseBenefitController;
use App\Http\Controllers\Admin\ModuleManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CodeCheckController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ModuleLearningController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentHistoryController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ResendEmailVerificationController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\ZoomLinkController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/signup', [AuthController::class, 'sign_up']);
Route::post('/signin', [AuthController::class, 'sign_in']);
Route::post('/signout', [AuthController::class, 'sign_out']);
Route::post('/auth/refresh', [AuthController::class, 'refreshToken']);

Route::get('auth/google', [AuthController::class, 'googleRedirect']);
Route::get('auth/google/callback', [AuthController::class, 'googleCallback']);

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
    Route::get('/user/courses', [RegistrationController::class, 'getUserCourses']);

    Route::get('/courses/{id}/with-materials', [CourseController::class, 'getCourseWithMaterials']);
    Route::get('/courses/with-zoom-link', [CourseController::class, 'getCourseTableWithZoomLink']);
    Route::get('/courses/name-and-id', [CourseController::class, 'getIdAndNameCourse']);


    Route::get('/user-profile/{id}', [UserProfileController::class, 'show']);
    Route::patch('/user-profile/{id}', [UserProfileController::class, 'update']);
    Route::delete('/user-profile/{id}/remove-image', [UserProfileController::class, 'removeImage']);


    Route::post('payments/create', [PaymentController::class, 'createTransaction']);
    Route::get('payments/{registration}', [PaymentController::class, 'getPaymentStatus']);

    Route::get('/user/dashboard', [UserDashboardController::class, 'index']);
    Route::get('/user/certificates', [UserDashboardController::class, 'getCertificates']);
    Route::get('/user/courses', [CourseEnrollmentController::class, 'index']);
    Route::get('/user/courses/{id}', [CourseEnrollmentController::class, 'show']);

    Route::get('/user/profile', [UserProfileController::class, 'getProfile']);
    Route::put('/user/profile', [UserProfileController::class, 'updateProfile']);
    Route::put('/user/{id}/update-image', [UserProfileController::class, 'updateImage']);

    Route::get('/user/payments', [PaymentHistoryController::class, 'index']);

    Route::get('/enrollments/{enrollment}/modules/{module}', [ModuleLearningController::class, 'show']);
    Route::post('/enrollments/{enrollment}/modules/{module}/progress', [ModuleLearningController::class, 'updateProgress']);
    Route::get(
        '/enrollments/{enrollment}/modules/{module}/contents/{content}/start-quiz',
        [ModuleLearningController::class, 'startQuiz']
    );

    Route::post(
        '/enrollments/{enrollment}/modules/{module}/contents/{content}/submit-quiz',
        [ModuleLearningController::class, 'submitQuiz']
    );


    Route::post(
        '/enrollments/{enrollment}/modules/{module}/contents/{content}/submit-assignment',
        [ModuleLearningController::class, 'submitAssignment']
    );

    Route::get(
        '/enrollments/{enrollment}/modules/{module}/contents/{content}/start-practice',
        [ModuleLearningController::class, 'startPractice']
    );

    Route::post(
        '/enrollments/{enrollment}/modules/{module}/contents/{content}/submit-practice',
        [ModuleLearningController::class, 'submitPractice']
    );

    Route::get('/enrollments/{enrollment}/contents/{content}/quiz-attempt',
        [ModuleLearningController::class, 'getQuizAttempt']
    );

    // Live Sessions
    Route::apiResource('live-sessions', \App\Http\Controllers\Api\LiveSessionController::class);
    Route::get('courses/{courseId}/live-sessions', [\App\Http\Controllers\Api\LiveSessionController::class, 'getLiveSessionsByCourse']);
});
// Route::prefix('payments')->group(function () {
//     Route::post('callback', [PaymentController::class, 'handleCallback']);
//     Route::post('status/update', [PaymentController::class, 'updatePaymentStatus']);
// });

Route::post('payments/callback', [PaymentController::class, 'handleCallback'])
    ->withoutMiddleware(['auth:sanctum', 'throttle:api', 'auth', 'auth:api'])
    ->name('payments.callback');  // Add route name

Route::post('payments/status/update', [PaymentController::class, 'updatePaymentStatus']);

// Routes that do not require authentication
Route::apiResource('contacts', ContactController::class)->except(['destroy']);
Route::get('/course', [CourseController::class, 'index']);
Route::get('courses/name/{id}', [CourseController::class, 'getCourseNameById']);
Route::get('/course/{id}', [CourseController::class, 'show']);
Route::get('courses/{id}/related', [CourseController::class, 'relatedCourse']);
Route::get('/course/{id}/with-modules', [CourseController::class, 'getCourseWithModules']);

Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/faqs/{faq}', [FaqController::class, 'show']);

Route::get('/trainers', [TrainerController::class, 'index']);
Route::get('/starred-trainers', [TrainerController::class, 'starredTrainers']);
Route::get('/trainers/{trainer}', [TrainerController::class, 'show']);
Route::get('/trainers/qualification/{qualification}/{id}', [TrainerController::class, 'trainersWithSameQualification']);


Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'can:delete-contact,contact']);

Route::get('course/{courseId}/course_benefits', [CourseBenefitController::class, 'getByCourse']);
