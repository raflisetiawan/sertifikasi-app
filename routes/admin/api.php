<?php

use App\Http\Controllers\Admin\AssignmentManagementController;
use App\Http\Controllers\Admin\CourseBenefitController;
use App\Http\Controllers\Admin\FileManagementController;
use App\Http\Controllers\Admin\ModuleConceptManagementController;
use App\Http\Controllers\Admin\ModuleContentManagementController;
use App\Http\Controllers\Admin\ModuleExerciseManagementController;
use App\Http\Controllers\Admin\ModuleManagementController;
use App\Http\Controllers\Admin\PracticeManagementController;
use App\Http\Controllers\Admin\QuizManagementController;
use App\Http\Controllers\Admin\TextManagementController;
use App\Http\Controllers\Admin\VideoManagementController;
use App\Http\Controllers\Admin\EnrollmentReviewController;
use App\Http\Controllers\Admin\ForumController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FaqController;

use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\ZoomLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/courses', [CourseController::class, 'getCourseTableWithZoomLink']);
Route::post('/course', [CourseController::class, 'store']);
Route::delete('/course/{id}', [CourseController::class, 'destroy']);
Route::post('/course/{id}', [CourseController::class, 'update']);
Route::patch('/course/updateStatus/{id}', [CourseController::class, 'editCourseStatus']);
Route::post('/course/{id}/upload-certificate', [CourseController::class, 'uploadCertificateTemplate']);

Route::post('/faqs', [FaqController::class, 'store']);
Route::put('/faqs/{faq}', [FaqController::class, 'update']);
Route::delete('/faqs/{faq}', [FaqController::class, 'destroy']);

Route::post('/trainers', [TrainerController::class, 'store']);
Route::put('/trainers/{trainer}', [TrainerController::class, 'update']);
Route::delete('/trainers/{trainer}', [TrainerController::class, 'destroy']);
Route::put('/trainers/{trainer}/toggle-starred', [TrainerController::class, 'toggleStarred']);

Route::get('/course-registrant', [RegistrationController::class, 'index']);
Route::get('/course-registrant/{registrationId}', [RegistrationController::class, 'detailRegistration']);
Route::post('/course-registrant/approve/{registrationId}', [RegistrationController::class, 'approveRegistration']);

Route::post('/zoom-link', [ZoomLinkController::class, 'store']);
Route::patch('/zoom-link/{id}', [ZoomLinkController::class, 'update']);
Route::delete('/zoom-link/{id}', [ZoomLinkController::class, 'destroy']);

Route::get('courses/{courseId}/modules', [ModuleManagementController::class, 'index']);
Route::post('modules', [ModuleManagementController::class, 'store']);
Route::get('modules/{id}', [ModuleManagementController::class, 'show']);
Route::put('modules/{id}', [ModuleManagementController::class, 'update']);
Route::delete('modules/{id}', [ModuleManagementController::class, 'destroy']);
Route::post('modules/reorder', [ModuleManagementController::class, 'reorder']);

Route::get('modules/{moduleId}/concepts', [ModuleConceptManagementController::class, 'index']);
Route::post('module-concepts', [ModuleConceptManagementController::class, 'store']);
Route::get('module-concepts/{id}', [ModuleConceptManagementController::class, 'show']);
Route::put('module-concepts/{id}', [ModuleConceptManagementController::class, 'update']);
Route::delete('module-concepts/{id}', [ModuleConceptManagementController::class, 'destroy']);
Route::post('module-concepts/reorder', [ModuleConceptManagementController::class, 'reorder']);

Route::get('modules/{moduleId}/exercises', [ModuleExerciseManagementController::class, 'index']);
Route::post('module-exercises', [ModuleExerciseManagementController::class, 'store']);
Route::get('module-exercises/{id}', [ModuleExerciseManagementController::class, 'show']);
Route::put('module-exercises/{id}', [ModuleExerciseManagementController::class, 'update']);
Route::delete('module-exercises/{id}', [ModuleExerciseManagementController::class, 'destroy']);
Route::post('module-exercises/reorder', [ModuleExerciseManagementController::class, 'reorder']);

