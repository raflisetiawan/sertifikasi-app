<?php

use App\Http\Controllers\Admin\ModuleConceptManagementController;
use App\Http\Controllers\Admin\ModuleExerciseManagementController;
use App\Http\Controllers\Admin\ModuleManagementController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\ZoomLinkController;
use Illuminate\Support\Facades\Route;

Route::post('/course', [CourseController::class, 'store']);
Route::delete('/course/{id}', [CourseController::class, 'destroy']);
Route::patch('/course/{id}', [CourseController::class, 'update']);
Route::patch('/course/updateStatus/{id}', [CourseController::class, 'editCourseStatus']);
Route::post('/course/{id}/upload-certificate', [CourseController::class, 'uploadCertificateTemplate']);

Route::post('/faqs', [FaqController::class, 'store']);
Route::put('/faqs/{faq}', [FaqController::class, 'update']);
Route::delete('/faqs/{faq}', [FaqController::class, 'destroy']);

Route::post('/trainers', [TrainerController::class, 'store']);
Route::put('/trainers/{trainer}', [TrainerController::class, 'update']);
Route::delete('/trainers/{trainer}', [TrainerController::class, 'destroy']);
Route::put('/trainers/{trainer}/toggle-starred', [TrainerController::class, 'toggleStarred']);

Route::post('/materials', [MaterialController::class, 'store']);
Route::patch('/materials/{material}', [MaterialController::class, 'update']);
Route::delete('/materials/{material}', [MaterialController::class, 'destroy']);
Route::get('/materials/by-course/{courseId}', [MaterialController::class, 'getMaterialsByCourse']);

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
