<?php

use App\Http\Controllers\Admin\BalanceSheetController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CampaignPageController;
use App\Http\Controllers\Admin\CapitalTransactionController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CourierConsignmentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DayBookController;
use App\Http\Controllers\Admin\DeliveryPartnerController;
use App\Http\Controllers\Admin\DeliveryZoneController;
use App\Http\Controllers\Admin\DueReportController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FundTransferController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\IncomeController;
use App\Http\Controllers\Admin\InitialStockController;
use App\Http\Controllers\Admin\LedgerAccountController;
use App\Http\Controllers\Admin\PartyController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\ProfitLossController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\PurchaseRequisitionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SaleQuotationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\StockReportController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Admin\TrialBalanceController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CurrentSiteController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

// Public company website — the e-commerce storefront link only appears
// once "Enable E-commerce" is switched on from Admin > Settings.
Route::get('/', [WebsiteController::class, 'home'])->name('home');
Route::get('/about', [WebsiteController::class, 'about'])->name('about');
Route::get('/media', [WebsiteController::class, 'media'])->name('media');
Route::get('/career', [WebsiteController::class, 'career'])->name('career');
Route::get('/contact', [WebsiteController::class, 'contact'])->name('contact');
Route::post('/contact', [WebsiteController::class, 'submitContact'])->name('contact.submit');

