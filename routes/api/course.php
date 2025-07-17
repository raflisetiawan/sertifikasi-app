<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseController;

Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::get('/{course}', [CourseController::class, 'show']);
});
