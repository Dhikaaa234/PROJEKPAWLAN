<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StatusController;
use Illuminate\Support\Facades\Route;

// Endpoint publik: dipakai sebelum user login.
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Endpoint protected: semua request di dalam group ini wajib membawa token Sanctum.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Master data untuk dropdown kategori dan status laporan.
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/statuses', [StatusController::class, 'index']);

    // Dashboard user dan data profile/settings.
    Route::get('/user/dashboard', [DashboardController::class, 'userDashboard']);
    Route::get('/dashboard', [DashboardController::class, 'userDashboard']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/change-password', [ProfileController::class, 'changePassword']);

    // Fitur laporan user: list, create, detail, cancel, dan data pendukung form.
    Route::get('/reports/options', [ReportController::class, 'options']);
    Route::get('/reports/similar', [ReportController::class, 'similar']);
    Route::get('/reports/my', [ReportController::class, 'myReports']);
    Route::get('/my-reports', [ReportController::class, 'myReports']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports/{report}', [ReportController::class, 'show']);
    Route::patch('/reports/{report}/cancel', [ReportController::class, 'cancel']);

    // Notifikasi user, termasuk mark as read dan read all.
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'show']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read']);

    // Endpoint khusus admin: selain auth, juga wajib lolos middleware role admin.
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard']);
        Route::get('/reports/stats', [DashboardController::class, 'adminReportStats']);
        Route::get('/reports/export', [DashboardController::class, 'exportReports']);
        Route::post('/reports/generate', [DashboardController::class, 'generateReport']);
        Route::get('/reports', [ReportController::class, 'adminIndex']);
        Route::get('/reports/{report}', [ReportController::class, 'adminShow']);
        Route::patch('/reports/{report}/status', [ReportController::class, 'updateStatus']);

        Route::get('/notifications', [NotificationController::class, 'adminIndex']);
        Route::patch('/notifications/read-all', [NotificationController::class, 'adminReadAll']);
        Route::get('/notifications/{notification}', [NotificationController::class, 'adminShow']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'adminRead']);
    });
});
