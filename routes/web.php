<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\CustomerController;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'show'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Super Admin Only - User Management
    Route::middleware('role:super_admin')->group(function() {
        Route::resource('users', UserController::class);

        // Customer Management
        Route::resource('customers', CustomerController::class)->only(['index', 'show', 'edit', 'update']);
        Route::post('customers/{customer}/notes', [CustomerController::class, 'addNote'])->name('customers.addNote');
    });

    // Lead Management
    Route::resource('leads', LeadController::class);
    Route::post('leads/{lead}/approve', [LeadController::class, 'approve'])->name('leads.approve');
    Route::post('leads/{lead}/reject', [LeadController::class, 'reject'])->name('leads.reject');
    Route::post('leads/{lead}/calls', [LeadController::class, 'addCall'])->name('leads.addCall');
    Route::post('leads/{lead}/notes', [LeadController::class, 'addNote'])->name('leads.addNote');

    // Job Management
    Route::resource('jobs', JobController::class);
    Route::post('jobs/{job}/assign', [JobController::class, 'assign'])->name('jobs.assign');
    Route::post('jobs/{job}/start', [JobController::class, 'startJob'])->name('jobs.start');
    Route::post('jobs/{job}/complete', [JobController::class, 'completeJob'])->name('jobs.complete');

    // Duplicate check and direct job creation
    Route::post('leads/check-duplicate', [LeadController::class, 'checkDuplicate'])->name('leads.checkDuplicate');
    Route::post('jobs/create-for-customer', [JobController::class, 'createForCustomer'])->name('jobs.createForCustomer');
});

// Redirect root to login or dashboard
Route::redirect('/', 'dashboard');
