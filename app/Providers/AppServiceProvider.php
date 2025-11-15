<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Check role blade directive
        Blade::if('role', function ($role) {
            return optional(auth()->user())->hasRole($role);
        });

        // Super Admin blade directive
        Blade::if('superadmin', function () {
            return optional(auth()->user())->isSuperAdmin();
        });

        // Lead Manager blade directive
        Blade::if('leadmanager', function () {
            return optional(auth()->user())->isLeadManager();
        });

        // Field Staff blade directive
        Blade::if('fieldstaff', function () {
            return optional(auth()->user())->isFieldStaff();
        });

        // Reporting User blade directive
        Blade::if('reportinguser', function () {
            return optional(auth()->user())->isReportingUser();
        });
    }
}
