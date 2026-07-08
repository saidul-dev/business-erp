<?php

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CurrentSiteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

// Public company website — the e-commerce storefront link only appears
// once "Enable E-commerce" is switched on from Admin > Settings.
Route::get('/', [WebsiteController::class, 'home'])->name('home');
Route::get('/shop', [WebsiteController::class, 'shop'])->name('shop');

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

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';