Route::resource('course_benefits', CourseBenefitController::class);

Route::group(['prefix' => 'admin'], function () {
    Route::get('modules/{module}/contents', [ModuleContentManagementController::class, 'index']);
    Route::post('modules/{module}/contents', [ModuleContentManagementController::class, 'store']);
    Route::get('modules/{module}/contents/{content}', [ModuleContentManagementController::class, 'show']);
    Route::put('modules/{module}/contents/{content}', [ModuleContentManagementController::class, 'update']);
    Route::delete('modules/{module}/contents/{content}', [ModuleContentManagementController::class, 'destroy']);
    Route::post('modules/{module}/contents/reorder', [ModuleContentManagementController::class, 'reorder']);

    Route::group(['prefix' => 'texts'], function () {
        Route::get('/', [TextManagementController::class, 'index']);
        Route::post('/', [TextManagementController::class, 'store']);
        Route::get('/{id}', [TextManagementController::class, 'show']);
        Route::put('/{id}', [TextManagementController::class, 'update']);
        Route::delete('/{id}', [TextManagementController::class, 'destroy']);
    });


    Route::group(['prefix' => 'quizzes'], function () {
        Route::get('/', [QuizManagementController::class, 'index']);
        Route::post('/', [QuizManagementController::class, 'store']);
        Route::get('/{id}', [QuizManagementController::class, 'show']);
        Route::put('/{id}', [QuizManagementController::class, 'update']);
        Route::delete('/{id}', [QuizManagementController::class, 'destroy']);
    });

    Route::group(['prefix' => 'assignments'], function () {
        Route::get('/', [AssignmentManagementController::class, 'index']);
        Route::post('/', [AssignmentManagementController::class, 'store']);
        Route::get('/{id}', [AssignmentManagementController::class, 'show']);
        Route::put('/{id}', [AssignmentManagementController::class, 'update']);
        Route::delete('/{id}', [AssignmentManagementController::class, 'destroy']);
    });

    Route::group(['prefix' => 'videos'], function () {
        Route::get('/', [VideoManagementController::class, 'index']);
        Route::post('/', [VideoManagementController::class, 'store']);
        Route::get('/{id}', [VideoManagementController::class, 'show']);
        Route::put('/{video}', [VideoManagementController::class, 'update']);
        Route::delete('/{id}', [VideoManagementController::class, 'destroy']);
    });

    Route::group(['prefix' => 'practices'], function () {
        Route::get('/', [PracticeManagementController::class, 'index']);
        Route::post('/', [PracticeManagementController::class, 'store']);
        Route::get('/{id}', [PracticeManagementController::class, 'show']);
        Route::put('/{id}', [PracticeManagementController::class, 'update']);
        Route::delete('/{id}', [PracticeManagementController::class, 'destroy']);
    });

    Route::group(['prefix' => 'files'], function () {
        Route::get('/', [FileManagementController::class, 'index']);
        Route::post('/', [FileManagementController::class, 'store']);
        Route::get('/{id}', [FileManagementController::class, 'show']);
        Route::post('/{id}', [FileManagementController::class, 'update']);
        Route::delete('/{id}', [FileManagementController::class, 'destroy']);
        Route::get('/{id}/download', [FileManagementController::class, 'download']);
    });

    Route::get('enrollments', [EnrollmentReviewController::class, 'index']);
    Route::put('enrollments/{enrollment}/review', [EnrollmentReviewController::class, 'review']);
    Route::post('enrollments/{enrollment}/generate-certificate', [EnrollmentReviewController::class, 'generateCertificate']);

    // Forum management for specific courses
    Route::apiResource('courses.forums', ForumController::class)->shallow();
});
