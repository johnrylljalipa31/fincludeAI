<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsentController;
use App\Http\Controllers\SmileController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ManagerController;

// Public Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Smile Open Banking Webhook Route
Route::post('/webhook/smile', [WebhookController::class, 'handleWebhook']);

// Smile Sandbox Test Token Route (Allows immediate developer sandbox testing)
Route::match(['get', 'post'], '/smile/test-token', [SmileController::class, 'createToken']);

// Protected User Routes (Sanctum Token Auth)
Route::middleware('auth:sanctum')->group(function () {
    // Auth Profile
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/profile/picture', [AuthController::class, 'uploadProfilePicture']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Data Privacy Consent (RA 10173)
    Route::post('/consent/agree', [ConsentController::class, 'agree']);
    Route::get('/consent/status', [ConsentController::class, 'status']);

    // Smile Wink Account Linking Token
    Route::post('/smile/token', [SmileController::class, 'createToken']);

    // Core Dashboard & Scoring
    Route::get('/dashboard', [LoanController::class, 'dashboard']);

    // Micro-Loan Lifecycle & Compliance
    Route::post('/loan/apply', [LoanController::class, 'apply']);
    Route::get('/loan/active', [LoanController::class, 'activeLoan']);

    // Manager / Underwriter Portal Endpoints
    Route::prefix('manager')->group(function () {
        Route::get('/queue', [ManagerController::class, 'queue']);
        Route::get('/applicant/{id}', [ManagerController::class, 'applicantProfile']);
        Route::post('/loan/{id}/decision', [ManagerController::class, 'decide']);
        Route::delete('/loan/{id}', [ManagerController::class, 'removeLoan']);
    });
});