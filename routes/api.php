<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JobApplicationController;

// Job Application API Routes
Route::prefix('job-applications')->group(function () {
    
    // Public routes (for recruitment forms)
    Route::post('/', [JobApplicationController::class, 'store']);
    Route::get('/departments', [JobApplicationController::class, 'getDepartments']);
    Route::get('/form-options', [JobApplicationController::class, 'getFormOptions']);
    
    // Protected routes (requires authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [JobApplicationController::class, 'index']);
        Route::get('/{id}', [JobApplicationController::class, 'show']);
        Route::patch('/{id}/status', [JobApplicationController::class, 'updateStatus']);
        Route::get('/{id}/files/{filename}', [JobApplicationController::class, 'downloadFile']);
    });
});