<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\LoginController;

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

    // If you need any additional custom routes for transactions, you can add them here.
    // For example:
    // Route::get('transactions/report', [TransactionController::class, 'report'])->name('transactions.report');

    Route::get('/transactions/customers', [TransactionController::class, 'getCustomers'])->name('transactions.customers');

    // Add these routes with your existing routes
    Route::get('/reports/chart', [ReportController::class, 'transactionChart'])->name('reports.chart');
    Route::get('/reports/chart-data', [ReportController::class, 'getChartData'])->name('reports.chartData');
    Route::get('/reports/price-chart', [ReportController::class, 'priceChart'])->name('reports.priceChart');
    Route::get('/reports/price-chart-data', [ReportController::class, 'getPriceChartData'])->name('reports.priceChartData');
    Route::get('/reports/monthly-table', [ReportController::class, 'monthlyReport'])->name('reports.monthlyTable');
    Route::get('/reports/monthly-table-data', [ReportController::class, 'getMonthlyTableData'])->name('reports.monthlyTableData');
});

// Keep these routes outside the middleware group
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Customer routes