// The storefront — catalog, cart, guest checkout, order tracking. All
// gated behind the 'ecommerce' middleware (404 until Admin > Settings >
// E-commerce is switched on).
Route::middleware('ecommerce')->group(function () {
    Route::get('/shop', [ShopController::class, 'index'])->name('shop');
    Route::get('/shop/{product}', [ShopController::class, 'show'])->name('shop.product');
    Route::post('/shop/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/cart', [ShopController::class, 'cart'])->name('cart');
    Route::post('/cart', [ShopController::class, 'addToCart'])->name('cart.add');
    Route::patch('/cart/{itemKey}', [ShopController::class, 'updateCart'])->name('cart.update');
    Route::delete('/cart/{itemKey}', [ShopController::class, 'removeFromCart'])->name('cart.remove');

    Route::get('/checkout', [ShopController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [ShopController::class, 'placeOrder'])->name('checkout.store');
    Route::get('/order-confirmation/{sale}', [ShopController::class, 'confirmation'])->name('order.confirmation');

    Route::post('/payment/sslcommerz/success/{sale}', [ShopController::class, 'paymentSuccess'])->name('payment.sslcommerz.success');
    Route::post('/payment/sslcommerz/fail/{sale}', [ShopController::class, 'paymentFail'])->name('payment.sslcommerz.fail');
    Route::post('/payment/sslcommerz/cancel/{sale}', [ShopController::class, 'paymentCancel'])->name('payment.sslcommerz.cancel');

    Route::get('/track-order', [ShopController::class, 'trackForm'])->name('track-order');
    Route::post('/track-order', [ShopController::class, 'track'])->name('track-order.result');

    Route::get('/campaign/{campaignPage:slug}', [CampaignController::class, 'show'])->name('campaign.show');
    Route::post('/campaign/{campaignPage:slug}/buy', [CampaignController::class, 'buyNow'])->name('campaign.buy');
});

// UI language switch — not an authenticated action, just a session preference.
Route::post('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Backend/admin panel — kept under /admin so the public company website
// (and its optional e-commerce storefront, toggled from Settings) can
// live at the root without colliding with these routes.
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['auth', 'verified', 'current-site'])->name('dashboard');

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

        Route::get('/purchases/manual', [PurchaseController::class, 'manual'])->name('purchases.manual');
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
        Route::get('/purchases/{purchase}/print', [PurchaseController::class, 'printOrder'])->name('purchases.print');
        Route::get('/purchases/{purchase}/receive', [PurchaseController::class, 'receiveForm'])->name('purchases.receive.create');
        Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive.store');
        Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
        Route::get('/purchase-receipts/{receipt}/print', [PurchaseController::class, 'printReceipt'])->name('purchases.receipts.print');
        Route::get('/purchases/{purchase}/return', [PurchaseController::class, 'returnForm'])->name('purchases.return.create');
        Route::post('/purchases/{purchase}/return', [PurchaseController::class, 'storeReturn'])->name('purchases.return.store');
        Route::get('/purchase-returns/{purchaseReturn}/print', [PurchaseController::class, 'printReturn'])->name('purchases.returns.print');

        Route::resource('purchase-requisitions', PurchaseRequisitionController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update'])
            ->parameters(['purchase-requisitions' => 'purchaseRequisition']);
        Route::get('/purchase-requisitions/{purchaseRequisition}/print', [PurchaseRequisitionController::class, 'print'])->name('purchase-requisitions.print');
        Route::post('/purchase-requisitions/{purchaseRequisition}/approve', [PurchaseRequisitionController::class, 'approve'])->name('purchase-requisitions.approve');
        Route::post('/purchase-requisitions/{purchaseRequisition}/reject', [PurchaseRequisitionController::class, 'reject'])->name('purchase-requisitions.reject');
        Route::post('/purchase-requisitions/{purchaseRequisition}/cancel', [PurchaseRequisitionController::class, 'cancel'])->name('purchase-requisitions.cancel');

        Route::get('/sales/manual', [SaleController::class, 'manual'])->name('sales.manual');
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
        Route::get('/sales/{sale}/print', [SaleController::class, 'printOrder'])->name('sales.print');
        Route::get('/sales/{sale}/deliver', [SaleController::class, 'deliverForm'])->name('sales.deliver.create');
        Route::post('/sales/{sale}/deliver', [SaleController::class, 'deliver'])->name('sales.deliver.store');
        Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
        Route::get('/sale-deliveries/{delivery}/print', [SaleController::class, 'printDelivery'])->name('sales.deliveries.print');
        Route::get('/sales/{sale}/return', [SaleController::class, 'returnForm'])->name('sales.return.create');
        Route::post('/sales/{sale}/return', [SaleController::class, 'storeReturn'])->name('sales.return.store');
        Route::get('/sale-returns/{saleReturn}/print', [SaleController::class, 'printReturn'])->name('sales.returns.print');

        Route::resource('sale-quotations', SaleQuotationController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update'])
            ->parameters(['sale-quotations' => 'saleQuotation']);
        Route::get('/sale-quotations/{saleQuotation}/print', [SaleQuotationController::class, 'print'])->name('sale-quotations.print');
        Route::post('/sale-quotations/{saleQuotation}/approve', [SaleQuotationController::class, 'approve'])->name('sale-quotations.approve');
        Route::post('/sale-quotations/{saleQuotation}/reject', [SaleQuotationController::class, 'reject'])->name('sale-quotations.reject');
        Route::post('/sale-quotations/{saleQuotation}/cancel', [SaleQuotationController::class, 'cancel'])->name('sale-quotations.cancel');

        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('/pos/products', [PosController::class, 'products'])->name('pos.products');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::post('/pos/customers', [PosController::class, 'storeCustomer'])->name('pos.customers.store');
        Route::get('/pos/{sale}/receipt', [PosController::class, 'receipt'])->name('pos.receipt');

        Route::resource('delivery-partners', DeliveryPartnerController::class)->except('show')
            ->parameters(['delivery-partners' => 'deliveryPartner']);
        Route::patch('/delivery-partners/{deliveryPartner}/toggle-status', [DeliveryPartnerController::class, 'toggleStatus'])->name('delivery-partners.toggle-status');
        Route::get('/courier-consignments', [CourierConsignmentController::class, 'index'])->name('courier-consignments.index');
        Route::get('/courier-consignments/{courierConsignment}', [CourierConsignmentController::class, 'show'])->name('courier-consignments.show');
        Route::patch('/courier-consignments/{courierConsignment}/status', [CourierConsignmentController::class, 'updateStatus'])->name('courier-consignments.status');
        Route::post('/courier-consignments/{courierConsignment}/settle-cod', [CourierConsignmentController::class, 'settleCod'])->name('courier-consignments.settle-cod');

        Route::resource('parties', PartyController::class)->except('show');
        Route::patch('/parties/{party}/toggle-status', [PartyController::class, 'toggleStatus'])->name('parties.toggle-status');
        Route::get('/parties/{party}/ledger', [PartyController::class, 'ledger'])->name('parties.ledger');

        // Accounting foundation — see docs/accounting-foundation.md. Only
        // Bank Accounts has a CRUD screen in Phase 1; every other
        // ledger_accounts group is system-seeded.
        Route::resource('bank-accounts', LedgerAccountController::class)->except('show')
            ->parameters(['bank-accounts' => 'bankAccount']);
        Route::patch('/bank-accounts/{bankAccount}/toggle-status', [LedgerAccountController::class, 'toggleStatus'])->name('bank-accounts.toggle-status');
        Route::get('/bank-accounts/{bankAccount}/ledger', [LedgerAccountController::class, 'ledger'])->name('bank-accounts.ledger');
        Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/payments/{payment}/print', [PaymentController::class, 'print'])->name('payments.print');
        Route::resource('collections', CollectionController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/collections/{collection}/print', [CollectionController::class, 'print'])->name('collections.print');
        Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/expenses/{expense}/print', [ExpenseController::class, 'print'])->name('expenses.print');
        Route::resource('incomes', IncomeController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/incomes/{income}/print', [IncomeController::class, 'print'])->name('incomes.print');
        Route::resource('fund-transfers', FundTransferController::class)->only(['index', 'create', 'store', 'show'])
            ->parameters(['fund-transfers' => 'fundTransfer']);
        Route::get('/fund-transfers/{fundTransfer}/print', [FundTransferController::class, 'print'])->name('fund-transfers.print');
        Route::resource('capital-transactions', CapitalTransactionController::class)->only(['index', 'create', 'store', 'show'])
            ->parameters(['capital-transactions' => 'capitalTransaction']);
        Route::get('/capital-transactions/{capitalTransaction}/print', [CapitalTransactionController::class, 'print'])->name('capital-transactions.print');
        Route::get('/day-book', [DayBookController::class, 'index'])->name('day-book.index');
        Route::get('/trial-balance', [TrialBalanceController::class, 'index'])->name('trial-balance.index');
        Route::get('/balance-sheet', [BalanceSheetController::class, 'index'])->name('balance-sheet.index');
        Route::get('/profit-loss', [ProfitLossController::class, 'index'])->name('profit-loss.index');
        Route::get('/due-report', [DueReportController::class, 'index'])->name('due-report.index');

        Route::get('/contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('/contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::delete('/contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

        Route::get('/product-reviews', [ProductReviewController::class, 'index'])->name('product-reviews.index');
        Route::get('/product-reviews/{productReview}', [ProductReviewController::class, 'show'])->name('product-reviews.show');
        Route::post('/product-reviews/{productReview}/approve', [ProductReviewController::class, 'approve'])->name('product-reviews.approve');
        Route::post('/product-reviews/{productReview}/reject', [ProductReviewController::class, 'reject'])->name('product-reviews.reject');
        Route::delete('/product-reviews/{productReview}', [ProductReviewController::class, 'destroy'])->name('product-reviews.destroy');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/settings/website', [SettingController::class, 'editWebsite'])->name('settings.website.edit');
        Route::put('/settings/website', [SettingController::class, 'updateWebsite'])->name('settings.website.update');
        Route::get('/settings/ecommerce', [SettingController::class, 'editEcommerce'])->name('settings.ecommerce.edit');
        Route::put('/settings/ecommerce', [SettingController::class, 'updateEcommerce'])->name('settings.ecommerce.update');
        Route::get('/settings/approvals', [SettingController::class, 'editApprovals'])->name('settings.approvals.edit');
        Route::put('/settings/approvals', [SettingController::class, 'updateApprovals'])->name('settings.approvals.update');

        Route::resource('hero-slides', HeroSlideController::class)->except('show')
            ->parameters(['hero-slides' => 'heroSlide']);
        Route::patch('/hero-slides/{heroSlide}/toggle-status', [HeroSlideController::class, 'toggleStatus'])->name('hero-slides.toggle-status');

        Route::resource('campaign-pages', CampaignPageController::class)->except('show')
            ->parameters(['campaign-pages' => 'campaignPage']);
        Route::patch('/campaign-pages/{campaignPage}/toggle-status', [CampaignPageController::class, 'toggleStatus'])->name('campaign-pages.toggle-status');

        Route::resource('delivery-zones', DeliveryZoneController::class)->except('show')
            ->parameters(['delivery-zones' => 'deliveryZone']);
        Route::patch('/delivery-zones/{deliveryZone}/toggle-status', [DeliveryZoneController::class, 'toggleStatus'])->name('delivery-zones.toggle-status');
    });
});

require __DIR__.'/auth.php';
