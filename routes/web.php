<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\InitialStockController;
use App\Http\Controllers\Admin\LedgerAccountController;
use App\Http\Controllers\Admin\PartyController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\StockReportController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CurrentSiteController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

// Public company website — the e-commerce storefront link only appears
// once "Enable E-commerce" is switched on from Admin > Settings.
Route::get('/', [WebsiteController::class, 'home'])->name('home');
Route::get('/shop', [WebsiteController::class, 'shop'])->name('shop');

// UI language switch — not an authenticated action, just a session preference.
Route::post('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Backend/admin panel — kept under /admin so the public company website
// (and its optional e-commerce storefront, toggled from Settings) can
// live at the root without colliding with these routes.
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified', 'current-site'])->name('dashboard');

    Route::middleware(['auth', 'current-site'])->group(function () {
        Route::get('/select-site', [CurrentSiteController::class, 'select'])->name('sites.select');
        Route::post('/switch-site', [CurrentSiteController::class, 'switch'])->name('sites.switch');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Per-action permission checks live in each controller's middleware() method
        Route::resource('users', UserController::class)->except('show');
        Route::resource('roles', RoleController::class)->except('show');
        Route::resource('sites', SiteController::class)->except('show');
        Route::patch('/sites/{site}/toggle-status', [SiteController::class, 'toggleStatus'])->name('sites.toggle-status');

        // Inventory master data (global — not site-scoped; stock levels per Site come later)
        Route::resource('categories', CategoryController::class)->except('show');
        Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::resource('brands', BrandController::class)->except('show');
        Route::patch('/brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggle-status');
        Route::resource('units', UnitController::class)->except('show');
        Route::patch('/units/{unit}/toggle-status', [UnitController::class, 'toggleStatus'])->name('units.toggle-status');
        Route::resource('attributes', \App\Http\Controllers\Admin\AttributeController::class)->except('show');
        Route::resource('products', ProductController::class)->except('show');
        Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::get('/products/barcode', [ProductController::class, 'barcode'])->name('products.barcode');

        Route::get('/stock/initial-stock', [InitialStockController::class, 'index'])->name('stock.initial.index');
        Route::post('/stock/initial-stock', [InitialStockController::class, 'store'])->name('stock.initial.store');
        Route::get('/stock/report', [StockReportController::class, 'index'])->name('stock.report');
        Route::get('/stock/adjustment', [StockAdjustmentController::class, 'index'])->name('stock.adjustment.index');
        Route::post('/stock/adjustment', [StockAdjustmentController::class, 'store'])->name('stock.adjustment.store');

        Route::get('/stock/transfers', [StockTransferController::class, 'index'])->name('stock.transfers.index');
        Route::get('/stock/transfers/create', [StockTransferController::class, 'create'])->name('stock.transfers.create');
        Route::post('/stock/transfers', [StockTransferController::class, 'store'])->name('stock.transfers.store');
        Route::get('/stock/transfers/{transfer}', [StockTransferController::class, 'show'])->name('stock.transfers.show');
        Route::post('/stock/transfers/{transfer}/receive', [StockTransferController::class, 'receive'])->name('stock.transfers.receive');
        Route::post('/stock/transfers/{transfer}/cancel', [StockTransferController::class, 'cancel'])->name('stock.transfers.cancel');

        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/purchases/{purchase}/receive', [PurchaseController::class, 'receiveForm'])->name('purchases.receive.create');
        Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive.store');
        Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');

        Route::resource('parties', PartyController::class)->except('show');
        Route::patch('/parties/{party}/toggle-status', [PartyController::class, 'toggleStatus'])->name('parties.toggle-status');
        Route::get('/parties/{party}/ledger', [PartyController::class, 'ledger'])->name('parties.ledger');

        // Accounting foundation — see docs/accounting-foundation.md. Only
        // Bank Accounts has a CRUD screen in Phase 1; every other
        // ledger_accounts group is system-seeded.
        Route::resource('bank-accounts', LedgerAccountController::class)->except('show')
            ->parameters(['bank-accounts' => 'bankAccount']);
        Route::patch('/bank-accounts/{bankAccount}/toggle-status', [LedgerAccountController::class, 'toggleStatus'])->name('bank-accounts.toggle-status');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';
