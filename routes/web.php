<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
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

/*
|--------------------------------------------------------------------------
| Public Routes — No auth required
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));

Route::get('/login',  [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');

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
    Route::resource('employees', EmployeeController::class);

    // Deductions (managed by dedicated controller)
    Route::get( '/employees/{employee}/deductions',
                [EmployeeDeductionController::class, 'index'])->name('employees.deductions');
    Route::post('/employees/{employee}/deductions',
                [EmployeeDeductionController::class, 'update'])->name('employees.deductions.update');

    // Promotion / Step History
    Route::get(   '/employees/{employee}/promotions',
                  [EmployeePromotionController::class, 'index'])->name('employees.promotions.index');
    Route::get(   '/employees/{employee}/promotions/create',
                  [EmployeePromotionController::class, 'create'])->name('employees.promotions.create');
    Route::post(  '/employees/{employee}/promotions',
                  [EmployeePromotionController::class, 'store'])->name('employees.promotions.store');
    Route::delete('/employees/{employee}/promotions/{promotion}',
                  [EmployeePromotionController::class, 'destroy'])->name('employees.promotions.destroy');

    // ── Divisions ────────────────────────────────────────────────
    Route::resource('divisions', DivisionController::class);

    // ── Payroll ──────────────────────────────────────────────────
Route::resource('payroll', PayrollController::class);
Route::post('/payroll/{payroll}/compute', [PayrollController::class, 'compute'])->name('payroll.compute');
Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('payroll.approve');
Route::post('/payroll/{payroll}/lock',    [PayrollController::class, 'lock'])->name('payroll.lock');

    Route::get('/payroll/{payrollBatch}/entries',
               [PayrollEntryController::class, 'index'])->name('payroll.entries.index');
    Route::get('/payroll/{payrollBatch}/entries/{entry}',
               [PayrollEntryController::class, 'show'])->name('payroll.entries.show');
    Route::put('/payroll/{payrollBatch}/entries/{entry}',
               [PayrollEntryController::class, 'update'])->name('payroll.entries.update');
    Route::get('/payroll/{payrollBatch}/payslip/{entry}',
               [PayrollEntryController::class, 'payslip'])->name('payroll.payslip');

    // ── Special Payroll ──────────────────────────────────────────
    Route::resource('special-payroll', SpecialPayrollController::class);
    Route::post('/special-payroll/{specialPayrollBatch}/approve',
                [SpecialPayrollController::class, 'approve'])->name('special-payroll.approve');

    // ── Office Orders ────────────────────────────────────────────
    Route::resource('office-orders', OfficeOrderController::class);
    Route::post('/office-orders/{officeOrder}/approve',
                [OfficeOrderController::class, 'approve'])->name('office-orders.approve');

    // ── TEV ──────────────────────────────────────────────────────
    Route::resource('tev', TevController::class);
    Route::post('/tev/{tevRequest}/submit',  [TevController::class, 'submit'])->name('tev.submit');
    Route::post('/tev/{tevRequest}/approve', [TevController::class, 'approve'])->name('tev.approve');
    Route::post('/tev/{tevRequest}/certify', [TevController::class, 'certify'])->name('tev.certify');

    Route::post(  '/tev/{tevRequest}/itinerary',
                  [TevItineraryController::class, 'store'])->name('tev.itinerary.store');
    Route::put(   '/tev/{tevRequest}/itinerary/{line}',
                  [TevItineraryController::class, 'update'])->name('tev.itinerary.update');
    Route::delete('/tev/{tevRequest}/itinerary/{line}',
                  [TevItineraryController::class, 'destroy'])->name('tev.itinerary.destroy');

    // ── Reports ──────────────────────────────────────────────────
    Route::get('/reports',                    [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/payroll-register',   [ReportController::class, 'payrollRegister'])->name('reports.payroll-register');
    Route::get('/reports/payslip',            [ReportController::class, 'payslip'])->name('reports.payslip');
    Route::get('/reports/gsis-summary',       [ReportController::class, 'gsisSummary'])->name('reports.gsis-summary');
    Route::get('/reports/gsis-detailed',      [ReportController::class, 'gsisDetailed'])->name('reports.gsis-detailed');
    Route::get('/reports/hdmf-p1',            [ReportController::class, 'hdmfP1'])->name('reports.hdmf-p1');
    Route::get('/reports/hdmf-p2',            [ReportController::class, 'hdmfP2'])->name('reports.hdmf-p2');
    Route::get('/reports/hdmf-mpl',           [ReportController::class, 'hdmfMpl'])->name('reports.hdmf-mpl');
    Route::get('/reports/hdmf-cal',           [ReportController::class, 'hdmfCal'])->name('reports.hdmf-cal');
    Route::get('/reports/hdmf-housing',       [ReportController::class, 'hdmfHousing'])->name('reports.hdmf-housing');
    Route::get('/reports/caress-union',       [ReportController::class, 'caressUnion'])->name('reports.caress-union');
    Route::get('/reports/caress-mortuary',    [ReportController::class, 'caressMortuary'])->name('reports.caress-mortuary');
    Route::get('/reports/lbp-loan',           [ReportController::class, 'lbpLoan'])->name('reports.lbp-loan');
    Route::get('/reports/mass',               [ReportController::class, 'mass'])->name('reports.mass');
    Route::get('/reports/provident-fund',     [ReportController::class, 'providentFund'])->name('reports.provident-fund');
    Route::get('/reports/btr-refund',         [ReportController::class, 'btrRefund'])->name('reports.btr-refund');

    Route::get('/reports/tev/{tevRequest}/itinerary',
               [ReportController::class, 'tevItinerary'])->name('reports.tev-itinerary');
    Route::get('/reports/tev/{tevRequest}/travel-completed',
               [ReportController::class, 'tevTravelCompleted'])->name('reports.tev-travel-completed');
    Route::get('/reports/tev/{tevRequest}/annex-a',
               [ReportController::class, 'tevAnnexA'])->name('reports.tev-annex-a');

    // ── Users ────────────────────────────────────────────────────
    Route::resource('users', UserController::class);

});

/*
|--------------------------------------------------------------------------
| AJAX API Routes — Auth required, /api prefix
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/sit', [SalaryIndexTableController::class, 'lookup'])->name('api.sit');
});