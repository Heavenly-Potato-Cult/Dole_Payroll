<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Payroll\Models\PayrollBatch;
use Modules\Payroll\Policies\PayrollPolicy;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(PayrollBatch::class, PayrollPolicy::class);
        
        // Blade directives for role-based access control
        Blade::if('canAccessPayroll', function () {
            return \App\SharedKernel\Services\RoleService::canAccessPayroll(auth()->user());
        });
        
        Blade::if('canCreatePayroll', function () {
            return \App\SharedKernel\Services\RoleService::canCreatePayroll(auth()->user());
        });
        
        Blade::if('canAccessSpecialPayroll', function () {
            return \App\SharedKernel\Services\RoleService::canAccessSpecialPayroll(auth()->user());
        });
        
        Blade::if('canAccessTev', function () {
            return \App\SharedKernel\Services\RoleService::canAccessTev(auth()->user());
        });
    }
}