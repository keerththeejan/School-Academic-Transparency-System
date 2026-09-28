<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ParentApiController;
use App\Http\Controllers\Api\V1\SchoolApiController;
use App\Http\Controllers\Api\V1\TeacherApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/parent/children', [ParentApiController::class, 'children']);
        Route::get('/parent/summaries', [ParentApiController::class, 'summaries']);
        Route::get('/parent/summaries/{summary}', [ParentApiController::class, 'summary']);
        Route::post('/parent/discrepancies', [ParentApiController::class, 'discrepancy']);

        Route::get('/teacher/today', [TeacherApiController::class, 'today']);
        Route::post('/teacher/deviations', [TeacherApiController::class, 'deviate']);

        Route::get('/school/dashboard', [SchoolApiController::class, 'dashboard']);
        Route::get('/school/reports', [SchoolApiController::class, 'report']);
    });
});
