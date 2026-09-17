<?php

use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RequestFileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
	Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
	Route::post('/login', [AuthenticatedSessionController::class, 'store']);
	Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
	Route::post('/register', [RegisteredUserController::class, 'store']);
	Route::get('/forgot-password', [AuthenticatedSessionController::class, 'forgotPassword'])->name('password.request');
	Route::post('/forgot-password', [AuthenticatedSessionController::class, 'sendResetLink'])->name('password.email');
	Route::get('/reset-password/{token}', [AuthenticatedSessionController::class, 'resetPassword'])->name('password.reset');
	Route::post('/reset-password', [AuthenticatedSessionController::class, 'updatePassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
	Route::get('/', DashboardController::class)->name('dashboard');
	Route::get('/requests', [DocumentRequestController::class, 'index'])->name('requests.index');
	Route::get('/requests/create', [DocumentRequestController::class, 'create'])->name('requests.create');
	Route::get('/requests/{documentRequest}', [DocumentRequestController::class, 'show'])->name('requests.show');
	Route::post('/requests', [DocumentRequestController::class, 'store'])->name('requests.store');
	Route::patch('/requests/{documentRequest}/status', [DocumentRequestController::class, 'updateStatus'])->name('requests.status');
	Route::get('/requests/{documentRequest}/edit', [DocumentRequestController::class, 'edit'])->middleware('role:staff,admin')->name('requests.edit');
	Route::put('/requests/{documentRequest}', [DocumentRequestController::class, 'update'])->middleware('role:staff,admin')->name('requests.update');
	Route::delete('/requests/{documentRequest}', [DocumentRequestController::class, 'destroy'])->middleware('role:admin')->name('requests.destroy');
	Route::patch('/requests/{documentRequest}/restore', [DocumentRequestController::class, 'restore'])->middleware('role:admin')->name('requests.restore');
	Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
	Route::get('/account', [ProfileController::class, 'edit'])->name('profile.edit');
	Route::patch('/account', [ProfileController::class, 'update'])->name('profile.update');
	Route::patch('/account/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
	Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
	Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
	Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
	Route::get('/request-files/{requestFile}', [RequestFileController::class, 'download'])->name('request-files.download');
	Route::middleware('role:admin')->group(function () {
		Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users');
		Route::patch('/admin/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('admin.users.role');
		Route::get('/admin/activity', [ActivityLogController::class, 'index'])->name('admin.activities');
		Route::get('/admin/reports', [ReportController::class, 'index'])->name('reports.index');
		Route::get('/admin/reports.csv', [ReportController::class, 'csv'])->name('reports.csv');
		Route::get('/admin/settings', [SettingsController::class, 'edit'])->name('admin.settings');
		Route::patch('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
		Route::patch('/admin/users/{user}/active', [AdminUserController::class, 'toggleActive'])->name('admin.users.active');
	});
	Route::middleware('role:staff,admin')->get('/staff/reports', [ReportController::class, 'index'])->name('staff.reports');
	Route::middleware('role:staff,admin')->get('/staff/reports.csv', [ReportController::class, 'csv'])->name('staff.reports.csv');
	Route::middleware('role:staff,admin')->get('/staff/activity', [ActivityLogController::class, 'index'])->name('staff.activities');
});
