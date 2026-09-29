<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WebController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes — Finclude-AI All-in-One Laravel Application
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Public Authentication & Information Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [WebController::class, 'index'])
    ->name('home');

Route::get('/login', [WebController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [WebController::class, 'login'])
    ->name('login.submit');

Route::get('/register', [WebController::class, 'showRegister'])
    ->name('register');

Route::post('/register', [WebController::class, 'register'])
    ->name('register.submit');


/*
|--------------------------------------------------------------------------
| Code-Based Password Reset Routes
|--------------------------------------------------------------------------
*/

Route::post('/password/code/send', [WebController::class, 'sendPasswordCode'])
    ->name('password.code.send');

Route::post('/password/code/verify', [WebController::class, 'verifyAndResetPassword'])
    ->name('password.code.verify');


/*
|--------------------------------------------------------------------------
| Public Information
|--------------------------------------------------------------------------
*/

Route::get('/privacy', [WebController::class, 'showPrivacy'])
    ->name('privacy');


/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post('/logout', [WebController::class, 'logout'])
    ->name('logout');

Route::get('/logout', [WebController::class, 'logout']);


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Gig Worker Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [WebController::class, 'dashboard'])
        ->name('dashboard');

    Route::post('/profile/update', [WebController::class, 'updateProfile'])
        ->name('profile.update');

    Route::post('/profile/picture', [WebController::class, 'uploadProfilePicture'])
        ->name('profile.picture');


    /*
    |--------------------------------------------------------------------------
    | RA 10173 Compliance Consent
    |--------------------------------------------------------------------------
    */

    Route::get('/consent', [WebController::class, 'showConsent'])
        ->name('consent');

    Route::post('/consent', [WebController::class, 'submitConsent'])
        ->name('consent.submit');


    /*
    |--------------------------------------------------------------------------
    | Smile Open Banking Account Linker
    |--------------------------------------------------------------------------
    */

    Route::get('/smile/link', [WebController::class, 'showSmileLinking'])
        ->name('smile.link');

    Route::post('/smile/connect', [WebController::class, 'connectSmileProvider'])
        ->name('smile.connect.submit');


    /*
    |--------------------------------------------------------------------------
    | Micro-Loan Application & Repayment Lifecycle
    |--------------------------------------------------------------------------
    */

    Route::post('/loan/apply', [WebController::class, 'applyLoan'])
        ->name('loan.apply.submit');

    Route::get('/loan/lifecycle', [WebController::class, 'loanLifecycle'])
        ->name('loan.lifecycle');

    Route::post('/loan/repay', [WebController::class, 'repayLoan'])
        ->name('loan.repay.submit');


    /*
    |--------------------------------------------------------------------------
    | Manager / Underwriter Portal
    |--------------------------------------------------------------------------
    */

    Route::prefix('manager')->group(function () {

        Route::get('/', [WebController::class, 'managerDashboard'])
            ->name('manager.dashboard');

        Route::post('/loan/{id}/decision', [WebController::class, 'managerDecision'])
            ->name('manager.loan.decision');

        Route::post('/loan/{id}/remove', [WebController::class, 'managerRemoveLoan'])
            ->name('manager.loan.remove');

        Route::get('/profile', [WebController::class, 'managerProfile'])
            ->name('manager.profile');
    });


    /*
    |--------------------------------------------------------------------------
    | Admin Portal
    |--------------------------------------------------------------------------
    |
    | Admin dashboard and administration functions.
    | AdminController performs the admin-role verification.
    |
    */

    Route::prefix('admin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Admin Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [AdminController::class, 'dashboard'])
            ->name('admin.dashboard');


        /*
        |--------------------------------------------------------------------------
        | Admin Users
        |--------------------------------------------------------------------------
        */

        // Users list
        Route::get('/users', [AdminController::class, 'users'])
            ->name('admin.users');

        // Edit user
        Route::get('/users/{userId}/edit', [AdminController::class, 'editUser'])
            ->name('admin.users.edit');

        // Update user
        Route::put('/users/{userId}', [AdminController::class, 'updateUser'])
            ->name('admin.users.update');

        // Deactivate user
        Route::put('/users/{userId}/deactivate', [AdminController::class, 'deactivateUser'])
            ->name('admin.users.deactivate');

        // Restore user
        Route::put('/users/{userId}/restore', [AdminController::class, 'restoreUser'])
            ->name('admin.users.restore');


        /*
        |--------------------------------------------------------------------------
        | Admin User Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/users/{userId}', [AdminController::class, 'userProfile'])
            ->name('admin.users.profile');


        /*
        |--------------------------------------------------------------------------
        | Admin Loans
        |--------------------------------------------------------------------------
        */

        Route::get('/loans', [AdminController::class, 'loans'])
            ->name('admin.loans');


        /*
        |--------------------------------------------------------------------------
        | Admin Audit Logs
        |--------------------------------------------------------------------------
        */

        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])
            ->name('admin.audit.logs');
    });
});