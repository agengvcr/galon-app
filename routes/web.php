<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeAttendanceController;
use App\Http\Controllers\OperationalExpenseController;
use App\Http\Controllers\EmployeeLoanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth'])->group(function () {
    // Move all your protected routes here
    Route::get('/', [HomeController::class, 'index'])->name('home');
    // Customer routes
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Transaction routes
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::post('/transactions/batch', [TransactionController::class, 'storeBatch'])->name('transactions.storeBatch');
    Route::get('/transactions/summary', [TransactionController::class, 'getSummary'])->name('transactions.summary');

    // If you need any additional custom routes for transactions, you can add them here.
    // For example:
    // Route::get('transactions/report', [TransactionController::class, 'report'])->name('transactions.report');

    Route::get('/transactions/customers', [TransactionController::class, 'getCustomers'])->name('transactions.customers');
    Route::get('/transactions/customer-galon', [TransactionController::class, 'getCustomerGalon'])->name('transactions.customer-galon');
    Route::get('/transactions/customers-by-date', [App\Http\Controllers\TransactionController::class, 'customersByDate'])->name('transactions.customers-by-date');

    // Add these routes with your existing routes
    Route::get('/reports/monthly-table', [ReportController::class, 'monthlyReport'])->name('reports.monthlyTable');
    Route::get('/reports/monthly-table-data', [ReportController::class, 'getMonthlyTableData'])->name('reports.monthlyTableData');
    Route::get('/reports/payroll', [ReportController::class, 'payrollReport'])->name('reports.payroll');
    Route::get('/reports/galon-stock', [ReportController::class, 'galonStockReport'])->name('reports.galonStock');
    Route::get('/reports/payroll/detail/{employee}/{start}/{end}', [App\Http\Controllers\ReportController::class, 'payrollDetail'])->name('reports.payrollDetail');
    
    // Home page API endpoints
    Route::get('/reports/inactive-customers', [ReportController::class, 'getInactiveCustomers'])->name('reports.inactiveCustomers');

    // Debt routes
    Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
    Route::post('/debts', [DebtController::class, 'store'])->name('debts.store');
    Route::get('/debts/{id}/edit', [DebtController::class, 'edit'])->name('debts.edit');
    Route::put('/debts/{id}', [DebtController::class, 'update'])->name('debts.update');
    Route::delete('/debts/{id}', [DebtController::class, 'destroy'])->name('debts.destroy');
    Route::post('/debts/{id}/payment', [DebtController::class, 'updatePayment'])->name('debts.updatePayment');
    Route::get('/debts/customer/{customer}', [DebtController::class, 'getCustomerDebtSummary'])->name('debts.customerSummary');
    Route::get('/debts/create', [DebtController::class, 'create'])->name('debts.create');
    Route::get('/debts/customers', [DebtController::class, 'getCustomers'])->name('debts.customers');
    Route::get('/debts/summary-by-date', [DebtController::class, 'getSummaryByDate'])->name('debts.summaryByDate');
    Route::get('/debts/statistics', [DebtController::class, 'getStatistics'])->name('debts.statistics');
    Route::get('/debts/{id}/payments', [DebtController::class, 'paymentHistory']);

    // Employee routes
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    // Employee Attendance routes
    Route::resource('employee-attendance', EmployeeAttendanceController::class);

    // Operational Expenses routes
    Route::resource('operational-expenses', OperationalExpenseController::class);

    // Employee Loans routes
    Route::resource('employee-loans', EmployeeLoanController::class);
});

// Keep these routes outside the middleware group
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Customer routes



