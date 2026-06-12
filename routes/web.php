<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\AssetPurchaseController;
use App\Http\Controllers\Finance\DebtReceivableController;
use App\Http\Controllers\Finance\FinanceTransactionController;
use App\Http\Controllers\Finance\RevenueSharingController;
use App\Http\Controllers\Master\AssetCategoryController;
use App\Http\Controllers\Master\BankAccountController;
use App\Http\Controllers\Master\ChecklistCategoryController;
use App\Http\Controllers\Master\ChecklistItemController;
use App\Http\Controllers\Master\DebtReceivableCategoryController;
use App\Http\Controllers\Master\FinanceCategoryController;
use App\Http\Controllers\Master\FinanceItemController;
use App\Http\Controllers\Master\MaterialCategoryController;
use App\Http\Controllers\Master\MaterialController;
use App\Http\Controllers\Master\PromoPackageController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\MyAccountController;
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

Route::get('/invoice/order/{token}', [OrderController::class, 'publicInvoice'])->name('orders.invoice.share');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    Route::get('/my-account', [MyAccountController::class, 'edit'])->name('my-account.edit');
    Route::put('/my-account/profile', [MyAccountController::class, 'updateProfile'])->name('my-account.profile');
    Route::put('/my-account/password', [MyAccountController::class, 'updatePassword'])->name('my-account.password');

    Route::middleware('can:notifications.view')->group(function () {
        Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifikasi/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifikasi/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    });

    // Master Data - User
    Route::middleware('can:users.view')->group(function () {
        Route::get('/master/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/master/users', [UserController::class, 'store'])->middleware('can:users.create')->name('users.store');
        Route::get('/master/users/{user}/edit', [UserController::class, 'edit'])->middleware('can:users.edit')->name('users.edit');
        Route::put('/master/users/{user}', [UserController::class, 'update'])->middleware('can:users.edit')->name('users.update');
        Route::delete('/master/users/{user}', [UserController::class, 'destroy'])->middleware('can:users.delete')->name('users.destroy');
    });

    // Pengaturan - Roles
    Route::middleware('can:roles.view')->group(function () {
        Route::get('/pengaturan/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/pengaturan/roles', [RoleController::class, 'store'])->middleware('can:roles.create')->name('roles.store');
        Route::get('/pengaturan/roles/{role}/edit', [RoleController::class, 'edit'])->middleware('can:roles.edit')->name('roles.edit');
        Route::put('/pengaturan/roles/{role}', [RoleController::class, 'update'])->middleware('can:roles.edit')->name('roles.update');
        Route::delete('/pengaturan/roles/{role}', [RoleController::class, 'destroy'])->middleware('can:roles.delete')->name('roles.destroy');
    });

    // Master Data - Checklist Categories
    Route::middleware('can:checklist.view')->group(function () {
        Route::get('/master/checklist-categories', [ChecklistCategoryController::class, 'index'])->name('checklist-categories.index');
        Route::post('/master/checklist-categories', [ChecklistCategoryController::class, 'store'])->middleware('can:checklist.create')->name('checklist-categories.store');
        Route::get('/master/checklist-categories/{checklist_category}/edit', [ChecklistCategoryController::class, 'edit'])->middleware('can:checklist.edit')->name('checklist-categories.edit');
        Route::put('/master/checklist-categories/{checklist_category}', [ChecklistCategoryController::class, 'update'])->middleware('can:checklist.edit')->name('checklist-categories.update');
        Route::delete('/master/checklist-categories/{checklist_category}', [ChecklistCategoryController::class, 'destroy'])->middleware('can:checklist.delete')->name('checklist-categories.destroy');
    });

    // Master Data - Checklist Items
    Route::middleware('can:checklist.view')->group(function () {
        Route::get('/master/checklist-items', [ChecklistItemController::class, 'index'])->name('checklist-items.index');
        Route::post('/master/checklist-items', [ChecklistItemController::class, 'store'])->middleware('can:checklist.create')->name('checklist-items.store');
        Route::get('/master/checklist-items/{checklist_item}/edit', [ChecklistItemController::class, 'edit'])->middleware('can:checklist.edit')->name('checklist-items.edit');
        Route::put('/master/checklist-items/{checklist_item}', [ChecklistItemController::class, 'update'])->middleware('can:checklist.edit')->name('checklist-items.update');
        Route::delete('/master/checklist-items/{checklist_item}', [ChecklistItemController::class, 'destroy'])->middleware('can:checklist.delete')->name('checklist-items.destroy');
    });

    // Master Data - Material Categories
    Route::middleware('can:materials.view')->group(function () {
        Route::get('/master/material-categories', [MaterialCategoryController::class, 'index'])->name('material-categories.index');
        Route::post('/master/material-categories', [MaterialCategoryController::class, 'store'])->middleware('can:materials.create')->name('material-categories.store');
        Route::get('/master/material-categories/{material_category}/edit', [MaterialCategoryController::class, 'edit'])->middleware('can:materials.edit')->name('material-categories.edit');
        Route::put('/master/material-categories/{material_category}', [MaterialCategoryController::class, 'update'])->middleware('can:materials.edit')->name('material-categories.update');
        Route::delete('/master/material-categories/{material_category}', [MaterialCategoryController::class, 'destroy'])->middleware('can:materials.delete')->name('material-categories.destroy');
    });

    // Master Data - Materials
    Route::middleware('can:materials.view')->group(function () {
        Route::get('/master/materials', [MaterialController::class, 'index'])->name('materials.index');
        Route::post('/master/materials', [MaterialController::class, 'store'])->middleware('can:materials.create')->name('materials.store');
        Route::get('/master/materials/{material}/edit', [MaterialController::class, 'edit'])->middleware('can:materials.edit')->name('materials.edit');
        Route::put('/master/materials/{material}', [MaterialController::class, 'update'])->middleware('can:materials.edit')->name('materials.update');
        Route::delete('/master/materials/{material}', [MaterialController::class, 'destroy'])->middleware('can:materials.delete')->name('materials.destroy');
    });

    // Master Data - Promo Packages
    Route::middleware('can:promo.view')->group(function () {
        Route::get('/master/promo-packages', [PromoPackageController::class, 'index'])->name('promo-packages.index');
        Route::post('/master/promo-packages', [PromoPackageController::class, 'store'])->middleware('can:promo.create')->name('promo-packages.store');
        Route::get('/master/promo-packages/{promo_package}/edit', [PromoPackageController::class, 'edit'])->middleware('can:promo.edit')->name('promo-packages.edit');
        Route::put('/master/promo-packages/{promo_package}', [PromoPackageController::class, 'update'])->middleware('can:promo.edit')->name('promo-packages.update');
        Route::delete('/master/promo-packages/{promo_package}', [PromoPackageController::class, 'destroy'])->middleware('can:promo.delete')->name('promo-packages.destroy');
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

        Route::get('/master/asset-categories', [AssetCategoryController::class, 'index'])->name('asset-categories.index');
        Route::post('/master/asset-categories', [AssetCategoryController::class, 'store'])->middleware('can:finance-master.create')->name('asset-categories.store');
        Route::get('/master/asset-categories/{asset_category}/edit', [AssetCategoryController::class, 'edit'])->middleware('can:finance-master.edit')->name('asset-categories.edit');
        Route::put('/master/asset-categories/{asset_category}', [AssetCategoryController::class, 'update'])->middleware('can:finance-master.edit')->name('asset-categories.update');
        Route::delete('/master/asset-categories/{asset_category}', [AssetCategoryController::class, 'destroy'])->middleware('can:finance-master.delete')->name('asset-categories.destroy');

        Route::get('/master/debt-receivable-categories', [DebtReceivableCategoryController::class, 'index'])->name('debt-receivable-categories.index');
        Route::post('/master/debt-receivable-categories', [DebtReceivableCategoryController::class, 'store'])->middleware('can:finance-master.create')->name('debt-receivable-categories.store');
        Route::get('/master/debt-receivable-categories/{debt_receivable_category}/edit', [DebtReceivableCategoryController::class, 'edit'])->middleware('can:finance-master.edit')->name('debt-receivable-categories.edit');
        Route::put('/master/debt-receivable-categories/{debt_receivable_category}', [DebtReceivableCategoryController::class, 'update'])->middleware('can:finance-master.edit')->name('debt-receivable-categories.update');
        Route::delete('/master/debt-receivable-categories/{debt_receivable_category}', [DebtReceivableCategoryController::class, 'destroy'])->middleware('can:finance-master.delete')->name('debt-receivable-categories.destroy');
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
        Route::post('/operasional/customers', [CustomerController::class, 'store'])->middleware('can:customers.create')->name('customers.store');
        Route::get('/operasional/customers/{customer}/edit', [CustomerController::class, 'edit'])->middleware('can:customers.edit')->name('customers.edit');
        Route::get('/operasional/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::put('/operasional/customers/{customer}', [CustomerController::class, 'update'])->middleware('can:customers.edit')->name('customers.update');
        Route::delete('/operasional/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('can:customers.delete')->name('customers.destroy');
    });

    // Operasional - Orders
    Route::middleware('can:orders.view')->group(function () {
        Route::get('/operasional/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/operasional/orders/create', [OrderController::class, 'create'])->middleware('can:orders.create')->name('orders.create');
        Route::get('/operasional/orders/search-plate', [OrderController::class, 'searchPlate'])->name('orders.search-plate');
        Route::post('/operasional/orders', [OrderController::class, 'store'])->middleware('can:orders.create')->name('orders.store');
        Route::get('/operasional/orders/{order}/edit', [OrderController::class, 'edit'])->middleware('can:orders.edit')->name('orders.edit');
        Route::put('/operasional/orders/{order}', [OrderController::class, 'update'])->middleware('can:orders.edit')->name('orders.update');
        Route::get('/operasional/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('/operasional/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::delete('/operasional/orders/{order}', [OrderController::class, 'destroy'])->middleware('can:orders.delete')->name('orders.destroy');
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
        Route::get('/keuangan/transaksi/neraca', [FinanceTransactionController::class, 'balanceSheet'])->name('finance-transactions.balance-sheet');
        Route::post('/keuangan/transaksi/neraca/cutoff', [FinanceTransactionController::class, 'storeBalanceCutoff'])->middleware('can:finance-transactions.create')->name('finance-transactions.balance-sheet.cutoff');
        Route::delete('/keuangan/transaksi/neraca/cutoff/{financial_balance_cutoff}', [FinanceTransactionController::class, 'destroyBalanceCutoff'])->middleware('can:finance-transactions.delete')->name('finance-transactions.balance-sheet.cutoff.destroy');
        Route::get('/keuangan/transaksi/create', [FinanceTransactionController::class, 'create'])->middleware('can:finance-transactions.create')->name('finance-transactions.create');
        Route::post('/keuangan/transaksi', [FinanceTransactionController::class, 'store'])->middleware('can:finance-transactions.create')->name('finance-transactions.store');
        Route::post('/keuangan/transaksi/import', [FinanceTransactionController::class, 'import'])->middleware('can:finance-transactions.create')->name('finance-transactions.import');
        Route::get('/keuangan/transaksi/{finance_transaction}/edit', [FinanceTransactionController::class, 'edit'])->middleware('can:finance-transactions.edit')->name('finance-transactions.edit');
        Route::put('/keuangan/transaksi/{finance_transaction}', [FinanceTransactionController::class, 'update'])->middleware('can:finance-transactions.edit')->name('finance-transactions.update');
        Route::delete('/keuangan/transaksi/{finance_transaction}', [FinanceTransactionController::class, 'destroy'])->middleware('can:finance-transactions.delete')->name('finance-transactions.destroy');
        Route::post('/keuangan/transaksi/{finance_transaction}/approve', [FinanceTransactionController::class, 'approve'])->middleware('can:finance-transactions.approve')->name('finance-transactions.approve');
        Route::post('/keuangan/transaksi/{finance_transaction}/reject', [FinanceTransactionController::class, 'reject'])->middleware('can:finance-transactions.approve')->name('finance-transactions.reject');
        Route::get('/keuangan/transaksi/{finance_transaction}', [FinanceTransactionController::class, 'show'])->name('finance-transactions.show');
    });

    // Keuangan - Pembelian Aset
    Route::middleware('can:asset-purchases.view')->group(function () {
        Route::get('/keuangan/pembelian-aset', [AssetPurchaseController::class, 'index'])->name('asset-purchases.index');
        Route::get('/keuangan/pembelian-aset/create', [AssetPurchaseController::class, 'create'])->middleware('can:asset-purchases.create')->name('asset-purchases.create');
        Route::post('/keuangan/pembelian-aset', [AssetPurchaseController::class, 'store'])->middleware('can:asset-purchases.create')->name('asset-purchases.store');
        Route::get('/keuangan/pembelian-aset/{asset_purchase}/edit', [AssetPurchaseController::class, 'edit'])->middleware('can:asset-purchases.edit')->name('asset-purchases.edit');
        Route::put('/keuangan/pembelian-aset/{asset_purchase}', [AssetPurchaseController::class, 'update'])->middleware('can:asset-purchases.edit')->name('asset-purchases.update');
        Route::post('/keuangan/pembelian-aset/{asset_purchase}/approve', [AssetPurchaseController::class, 'approve'])->middleware('can:asset-purchases.approve')->name('asset-purchases.approve');
        Route::post('/keuangan/pembelian-aset/{asset_purchase}/reject', [AssetPurchaseController::class, 'reject'])->middleware('can:asset-purchases.approve')->name('asset-purchases.reject');
        Route::post('/keuangan/pembelian-aset/{asset_purchase}/condition', [AssetPurchaseController::class, 'updateCondition'])->middleware('can:asset-purchases.edit')->name('asset-purchases.condition');
        Route::delete('/keuangan/pembelian-aset/{asset_purchase}', [AssetPurchaseController::class, 'destroy'])->middleware('can:asset-purchases.delete')->name('asset-purchases.destroy');
        Route::get('/keuangan/pembelian-aset/{asset_purchase}', [AssetPurchaseController::class, 'show'])->name('asset-purchases.show');
    });

    // Keuangan - Hutang Piutang
    Route::middleware('can:debt-receivables.view')->group(function () {
        Route::get('/keuangan/hutang-piutang', [DebtReceivableController::class, 'index'])->name('debt-receivables.index');
        Route::get('/keuangan/hutang-piutang/create', [DebtReceivableController::class, 'create'])->middleware('can:debt-receivables.create')->name('debt-receivables.create');
        Route::post('/keuangan/hutang-piutang', [DebtReceivableController::class, 'store'])->middleware('can:debt-receivables.create')->name('debt-receivables.store');
        Route::get('/keuangan/hutang-piutang/{debt_receivable}/edit', [DebtReceivableController::class, 'edit'])->middleware('can:debt-receivables.edit')->name('debt-receivables.edit');
        Route::put('/keuangan/hutang-piutang/{debt_receivable}', [DebtReceivableController::class, 'update'])->middleware('can:debt-receivables.edit')->name('debt-receivables.update');
        Route::post('/keuangan/hutang-piutang/{debt_receivable}/approve', [DebtReceivableController::class, 'approve'])->middleware('can:debt-receivables.approve')->name('debt-receivables.approve');
        Route::post('/keuangan/hutang-piutang/{debt_receivable}/reject', [DebtReceivableController::class, 'reject'])->middleware('can:debt-receivables.approve')->name('debt-receivables.reject');
        Route::post('/keuangan/hutang-piutang/{debt_receivable}/pay', [DebtReceivableController::class, 'pay'])->middleware('can:debt-receivables.edit')->name('debt-receivables.pay');
        Route::put('/keuangan/hutang-piutang/{debt_receivable}/payments/{payment}', [DebtReceivableController::class, 'updatePayment'])->middleware('can:debt-receivables.edit')->name('debt-receivables.payments.update');
        Route::delete('/keuangan/hutang-piutang/{debt_receivable}/payments/{payment}', [DebtReceivableController::class, 'destroyPayment'])->middleware('can:debt-receivables.delete')->name('debt-receivables.payments.destroy');
        Route::delete('/keuangan/hutang-piutang/{debt_receivable}', [DebtReceivableController::class, 'destroy'])->middleware('can:debt-receivables.delete')->name('debt-receivables.destroy');
        Route::get('/keuangan/hutang-piutang/{debt_receivable}', [DebtReceivableController::class, 'show'])->name('debt-receivables.show');
    });

    // Keuangan - Revenue Sharing
    Route::middleware('can:revenue-sharings.view')->group(function () {
        Route::get('/keuangan/revenue-sharing', [RevenueSharingController::class, 'index'])->name('revenue-sharings.index');
        Route::post('/keuangan/revenue-sharing/cutoffs', [RevenueSharingController::class, 'storeCutoff'])->middleware('can:revenue-sharings.create')->name('revenue-sharings.cutoffs.store');
        Route::delete('/keuangan/revenue-sharing/cutoffs/{revenue_cutoff}', [RevenueSharingController::class, 'destroyCutoff'])->middleware('can:revenue-sharings.delete')->name('revenue-sharings.cutoffs.destroy');
        Route::get('/keuangan/revenue-sharing/create', [RevenueSharingController::class, 'create'])->middleware('can:revenue-sharings.create')->name('revenue-sharings.create');
        Route::post('/keuangan/revenue-sharing', [RevenueSharingController::class, 'store'])->middleware('can:revenue-sharings.create')->name('revenue-sharings.store');
        Route::get('/keuangan/revenue-sharing/{revenue_sharing}/edit', [RevenueSharingController::class, 'edit'])->middleware('can:revenue-sharings.edit')->name('revenue-sharings.edit');
        Route::put('/keuangan/revenue-sharing/{revenue_sharing}', [RevenueSharingController::class, 'update'])->middleware('can:revenue-sharings.edit')->name('revenue-sharings.update');
        Route::post('/keuangan/revenue-sharing/{revenue_sharing}/approve', [RevenueSharingController::class, 'approve'])->middleware('can:revenue-sharings.approve')->name('revenue-sharings.approve');
        Route::post('/keuangan/revenue-sharing/{revenue_sharing}/reject', [RevenueSharingController::class, 'reject'])->middleware('can:revenue-sharings.approve')->name('revenue-sharings.reject');
        Route::delete('/keuangan/revenue-sharing/{revenue_sharing}', [RevenueSharingController::class, 'destroy'])->middleware('can:revenue-sharings.delete')->name('revenue-sharings.destroy');
        Route::get('/keuangan/revenue-sharing/{revenue_sharing}', [RevenueSharingController::class, 'show'])->name('revenue-sharings.show');
    });
});

require __DIR__.'/auth.php';
