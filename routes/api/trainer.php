<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrainerDashboardController;
use App\Http\Controllers\Api\TrainerModuleController;
use App\Http\Controllers\Trainer\TrainerCourseController;
use App\Http\Controllers\Trainer\TrainerModuleContentController;
use App\Http\Controllers\Trainer\TrainerEnrollmentController;
use App\Http\Controllers\Trainer\TrainerForumController;
use App\Http\Controllers\Trainer\TrainerAssignmentController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [TrainerDashboardController::class, 'index']);
    Route::get('/modules/{module}', [TrainerModuleController::class, 'show']);

    Route::get('/courses', [TrainerCourseController::class, 'index']);
    Route::get('/courses/{course}/modules', [TrainerCourseController::class, 'showModules']);
    Route::get('/modules/{module}/contents', [TrainerModuleContentController::class, 'index']);
    Route::get('/enrollments', [TrainerEnrollmentController::class, 'index']);
    Route::get('/enrollments/{enrollment}/progress', [TrainerEnrollmentController::class, 'showProgress']);
    Route::get('/courses/{course}/enrollments', [TrainerEnrollmentController::class, 'getCourseEnrollments']);

    // Forum Routes for Trainers
    Route::get('/courses/{course}/forum', [TrainerForumController::class, 'index']);
    Route::get('/forums/{forum}', [TrainerForumController::class, 'show']);
    Route::post('/forums/{forum}/threads', [TrainerForumController::class, 'storeThread']);
    Route::post('/threads/{thread}/posts', [TrainerForumController::class, 'storePost']);
    Route::get('/threads/{thread}', [TrainerForumController::class, 'showThread']);

    // Assignment Grading Routes for Trainers
    Route::get('/enrollments/{enrollment}/assignments/{moduleContent}/submission', [TrainerAssignmentController::class, 'showSubmission']);
    Route::post('/enrollments/{enrollment}/assignments/{moduleContent}/grade', [TrainerAssignmentController::class, 'gradeSubmission']);
});
