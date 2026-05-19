<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StatusController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
// Reset password (tambahan untuk lengkapi)
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (auth:sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Profile
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/change-password', [ProfileController::class, 'changePassword']);

    // Dashboard
    Route::get('/user/dashboard', [DashboardController::class, 'userDashboard']);

    // Reports - User
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/reports/my', [ReportController::class, 'myReports']);
    Route::get('/reports/options', [ReportController::class, 'options']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports/{report}', [ReportController::class, 'show']);
    Route::patch('/reports/{report}/cancel', [ReportController::class, 'cancel']);

    // Notifications - User
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll']);

    // Categories (opsional, untuk keperluan umum)
    Route::get('/categories', [CategoryController::class, 'index']);

    // Statuses (opsional)
    Route::get('/statuses', [StatusController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->group(function () {
        // Dashboard admin
        Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard']);
        Route::get('/admin/reports/stats', [DashboardController::class, 'adminReportStats']);
        Route::get('/admin/reports/export', [DashboardController::class, 'exportReports']);
        Route::post('/admin/reports/generate', [DashboardController::class, 'generateReport']);

        // Admin Reports
        Route::get('/admin/reports', [ReportController::class, 'adminIndex']);
        Route::get('/admin/reports/{report}', [ReportController::class, 'adminShow']);
        Route::patch('/admin/reports/{report}/status', [ReportController::class, 'updateStatus']);

        // Admin Notifications
        Route::get('/admin/notifications', [NotificationController::class, 'adminIndex']);
        Route::patch('/admin/notifications/{notification}/read', [NotificationController::class, 'adminRead']);
        Route::patch('/admin/notifications/read-all', [NotificationController::class, 'adminReadAll']);
    });
});