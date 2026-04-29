<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeductionTypeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDeductionController;
use App\Http\Controllers\EmployeePromotionController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollEntryController;
use App\Http\Controllers\SpecialPayrollController;
use App\Http\Controllers\TevController;
use App\Http\Controllers\TevItineraryController;
use App\Http\Controllers\OfficeOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SalaryIndexTableController;
use App\Http\Controllers\SignatoryController;

/*
|--------------------------------------------------------------------------
| Public Routes — No auth required
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));

Route::get('/login',  [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');

// ── HRIS SSO Routes — JWT authentication from HRIS simulation ─────
Route::get('/hris-auth', [AuthController::class, 'hrisAuth'])->name('hris.auth')->middleware('jwt.auth');
Route::get('/tev-hris-auth', [AuthController::class, 'tevHrisAuth'])->name('tev.hris.auth')->middleware('jwt.auth');


/*
|--------------------------------------------------------------------------
| Protected Routes — Requires session auth
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ── Dashboard ────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Employees ────────────────────────────────────────────────
    Route::middleware(['role:payroll_officer|hrmo|accountant|chief_admin_officer|super_admin'])
        ->group(function () {
            Route::resource('employees', EmployeeController::class);
            Route::post('/employees/pull-from-api', [EmployeeController::class, 'pullFromApi'])->name('employees.pullFromApi');

            Route::get( '/employees/{employee}/deductions',
                        [EmployeeDeductionController::class, 'index'])->name('employees.deductions');
            Route::post('/employees/{employee}/deductions',
                        [EmployeeDeductionController::class, 'update'])->name('employees.deductions.update');

            Route::get(   '/employees/{employee}/promotions',
                          [EmployeePromotionController::class, 'index'])->name('employees.promotions.index');
            Route::get(   '/employees/{employee}/promotions/create',
                          [EmployeePromotionController::class, 'create'])->name('employees.promotions.create');
            Route::post(  '/employees/{employee}/promotions',
                          [EmployeePromotionController::class, 'store'])->name('employees.promotions.store');
            Route::delete('/employees/{employee}/promotions/{promotion}',
                          [EmployeePromotionController::class, 'destroy'])->name('employees.promotions.destroy');

            // Employee TEV History
            Route::get('/employees/{employee}/tev-history',
                       [ReportController::class, 'employeeTevHistory'])->name('employees.tev-history');
        });

    // ── Deduction Types CMS ──────────────────────────────────────
    Route::middleware(['role:payroll_officer|super_admin'])
        ->group(function () {
            Route::resource('deduction-types', DeductionTypeController::class)
                ->except(['show', 'destroy']);

            // Toggle active/inactive
            Route::patch(
                '/deduction-types/{deductionType}/toggle',
                [DeductionTypeController::class, 'toggle']
            )->name('deduction-types.toggle');

            // AJAX bulk reorder (called from index page)
            Route::post(
                '/deduction-types/reorder',
                [DeductionTypeController::class, 'reorder']
            )->name('deduction-types.reorder');
        });

    // ── Divisions ────────────────────────────────────────────────
    Route::middleware(['role:payroll_officer|hrmo|super_admin'])
        ->group(function () {
            Route::resource('divisions', DivisionController::class);
        });

    // ── Payroll ──────────────────────────────────────────────────
    // Employee access - My Payslip page
    Route::middleware(['auth'])
        ->group(function () {
            Route::get('/my-payslip', [PayrollController::class, 'myPayslip'])->name('my-payslip');
        });

    // Officer access - full payroll management
    Route::middleware(['role:' . implode('|', \App\Services\RoleService::getRoleGroup('payroll'))])
        ->prefix('payroll')
        ->name('payroll.')
        ->group(function () {
            // Officer routes - specific routes first
            Route::get('/', [PayrollController::class, 'index'])->name('index');
            Route::get('/create', [PayrollController::class, 'create'])->name('create');
            Route::post('/', [PayrollController::class, 'store'])->name('store');
            Route::get('/{payroll}', [PayrollController::class, 'show'])->name('show');
            Route::get('/{payroll}/edit', [PayrollController::class, 'edit'])->name('edit');
            Route::put('/{payroll}', [PayrollController::class, 'update'])->name('update');
            Route::delete('/{payroll}', [PayrollController::class, 'destroy'])->name('destroy');

            // Payroll workflow actions
            Route::post('/{payroll}/compute',    [PayrollController::class, 'compute'])   ->name('compute');
            Route::post('/{payroll}/submit',     [PayrollController::class, 'submit'])    ->name('submit');
            Route::post('/{payroll}/certify',    [PayrollController::class, 'certify'])   ->name('certify');
            Route::post('/{payroll}/approve',    [PayrollController::class, 'approve'])   ->name('approve');
            Route::post('/{payroll}/lock',       [PayrollController::class, 'lock'])      ->name('lock');
            Route::get( '/{payroll}/verify',     [PayrollController::class, 'verify'])    ->name('verify');
            Route::post('/{payroll}/force-edit', [PayrollController::class, 'forceEdit'])->name('forceEdit');
            Route::post('/{payroll}/pull-attendance', [PayrollController::class, 'pullAttendance'])->name('pullAttendance');

            // ── Payslip generation (released / locked batches only) ──────
            // ?mode=consolidated (default) | per_batch
            // ?entry_id=<PayrollEntry id>  (optional — single employee)
            Route::get('/{payroll}/payslips/generate',
                       [PayrollController::class, 'generatePayslips'])
                ->name('payslips.generate');

            // Payroll entries
            Route::get('/{payrollBatch}/entries',
                       [PayrollEntryController::class, 'index'])->name('entries.index');
            Route::get('/{payrollBatch}/entries/{entry}',
                       [PayrollEntryController::class, 'show'])->name('entries.show');
            Route::put('/{payrollBatch}/entries/{entry}',
                       [PayrollEntryController::class, 'update'])->name('entries.update');
            
            // Individual payslip
            Route::get('/{payrollBatch}/payslip/{entry}',
                       [PayrollEntryController::class, 'payslip'])->name('payslip');
        });

    // ── Special Payroll — Newly Hired ────────────────────────────
    Route::middleware(['role:' . implode('|', \App\Services\RoleService::getRoleGroup('special_payroll'))])
        ->group(function () {
            Route::get(   '/special-payroll/newly-hired',
                          [SpecialPayrollController::class, 'newHireIndex'])
                ->name('special-payroll.newly-hired.index');

            Route::get(   '/special-payroll/newly-hired/create',
                          [SpecialPayrollController::class, 'newHireCreate'])
                ->name('special-payroll.newly-hired.create');

            Route::post(  '/special-payroll/newly-hired',
                          [SpecialPayrollController::class, 'newHireStore'])
                ->name('special-payroll.newly-hired.store');

            Route::get(   '/special-payroll/newly-hired/{id}',
                          [SpecialPayrollController::class, 'newHireShow'])
                ->name('special-payroll.newly-hired.show')
                ->where('id', '[0-9]+');

            Route::delete('/special-payroll/newly-hired/{id}',
                          [SpecialPayrollController::class, 'newHireDestroy'])
                ->name('special-payroll.newly-hired.destroy')
                ->where('id', '[0-9]+');
        });

    // ── Special Payroll — Salary Differential ────────────────────
    Route::middleware(['role:' . implode('|', \App\Services\RoleService::getRoleGroup('special_payroll'))])
        ->group(function () {
            Route::get(   '/special-payroll/differential',
                          [SpecialPayrollController::class, 'differentialIndex'])
                ->name('special-payroll.differential.index');

            Route::get(   '/special-payroll/differential/create',
                          [SpecialPayrollController::class, 'differentialCreate'])
                ->name('special-payroll.differential.create');

            Route::post(  '/special-payroll/differential',
                          [SpecialPayrollController::class, 'differentialStore'])
                ->name('special-payroll.differential.store');

            Route::get(   '/special-payroll/differential/{id}',
                          [SpecialPayrollController::class, 'differentialShow'])
                ->name('special-payroll.differential.show')
                ->where('id', '[0-9]+');

            Route::delete('/special-payroll/differential/{id}',
                          [SpecialPayrollController::class, 'differentialDestroy'])
                ->name('special-payroll.differential.destroy')
                ->where('id', '[0-9]+');
        });

    // ── Special Payroll — NOSI / NOSA ────────────────────────────
    Route::middleware(['role:' . implode('|', \App\Services\RoleService::getRoleGroup('special_payroll'))])
        ->group(function () {
            Route::get(    '/special-payroll/nosi-nosa',
                           [SpecialPayrollController::class, 'nosiNosaIndex'])
                ->name('special-payroll.nosi-nosa.index');

            Route::get(    '/special-payroll/nosi-nosa/create',
                           [SpecialPayrollController::class, 'nosiNosaCreate'])
                ->name('special-payroll.nosi-nosa.create');

            Route::post(   '/special-payroll/nosi-nosa',
                           [SpecialPayrollController::class, 'nosiNosaStore'])
                ->name('special-payroll.nosi-nosa.store');

            Route::get(    '/special-payroll/nosi-nosa/{id}',
                           [SpecialPayrollController::class, 'nosiNosaShow'])
                ->name('special-payroll.nosi-nosa.show')
                ->where('id', '[0-9]+');

            Route::post(   '/special-payroll/nosi-nosa/{id}/approve',
                           [SpecialPayrollController::class, 'nosiNosaApprove'])
                ->name('special-payroll.nosi-nosa.approve')
                ->where('id', '[0-9]+');

            Route::delete( '/special-payroll/nosi-nosa/{id}',
                           [SpecialPayrollController::class, 'nosiNosaDestroy'])
                ->name('special-payroll.nosi-nosa.destroy')
                ->where('id', '[0-9]+');
        });

    // ── TEV (Travel & Expense Voucher) ─────────────────────────────
    Route::middleware(['auth'])
        ->prefix('tev')
        ->name('tev.')
        ->group(function () {
            // TEV Dashboard - accessible to all authenticated users
            Route::get('/', [TevController::class, 'dashboard'])->name('dashboard');

            // TEV Requests - employees can create/view their own requests
            Route::resource('requests', TevController::class, [
                'names' => [
                    'index' => 'requests.index',
                    'create' => 'requests.create',
                    'store' => 'requests.store',
                    'show' => 'requests.show',
                    'edit' => 'requests.edit',
                    'update' => 'requests.update',
                    'destroy' => 'requests.destroy',
                ]
            ]);

            // Employee actions - submit own requests
            Route::post('/requests/{tevRequest}/submit',  [TevController::class, 'submit'])->name('requests.submit');

            // Itinerary management - employees can manage their own itinerary
            Route::post(  '/requests/{tevRequest}/itinerary',
                          [TevItineraryController::class, 'store'])->name('requests.itinerary.store');
            Route::put(   '/requests/{tevRequest}/itinerary/{line}',
                          [TevItineraryController::class, 'update'])->name('requests.itinerary.update');
            Route::delete('/requests/{tevRequest}/itinerary/{line}',
                          [TevItineraryController::class, 'destroy'])->name('requests.itinerary.destroy');

            // TEV Liquidation - employees can file their own liquidation
            Route::post('/requests/{tevRequest}/liquidate',
                        [TevController::class, 'fileLiquidation'])->name('requests.liquidate');

            // ── Officer-only actions ─────────────────────────────────
            Route::middleware(['role:hrmo|accountant|budget_officer|ard|cashier|chief_admin_officer|super_admin'])
                ->group(function () {
                    // Office Orders
                    Route::resource('office-orders', OfficeOrderController::class, [
                        'names' => [
                            'index' => 'office-orders.index',
                            'create' => 'office-orders.create',
                            'store' => 'office-orders.store',
                            'show' => 'office-orders.show',
                            'edit' => 'office-orders.edit',
                            'update' => 'office-orders.update',
                            'destroy' => 'office-orders.destroy',
                        ]
                    ]);

                    Route::post('/office-orders/{id}/approve',
                                [OfficeOrderController::class, 'approve'])
                        ->name('office-orders.approve')
                        ->where('id', '[0-9]+');

                    Route::post('/office-orders/{id}/cancel',
                                [OfficeOrderController::class, 'cancel'])
                        ->name('office-orders.cancel')
                        ->where('id', '[0-9]+');

                    // Approval actions
                    Route::post('/requests/{tevRequest}/approve', [TevController::class, 'approve'])->name('requests.approve');
                    Route::post('/requests/{tevRequest}/certify', [TevController::class, 'certify'])->name('requests.certify');
                    Route::post('/requests/{tevRequest}/reject',  [TevController::class, 'reject'])->name('requests.reject');

                    // Liquidation approval
                    Route::post('/requests/{tevRequest}/liquidation/approve',
                                [TevController::class, 'approveLiquidation'])->name('requests.liquidation.approve');
                });
        });

    // ── Reports ──────────────────────────────────────────────────
    Route::middleware(['role:payroll_officer|hrmo|accountant|ard|cashier|chief_admin_officer|budget_officer|super_admin'])
        ->group(function () {
            Route::get('/reports',                    [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/payroll-register',   [ReportController::class, 'payrollRegister'])->name('reports.payroll-register');
            Route::get('/reports/payslip',            [ReportController::class, 'payslip'])->name('reports.payslip');

            // ── GSIS ─────────────────────────────────────────────────────────
            Route::get('/reports/gsis-summary',       [ReportController::class, 'gsisSummary'])->name('reports.gsis-summary');
            Route::get('/reports/gsis-detailed',      [ReportController::class, 'gsisDetailed'])->name('reports.gsis-detailed');
            Route::get('/reports/gsis',               [ReportController::class, 'gsisIndex'])->name('reports.gsis');

            // ── HDMF / Pag-IBIG ──────────────────────────────────────────────
            Route::get('/reports/hdmf',          [ReportController::class, 'hdmfIndex'])->name('reports.hdmf');
            Route::get('/reports/hdmf/download', [ReportController::class, 'hdmf'])->name('reports.hdmf-download');

            // ── Other remittances ─────────────────────────────────────────────
            Route::get('/reports/remittances',     [ReportController::class, 'remittancesHub'])->name('reports.remittances');
            Route::get('/reports/phic-csv',        [ReportController::class, 'phicCsv'])->name('reports.phic-csv');
            Route::get('/reports/sss',             [ReportController::class, 'sssVoluntary'])->name('reports.sss');
            Route::get('/reports/lbp-loan',        [ReportController::class, 'lbpLoan'])->name('reports.lbp-loan');
            Route::get('/reports/caress-union',    [ReportController::class, 'caressUnion'])->name('reports.caress-union');
            Route::get('/reports/caress-mortuary', [ReportController::class, 'caressMortuary'])->name('reports.caress-mortuary');
            Route::get('/reports/mass',            [ReportController::class, 'mass'])->name('reports.mass');
            Route::get('/reports/provident-fund',  [ReportController::class, 'providentFund'])->name('reports.provident-fund');
            Route::get('/reports/btr-refund',      [ReportController::class, 'btrRefund'])->name('reports.btr-refund');

            // TEV PDF reports
            Route::get('/reports/tev/{tevRequest}/itinerary',
                       [ReportController::class, 'tevItinerary'])->name('reports.tev-itinerary');
            Route::get('/reports/tev/{tevRequest}/travel-completed',
                       [ReportController::class, 'tevTravelCompleted'])->name('reports.tev-travel-completed');
            Route::get('/reports/tev/{tevRequest}/annex-a',
                       [ReportController::class, 'tevAnnexA'])->name('reports.tev-annex-a');

            // TEV Register report + export
            Route::get('/reports/tev-register/export',
                       [ReportController::class, 'tevRegisterExport'])->name('reports.tev-register.export');
            Route::get('/reports/tev-register',
                       [ReportController::class, 'tevRegister'])->name('reports.tev-register');
        });

    // ── Users ────────────────────────────────────────────────────
    Route::middleware(['role:super_admin'])
        ->group(function () {
            Route::resource('users', UserController::class);

            // ↓ NEW: Toggle active/inactive for a specific role assignment on a user
            Route::post('users/{user}/activate-role', [UserController::class, 'activateRole'])
                ->name('users.activate-role');
        });

    // ── Signatories (payroll_officer + super_admin) ──────────────
    // Manages the dynamic signing officers shown on payslips and reports.
    Route::middleware(['role:payroll_officer|super_admin'])
        ->group(function () {
            // ↓ NEW: Must be declared BEFORE the resource to avoid Laravel
            //   treating 'users-for-role' as a {signatory} model binding.
            Route::get('signatories/users-for-role', [SignatoryController::class, 'usersForRole'])
                ->name('signatories.users-for-role');

            Route::resource('signatories', SignatoryController::class)
                ->except(['show']);

            Route::patch('/signatories/{signatory}/toggle',
                         [SignatoryController::class, 'toggle'])
                ->name('signatories.toggle');
        });

});

/*
|--------------------------------------------------------------------------
| AJAX API Routes — Auth required, /api prefix
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/sit', [SalaryIndexTableController::class, 'lookup'])->name('api.sit');
});
