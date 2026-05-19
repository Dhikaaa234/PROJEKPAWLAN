<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StatusController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/statuses', [StatusController::class, 'index']);

    Route::get('/user/dashboard', [DashboardController::class, 'userDashboard']);
    Route::get('/dashboard', [DashboardController::class, 'userDashboard']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/change-password', [ProfileController::class, 'changePassword']);

    Route::get('/reports/options', [ReportController::class, 'options']);
    Route::get('/reports/similar', [ReportController::class, 'similar']);
    Route::get('/reports/my', [ReportController::class, 'myReports']);
    Route::get('/my-reports', [ReportController::class, 'myReports']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports/{report}', [ReportController::class, 'show']);
    Route::patch('/reports/{report}/cancel', [ReportController::class, 'cancel']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::get('/notifications/{notification}', [NotificationController::class, 'show']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read']);

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
