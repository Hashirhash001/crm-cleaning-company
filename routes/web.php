<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FollowupController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadBulkImportController;

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
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Customer Note Delete
    Route::delete('/customers/{customer}/notes/{note}', [CustomerController::class, 'deleteNote'])
    ->name('customers.notes.delete');
    // API endpoints for AJAX calls
    Route::get('/api/customers/{customer}/jobs', [CustomerController::class, 'getCustomerJobs'])
        ->name('api.customers.jobs');

    Route::get('/api/customers/{customer}/notes', [CustomerController::class, 'getCustomerNotes'])
        ->name('api.customers.notes');

    // Customer Management
    Route::resource('customers', CustomerController::class)->only(['index', 'show', 'edit', 'update', 'create', 'store', 'destroy']);
    Route::post('customers/{customer}/notes', [CustomerController::class, 'addNote'])->name('customers.addNote');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/daily-budget', [SettingsController::class, 'updateDailyBudget'])->name('settings.updateDailyBudget');

    // Quick search for telecallers
    Route::get('/telecaller/quick-search', [LeadController::class, 'quickSearch'])
        ->name('telecaller.quick-search')
        ->middleware('auth');

    // Lead Management - SPECIFIC ROUTES MUST COME BEFORE RESOURCE ROUTE

    // Bulk Import Routes - BEFORE resource route
    Route::get('/leads/bulk-import', [LeadBulkImportController::class, 'bulkImport'])->name('leads.bulk-import');
    Route::get('/leads/download-template', [LeadBulkImportController::class, 'downloadTemplate'])->name('leads.download-template');
    Route::post('/leads/process-bulk-import', [LeadBulkImportController::class, 'processBulkImport'])->name('leads.process-bulk-import');
    Route::get('/leads/bulk-import/progress/{import}', [LeadBulkImportController::class, 'getImportProgress'])
    ->name('leads.import-progress');
    Route::get('/leads/bulk-import/download-failed/{import}', [LeadBulkImportController::class, 'downloadFailedRows'])
    ->name('leads.download-failed-rows');
    Route::post('/leads/pre-validate-import', [LeadBulkImportController::class, 'preValidateImport'])
    ->name('leads.pre-validate-import');

    // Other specific lead routes - BEFORE resource route
    Route::get('/leads/whatsapp', [LeadController::class, 'whatsappLeads'])->name('leads.whatsapp');
    Route::get('/leads/google-ads', [LeadController::class, 'googleAdsLeads'])->name('leads.google-ads');
    Route::get('/leads/services-by-type', [LeadController::class, 'getServicesByType'])->name('leads.servicesByType');

    // Lead actions
    Route::post('leads/{lead}/approve', [LeadController::class, 'approve'])->name('leads.approve');
    Route::post('leads/{lead}/reject', [LeadController::class, 'reject'])->name('leads.reject');
    Route::post('leads/{lead}/calls', [LeadController::class, 'addCall'])->name('leads.addCall');
    Route::post('leads/{lead}/notes', [LeadController::class, 'addNote'])->name('leads.addNote');
    Route::post('/leads/{lead}/assign', [LeadController::class, 'assignLead'])->name('leads.assign');
    Route::post('/leads/{lead}/followups', [LeadController::class, 'addFollowup'])->name('leads.addFollowup');
    Route::post('/leads/bulk-assign', [LeadController::class, 'bulkAssign'])->name('leads.bulkAssign');
    Route::post('/leads/{lead}/status', [LeadController::class, 'updateStatus'])
        ->name('leads.update-status');
    Route::delete('leads/followup/{followup}', [LeadController::class, 'deleteFollowup'])->name('leads.deleteFollowup');
    Route::delete('leads/call/{call}', [LeadController::class, 'deleteCall'])->name('leads.deleteCall');
    Route::delete('leads/note/{note}', [LeadController::class, 'deleteNote'])->name('leads.deleteNote');

    // Lead Followups
    Route::post('/lead-followups/{followup}/complete', [LeadController::class, 'complete'])->name('lead-followups.complete');

    // Lead resource route - MUST BE LAST
    Route::resource('leads', LeadController::class);

    // Job Management
    // Job Followups
    Route::get('/followups', [FollowupController::class, 'index'])->name('followups.index');
    Route::post('/jobs/{job}/followups', [JobController::class, 'addFollowup'])->name('jobs.addFollowup');
    Route::post('/job-followups/{followup}/complete', [JobController::class, 'completeFollowup'])->name('jobs.completeFollowup');
    Route::delete('/jobs/{job}/followups/{followup}', [JobController::class, 'deleteFollowup'])->name('jobs.deleteFollowup');

    // Job Calls
    Route::post('/jobs/{job}/calls', [JobController::class, 'addCall'])->name('jobs.addCall');
    Route::delete('/jobs/{job}/calls/{call}', [JobController::class, 'deleteCall'])->name('jobs.deleteCall');

    // Job Notes
    Route::post('/jobs/{job}/notes', [JobController::class, 'addNote'])->name('jobs.addNote');
    Route::delete('/jobs/{job}/notes/{note}', [JobController::class, 'deleteNote'])->name('jobs.deleteNote');

    Route::post('/jobs/{job}/confirm-status', [JobController::class, 'confirmStatus'])
    ->name('jobs.confirm-status');
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
