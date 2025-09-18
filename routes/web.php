<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\ShortlinkController;
use Illuminate\Support\Facades\Route;

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

// ========================================
// DASHBOARD ROUTES
// ========================================
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/stats', [DashboardController::class, 'stats'])->name('stats');
});

// Root redirect to dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ========================================
// DOMAIN MANAGEMENT ROUTES
// ========================================
Route::prefix('domains')->name('domains.')->group(function () {
    Route::get('/', [DomainController::class, 'index'])->name('index');
    Route::get('/create', [DomainController::class, 'create'])->name('create');
    Route::post('/', [DomainController::class, 'store'])->name('store');
    Route::get('/{domain}', [DomainController::class, 'show'])->name('show');
    Route::get('/{domain}/edit', [DomainController::class, 'edit'])->name('edit');
    Route::put('/{domain}', [DomainController::class, 'update'])->name('update');
    Route::delete('/{domain}', [DomainController::class, 'destroy'])->name('destroy');
    
    // Domain specific actions
    Route::post('/{domain}/toggle-status', [DomainController::class, 'toggleStatus'])->name('toggle-status');
});

// ========================================
// SHORTLINK MANAGEMENT ROUTES
// ========================================
Route::prefix('shortlinks')->name('shortlinks.')->group(function () {
    Route::get('/', [ShortlinkController::class, 'index'])->name('index');
    Route::get('/create', [ShortlinkController::class, 'create'])->name('create');
    Route::post('/', [ShortlinkController::class, 'store'])->name('store');
    Route::get('/{shortlink}', [ShortlinkController::class, 'show'])->name('show');
    Route::get('/{shortlink}/edit', [ShortlinkController::class, 'edit'])->name('edit');
    Route::put('/{shortlink}', [ShortlinkController::class, 'update'])->name('update');
    Route::delete('/{shortlink}', [ShortlinkController::class, 'destroy'])->name('destroy');
    
    // Shortlink specific actions
    Route::post('/{shortlink}/toggle-status', [ShortlinkController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{shortlink}/reset-stats', [ShortlinkController::class, 'resetStats'])->name('reset-stats');
    Route::get('/{shortlink}/analytics', [ShortlinkController::class, 'analytics'])->name('analytics');
    Route::get('/{shortlink}/analytics-data', [ShortlinkController::class, 'analyticsData'])->name('analytics-data');
    
    // Bulk actions
    Route::post('/bulk-action', [ShortlinkController::class, 'bulkAction'])->name('bulk-action');
});

// ========================================
// ANALYTICS ROUTES
// ========================================
Route::prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/', [AnalyticsController::class, 'index'])->name('index');
    Route::get('/shortlinks/{shortlink}', [AnalyticsController::class, 'shortlink'])->name('shortlink');
    Route::get('/realtime', [AnalyticsController::class, 'realtime'])->name('realtime');
    Route::get('/comparison', [AnalyticsController::class, 'comparison'])->name('comparison');
    Route::post('/export', [AnalyticsController::class, 'export'])->name('export');
});

// ========================================
// API ROUTES (for AJAX calls)
// ========================================
Route::prefix('api')->name('api.')->group(function () {
    Route::post('/validate-url', [ShortlinkController::class, 'validateUrl'])->name('validate-url');
    Route::post('/check-shortcode-availability', [ShortlinkController::class, 'checkAvailability'])->name('check-availability');
});

// ========================================
// REDIRECT ROUTES
// ========================================
// Note: These routes must be at the bottom to avoid conflicts with other routes

Route::group(['middleware' => 'throttle:60,1'], function () {
    // Password-protected shortlink routes (custom domains)
    Route::get('/{domain}/{shortCode}/password', [RedirectController::class, 'showPasswordForm'])
        ->name('redirect.password')
        ->where(['domain' => '[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}', 'shortCode' => '[a-zA-Z0-9_-]+']);

    Route::post('/{domain}/{shortCode}/password', [RedirectController::class, 'verifyPassword'])
        ->name('redirect.verify')
        ->where(['domain' => '[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}', 'shortCode' => '[a-zA-Z0-9_-]+']);

    // Direct shortcode password routes (primary domain)
    Route::get('/{shortCode}/password', [RedirectController::class, 'showPasswordFormDirect'])
        ->name('direct.redirect.password')
        ->where(['shortCode' => '[a-zA-Z0-9_-]+']);

    Route::post('/{shortCode}/password', [RedirectController::class, 'verifyPasswordDirect'])
        ->name('direct.redirect.verify')
        ->where(['shortCode' => '[a-zA-Z0-9_-]+']);

    // Preview route (with + prefix)
    Route::get('/{domain}/+{shortCode}', [RedirectController::class, 'preview'])
        ->name('redirect.preview')
        ->where(['domain' => '[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}', 'shortCode' => '[a-zA-Z0-9_-]+']);

    // Main redirect routes
    Route::get('/{shortCode}', [RedirectController::class, 'directRedirect'])
        ->name('direct.redirect')
        ->where(['shortCode' => '[a-zA-Z0-9_-]+']);

    Route::get('/{domain}/{shortCode}', [RedirectController::class, 'redirect'])
        ->name('redirect')
        ->where(['domain' => '[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}', 'shortCode' => '[a-zA-Z0-9_-]+']);
});

// ========================================
// FALLBACK ROUTE
// ========================================
Route::fallback(function () {
    return redirect()->route('dashboard');
});