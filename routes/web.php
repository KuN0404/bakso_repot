<?php

use App\Http\Controllers\ExportController;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Modifiers;
use App\Livewire\Admin\PaymentSources;
use App\Livewire\Admin\Products;
use App\Livewire\Admin\ProductSalesReport;
use App\Livewire\Admin\Roles;
use App\Livewire\Admin\SalesReport;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\ShiftReport;
use App\Livewire\Admin\Shifts;
use App\Livewire\Admin\TransactionHistory;
use App\Livewire\Admin\Users;
use App\Livewire\PosCheckout;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Login
Route::get('/login', [App\Http\Controllers\LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [App\Http\Controllers\LoginController::class, 'login'])->name('login.post')->middleware('guest');

// Logout — must be auth so CSRF session is still valid
Route::middleware('auth')->group(function () {
    Route::post('/logout', [App\Http\Controllers\LoginController::class, 'logout'])->name('logout');
});

// Admin Routes
// PENTING: sebelumnya route ini hanya dilindungi 'auth' — role apa pun yang login
// (termasuk Kasir/Kitchen) bisa buka /admin/users, /admin/roles, /admin/settings,
// dan laporan keuangan langsung lewat URL. Setiap route sekarang wajib 'can:xxx'
// yang sesuai; jangan hapus middleware ini lagi.
Route::middleware(['auth', 'throttle:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');

    // Master Data (Hanya Detail Produk untuk referensi laporan)
    Route::get('/products/{product}', App\Livewire\Admin\ProductDetail::class)->name('products.show')->middleware('can:view_products');

    // Transactions & Shifts
    Route::get('/shifts', Shifts::class)->name('shifts.index')->middleware('can:view_own_shifts');
    Route::get('/returns', App\Livewire\Admin\Returns::class)->name('returns')->middleware('can:view_returns');

    // Reports
    Route::get('/reports/transactions', TransactionHistory::class)->name('reports.transactions')->middleware('can:view_transactions');
    Route::get('/reports/sales', SalesReport::class)->name('reports.sales')->middleware('can:view_sales_reports');
    Route::get('/reports/products', ProductSalesReport::class)->name('reports.products')->middleware('can:view_sales_reports');
    Route::get('/reports/shifts', ShiftReport::class)->name('reports.shifts')->middleware('can:view_all_shifts');
    Route::get('/reports/inventory', App\Livewire\Admin\InventoryReport::class)->name('reports.inventory')->middleware('can:view_inventory_reports');

    // Settings
    Route::get('/users', Users::class)->name('users.index')->middleware('can:view_users');
    Route::get('/roles', Roles::class)->name('roles.index')->middleware('can:manage_roles');
    Route::get('/settings', Settings::class)->name('settings.index')->middleware('can:manage_settings');
});

// Print Routes
// Sama seperti halaman admin di atas — dulu cuma 'auth', jadi siapa pun yang login
// bisa akses URL print langsung tanpa pernah melewati halaman laporan yang di-gate izinnya.
Route::middleware(['auth'])->prefix('print')->name('print.')->group(function () {
    Route::get('/transactions/table', [App\Http\Controllers\PrintController::class, 'transactionsTable'])->name('transactions.table')->middleware('can:view_transactions');
    Route::get('/transactions/detail', [App\Http\Controllers\PrintController::class, 'transactionsDetail'])->name('transactions.detail')->middleware('can:view_transactions');
    Route::get('/returns-report', [App\Http\Controllers\PrintController::class, 'returnsReport'])->name('returns-report')->middleware('can:view_returns');
    Route::get('/return-detail/{id}', [App\Http\Controllers\PrintController::class, 'returnDetail'])->name('return-detail')->middleware('can:view_returns');
    Route::get('/sales-report', [App\Http\Controllers\PrintController::class, 'salesReport'])->name('sales-report')->middleware('can:view_sales_reports');
    Route::get('/inventory-report', [App\Http\Controllers\PrintController::class, 'inventoryReport'])->name('inventory-report')->middleware('can:view_inventory_reports');
    Route::get('/transaction/{transaction}', [App\Http\Controllers\PrintController::class, 'transactionSingle'])->name('transaction.single')->middleware('can:view_transactions');
    Route::get('/shifts/table', [App\Http\Controllers\PrintController::class, 'shiftsTable'])->name('shifts.table')->middleware('can:view_all_shifts');
    Route::get('/shift/{shift}', [App\Http\Controllers\PrintController::class, 'shiftDetail'])->name('shift.detail')->middleware('can:view_all_shifts');
    Route::get('/shift/{shift}/custom', [App\Http\Controllers\PrintController::class, 'shiftCustom'])->name('shift.custom')->middleware('can:view_all_shifts');
});

// Export Routes (separate for streaming)
Route::middleware(['auth', 'throttle:5,1'])->name('export.')->prefix('export')->group(function () {
    Route::get('/transactions', [ExportController::class, 'transactions'])->name('transactions')->middleware('can:view_transactions');
    Route::get('/transactions-detail', [ExportController::class, 'transactionsDetail'])->name('transactions.detail')->middleware('can:view_transactions');
    Route::get('/product-sales', [ExportController::class, 'productSales'])->name('product-sales')->middleware('can:view_sales_reports');
    Route::get('/sales-by-category', [ExportController::class, 'salesByCategory'])->name('sales-by-category')->middleware('can:view_sales_reports');
    Route::get('/sales-by-payment-method', [ExportController::class, 'salesByPaymentMethod'])->name('sales-by-payment-method')->middleware('can:view_sales_reports');
    Route::get('/product-returns', [App\Http\Controllers\ExportController::class, 'productReturns'])->name('product-returns')->middleware('can:view_returns');
    Route::get('/shifts', [ExportController::class, 'shifts'])->name('shifts')->middleware('can:view_all_shifts');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/returns/{return}/print', [App\Http\Controllers\ExportController::class, 'printReturn'])->name('returns.print')->middleware('can:view_returns');
});

