<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\FinanceTransactionController;
use App\Http\Controllers\Master\BankAccountController;
use App\Http\Controllers\Master\ChecklistCategoryController;
use App\Http\Controllers\Master\ChecklistItemController;
use App\Http\Controllers\Master\FinanceCategoryController;
use App\Http\Controllers\Master\FinanceItemController;
use App\Http\Controllers\Master\MaterialCategoryController;
use App\Http\Controllers\Master\MaterialController;
use App\Http\Controllers\Master\PromoPackageController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Operasional\CustomerController;
use App\Http\Controllers\Operasional\MaterialInventoryController;
use App\Http\Controllers\Operasional\MaterialPurchaseController;
use App\Http\Controllers\Operasional\OrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Role\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    Route::middleware('can:notifications.view')->group(function () {
        Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifikasi/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifikasi/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    });

    // Master Data - User
    Route::middleware('can:users.view')->group(function () {
        Route::get('/master/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/master/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/master/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/master/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/master/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Pengaturan - Roles
    Route::middleware('can:roles.view')->group(function () {
        Route::get('/pengaturan/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/pengaturan/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/pengaturan/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/pengaturan/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/pengaturan/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Master Data - Checklist Categories
    Route::middleware('can:checklist.view')->group(function () {
        Route::get('/master/checklist-categories', [ChecklistCategoryController::class, 'index'])->name('checklist-categories.index');
        Route::post('/master/checklist-categories', [ChecklistCategoryController::class, 'store'])->name('checklist-categories.store');
        Route::get('/master/checklist-categories/{checklist_category}/edit', [ChecklistCategoryController::class, 'edit'])->name('checklist-categories.edit');
        Route::put('/master/checklist-categories/{checklist_category}', [ChecklistCategoryController::class, 'update'])->name('checklist-categories.update');
        Route::delete('/master/checklist-categories/{checklist_category}', [ChecklistCategoryController::class, 'destroy'])->name('checklist-categories.destroy');
    });

    // Master Data - Checklist Items
    Route::middleware('can:checklist.view')->group(function () {
        Route::get('/master/checklist-items', [ChecklistItemController::class, 'index'])->name('checklist-items.index');
        Route::post('/master/checklist-items', [ChecklistItemController::class, 'store'])->name('checklist-items.store');
        Route::get('/master/checklist-items/{checklist_item}/edit', [ChecklistItemController::class, 'edit'])->name('checklist-items.edit');
        Route::put('/master/checklist-items/{checklist_item}', [ChecklistItemController::class, 'update'])->name('checklist-items.update');
        Route::delete('/master/checklist-items/{checklist_item}', [ChecklistItemController::class, 'destroy'])->name('checklist-items.destroy');
    });

    // Master Data - Material Categories
    Route::middleware('can:materials.view')->group(function () {
        Route::get('/master/material-categories', [MaterialCategoryController::class, 'index'])->name('material-categories.index');
        Route::post('/master/material-categories', [MaterialCategoryController::class, 'store'])->name('material-categories.store');
        Route::get('/master/material-categories/{material_category}/edit', [MaterialCategoryController::class, 'edit'])->name('material-categories.edit');
        Route::put('/master/material-categories/{material_category}', [MaterialCategoryController::class, 'update'])->name('material-categories.update');
        Route::delete('/master/material-categories/{material_category}', [MaterialCategoryController::class, 'destroy'])->name('material-categories.destroy');
    });

    // Master Data - Materials
    Route::middleware('can:materials.view')->group(function () {
        Route::get('/master/materials', [MaterialController::class, 'index'])->name('materials.index');
        Route::post('/master/materials', [MaterialController::class, 'store'])->name('materials.store');
        Route::get('/master/materials/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit');
        Route::put('/master/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
        Route::delete('/master/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
    });

    // Master Data - Promo Packages
    Route::middleware('can:promo.view')->group(function () {
        Route::get('/master/promo-packages', [PromoPackageController::class, 'index'])->name('promo-packages.index');
        Route::post('/master/promo-packages', [PromoPackageController::class, 'store'])->name('promo-packages.store');
        Route::get('/master/promo-packages/{promo_package}/edit', [PromoPackageController::class, 'edit'])->name('promo-packages.edit');
        Route::put('/master/promo-packages/{promo_package}', [PromoPackageController::class, 'update'])->name('promo-packages.update');
        Route::delete('/master/promo-packages/{promo_package}', [PromoPackageController::class, 'destroy'])->name('promo-packages.destroy');
    });

    // Master Data - Keuangan
    Route::middleware('can:finance-master.view')->group(function () {
        Route::get('/master/finance-categories', [FinanceCategoryController::class, 'index'])->name('finance-categories.index');
        Route::post('/master/finance-categories', [FinanceCategoryController::class, 'store'])->middleware('can:finance-master.create')->name('finance-categories.store');
        Route::get('/master/finance-categories/{finance_category}/edit', [FinanceCategoryController::class, 'edit'])->middleware('can:finance-master.edit')->name('finance-categories.edit');
        Route::put('/master/finance-categories/{finance_category}', [FinanceCategoryController::class, 'update'])->middleware('can:finance-master.edit')->name('finance-categories.update');
        Route::delete('/master/finance-categories/{finance_category}', [FinanceCategoryController::class, 'destroy'])->middleware('can:finance-master.delete')->name('finance-categories.destroy');

        Route::get('/master/finance-items', [FinanceItemController::class, 'index'])->name('finance-items.index');
        Route::post('/master/finance-items', [FinanceItemController::class, 'store'])->middleware('can:finance-master.create')->name('finance-items.store');
        Route::get('/master/finance-items/{finance_item}/edit', [FinanceItemController::class, 'edit'])->middleware('can:finance-master.edit')->name('finance-items.edit');
        Route::put('/master/finance-items/{finance_item}', [FinanceItemController::class, 'update'])->middleware('can:finance-master.edit')->name('finance-items.update');
        Route::delete('/master/finance-items/{finance_item}', [FinanceItemController::class, 'destroy'])->middleware('can:finance-master.delete')->name('finance-items.destroy');
    });

    Route::middleware('can:bank-accounts.view')->group(function () {
        Route::get('/master/bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts.index');
        Route::post('/master/bank-accounts', [BankAccountController::class, 'store'])->middleware('can:bank-accounts.create')->name('bank-accounts.store');
        Route::post('/master/bank-accounts/transfer', [BankAccountController::class, 'transfer'])->middleware('can:bank-accounts.edit')->name('bank-accounts.transfer');
        Route::get('/master/bank-accounts/{bank_account}/edit', [BankAccountController::class, 'edit'])->middleware('can:bank-accounts.edit')->name('bank-accounts.edit');
        Route::post('/master/bank-accounts/{bank_account}/straighten-balance', [BankAccountController::class, 'straightenBalance'])->middleware('can:bank-accounts.edit')->name('bank-accounts.straighten-balance');
        Route::get('/master/bank-accounts/{bank_account}', [BankAccountController::class, 'show'])->name('bank-accounts.show');
        Route::put('/master/bank-accounts/{bank_account}', [BankAccountController::class, 'update'])->middleware('can:bank-accounts.edit')->name('bank-accounts.update');
        Route::delete('/master/bank-accounts/{bank_account}', [BankAccountController::class, 'destroy'])->middleware('can:bank-accounts.delete')->name('bank-accounts.destroy');
    });

    // Operasional - Pelanggan
    Route::middleware('can:customers.view')->group(function () {
        Route::get('/operasional/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/operasional/customers/search-plate', [CustomerController::class, 'searchByPlate'])->name('customers.search-plate');
        Route::post('/operasional/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/operasional/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::get('/operasional/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::put('/operasional/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/operasional/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Operasional - Orders
    Route::middleware('can:orders.view')->group(function () {
        Route::get('/operasional/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/operasional/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::get('/operasional/orders/search-plate', [OrderController::class, 'searchPlate'])->name('orders.search-plate');
        Route::post('/operasional/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/operasional/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        Route::put('/operasional/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::get('/operasional/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('/operasional/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::delete('/operasional/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    });

    // Operasional - Pembelian Material
    Route::middleware('can:purchases.view')->group(function () {
        Route::get('/operasional/pembelian-material', [MaterialPurchaseController::class, 'index'])->name('material-purchases.index');
        Route::get('/operasional/pembelian-material/create', [MaterialPurchaseController::class, 'create'])->middleware('can:purchases.create')->name('material-purchases.create');
        Route::post('/operasional/pembelian-material', [MaterialPurchaseController::class, 'store'])->middleware('can:purchases.create')->name('material-purchases.store');
        Route::get('/operasional/pembelian-material/{transaction}/edit', [MaterialPurchaseController::class, 'edit'])->middleware('can:purchases.edit')->name('material-purchases.edit');
        Route::put('/operasional/pembelian-material/{transaction}', [MaterialPurchaseController::class, 'update'])->middleware('can:purchases.edit')->name('material-purchases.update');
        Route::delete('/operasional/pembelian-material/{transaction}', [MaterialPurchaseController::class, 'destroy'])->middleware('can:purchases.delete')->name('material-purchases.destroy');
        Route::post('/operasional/pembelian-material/{transaction}/accept', [MaterialPurchaseController::class, 'accept'])->middleware('can:purchases.approve')->name('material-purchases.accept');
        Route::post('/operasional/pembelian-material/{transaction}/reject', [MaterialPurchaseController::class, 'reject'])->middleware('can:purchases.approve')->name('material-purchases.reject');
        Route::get('/operasional/pembelian-material/{transaction}', [MaterialPurchaseController::class, 'show'])->name('material-purchases.show');
    });

    // Operasional - Persediaan Material
    Route::middleware('can:materials.view')->group(function () {
        Route::get('/operasional/persediaan-material', [MaterialInventoryController::class, 'index'])->name('material-inventory.index');
        Route::get('/operasional/persediaan-material/{material}/data', [MaterialInventoryController::class, 'data'])->name('material-inventory.data');
        Route::put('/operasional/persediaan-material/{material}', [MaterialInventoryController::class, 'update'])->middleware('can:materials.edit')->name('material-inventory.update');
        Route::post('/operasional/persediaan-material/{material}/adjust', [MaterialInventoryController::class, 'adjust'])->middleware('can:materials.edit')->name('material-inventory.adjust');
        Route::get('/operasional/persediaan-material/{material}', [MaterialInventoryController::class, 'show'])->name('material-inventory.show');
    });

    // Keuangan - Input Keuangan
    Route::middleware('can:finance-transactions.view')->group(function () {
        Route::get('/keuangan/transaksi', [FinanceTransactionController::class, 'index'])->name('finance-transactions.index');
        Route::get('/keuangan/transaksi/create', [FinanceTransactionController::class, 'create'])->middleware('can:finance-transactions.create')->name('finance-transactions.create');
        Route::post('/keuangan/transaksi', [FinanceTransactionController::class, 'store'])->middleware('can:finance-transactions.create')->name('finance-transactions.store');
        Route::get('/keuangan/transaksi/{finance_transaction}/edit', [FinanceTransactionController::class, 'edit'])->middleware('can:finance-transactions.edit')->name('finance-transactions.edit');
        Route::put('/keuangan/transaksi/{finance_transaction}', [FinanceTransactionController::class, 'update'])->middleware('can:finance-transactions.edit')->name('finance-transactions.update');
        Route::delete('/keuangan/transaksi/{finance_transaction}', [FinanceTransactionController::class, 'destroy'])->middleware('can:finance-transactions.delete')->name('finance-transactions.destroy');
        Route::post('/keuangan/transaksi/{finance_transaction}/approve', [FinanceTransactionController::class, 'approve'])->middleware('can:finance-transactions.approve')->name('finance-transactions.approve');
        Route::post('/keuangan/transaksi/{finance_transaction}/reject', [FinanceTransactionController::class, 'reject'])->middleware('can:finance-transactions.approve')->name('finance-transactions.reject');
        Route::get('/keuangan/transaksi/{finance_transaction}', [FinanceTransactionController::class, 'show'])->name('finance-transactions.show');
    });
});

require __DIR__.'/auth.php';
