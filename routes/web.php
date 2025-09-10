<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ModifierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HighlightController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\SelectOptionController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::get('locale/{locale}', function ($locale) {
    App::setLocale($locale);
    Session::put("locale", $locale);

    return redirect()->back();
});

Route::get('/', function () {
    return Redirect::route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/getMembers', [SelectOptionController::class, 'getMembers'])->name('getMembers');

    /**
     * ==============================
     *           Dashboard
     * ==============================
     */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('get_member_analysis_by_year', [DashboardController::class, 'get_member_analysis_by_year'])->name('dashboard.get_member_analysis_by_year');

    /**
     * ==============================
     *            Point
     * ==============================
     */
    Route::prefix('point')->group(function () {
        Route::get('/history', [PointController::class, 'index'])->name('point.index');
        Route::get('/getPointHistoryData', [PointController::class, 'getPointHistoryData'])->name('point.getPointHistoryData');
        Route::post('/manage_point', [PointController::class, 'manage_point'])->name('point.manage_point');
    });

    /**
     * ==============================
     *           Category
     * ==============================
     */
    Route::prefix('category')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('category.index');
        Route::get('/create', [CategoryController::class, 'create'])->name('category.create');
        Route::post('/create', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/fetch-category', [CategoryController::class, 'fetchCategory'])->name('category.fetch');
        Route::post('/update-status', [CategoryController::class, 'updateStatus'])->name('category.updateStatus');
        Route::get('/edit/{id}', [CategoryController::class, 'edit'])->name('category.edit');
        Route::post('/edit/{id}', [CategoryController::class, 'update'])->name('category.update');
        Route::delete('/delete', [CategoryController::class, 'destroy'])->name('category.destroy');
    });

    /**
     * ==============================
     *           Product
     * ==============================
     */
    Route::prefix('product')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('product.index');
        Route::get('/create', [ProductController::class, 'create'])->name('product.create');
        Route::post('/create', [ProductController::class, 'store'])->name('product.store');
        Route::get('/fetch-product', [ProductController::class, 'fetchProduct'])->name('product.fetch');
        Route::post('/update-status', [ProductController::class, 'updateStatus'])->name('product.updateStatus');
         Route::get('/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
         Route::post('/edit/{id}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('/delete', [ProductController::class, 'destroy'])->name('product.destroy');
    });

    /**
     * ==============================
     *           Modifier Group
     * ==============================
     */
    Route::prefix('modifier-group')->group(function () {
        Route::get('/', [ModifierController::class, 'index'])->name('modifier.group.index');
        Route::get('/create', [ModifierController::class, 'create'])->name('modifier.group.create');
        Route::post('/create', [ModifierController::class, 'storeGroup'])->name('modifier.group.store');
        Route::get('/fetch-modifier-group', [ModifierController::class, 'fetchModifierGroup'])->name('modifier.group.fetch');
        Route::get('/fetch-category-product', [ModifierController::class, 'fetchCategoryProduct'])->name('modifier.categoryProduct.fetch');
        Route::post('/update-status', [ModifierController::class, 'updateGroupStatus'])->name('modifier.group.updateStatus');
        Route::get('/edit/{id}', [ModifierController::class, 'editGroup'])->name('modifier.group.edit');
        Route::post('/edit/{id}', [ModifierController::class, 'updateGroup'])->name('modifier.group.update');
        Route::delete('/delete', [ModifierController::class, 'destroyGroup'])->name('modifier.group.destroy');
    });

    /**
     * ==============================
     *           Modifier Item
     * ==============================
     */
    Route::prefix('modifier-item')->group(function () {
        Route::get('/', [ModifierController::class, 'indexItem'])->name('modifier.item.index');
        Route::post('/create', [ModifierController::class, 'storeItem'])->name('modifier.item.store');
        Route::get('/fetch-modifier-item', [ModifierController::class, 'fetchModifierItem'])->name('modifier.item.fetch');
        Route::post('/edit', [ModifierController::class, 'updateItem'])->name('modifier.item.update');
        Route::delete('/delete', [ModifierController::class, 'destroyItem'])->name('modifier.item.destroy');
    });

    /**
     * ==============================
     *           Member
     * ==============================
     */
    Route::prefix('member')->group(function () {
        Route::get('/', [MemberController::class, 'index'])->name('member.index');
        Route::get('/fetch-member', [MemberController::class, 'fetchMember'])->name('member.fetch');
        Route::post('/adjust-point', [MemberController::class, 'adjustPoint'])->name('member.adjustPoint');
        Route::delete('/delete', [MemberController::class, 'destroy'])->name('member.destroy');
        Route::post('/reset-password', [MemberController::class, 'resetPassword'])->name('member.resetPassword');
    });

    /**
     * ==============================
     *         Highlight
     * ==============================
     */
    Route::prefix('highlight')->group(function () {
        Route::get('/', [HighlightController::class, 'index'])->name('highlight.index');
        Route::get('/create', [HighlightController::class, 'create'])->name('highlight.create');
        Route::post('/create', [HighlightController::class, 'store'])->name('highlight.store');
        Route::get('/fetch-highlight', [HighlightController::class, 'fetchHighlight'])->name('highlight.fetch');
        Route::post('/reorder', [HighlightController::class, 'reorder'])->name('highlight.reorder');
        Route::post('/update-status', [HighlightController::class, 'updateStatus'])->name('highlight.updateStatus');
        Route::post('/update-popup', [HighlightController::class, 'updatePopup'])->name('highlight.updatePopup');
        Route::get('/edit/{id}', [HighlightController::class, 'edit'])->name('highlight.edit');
        Route::post('/edit/{id}', [HighlightController::class, 'update'])->name('highlight.update');
        Route::delete('/delete', [HighlightController::class, 'destroy'])->name('highlight.destroy');
    });

    /**
     * ==============================
     *         Notification
     * ==============================
     */
    Route::prefix('push-notification')->group(function () {
        Route::get('/', [PushNotificationController::class, 'index'])->name('notification.index');
        Route::get('/create', [PushNotificationController::class, 'create'])->name('notification.create');
        Route::post('/create', [PushNotificationController::class, 'store'])->name('notification.store');
        Route::get('/fetch-notification', [PushNotificationController::class, 'fetchNotification'])->name('notification.fetch');
        Route::post('/update-status', [PushNotificationController::class, 'updateStatus'])->name('notification.updateStatus');
        Route::get('/edit/{id}', [PushNotificationController::class, 'edit'])->name('notification.edit');
        Route::post('/edit/{id}', [PushNotificationController::class, 'update'])->name('notification.update');
        Route::post('push_notification/{id}', [PushNotificationController::class, 'push_notification'])->name('notification.push_notification');
        Route::delete('/delete', [PushNotificationController::class, 'destroy'])->name('notification.destroy');
    });

    /**
     * ==============================
     *           Voucher
     * ==============================
     */
    Route::prefix('voucher')->group(function () {
        Route::get('/listing', [VoucherController::class, 'index'])->name('voucher.index');
        Route::get('/getVoucherListingData', [VoucherController::class, 'getVoucherListingData'])->name('voucher.getVoucherListingData');
        Route::get('/create', [VoucherController::class, 'create'])->name('voucher.create');

        Route::post('/create/validate/{step}', [VoucherController::class, 'validate_step'])->name('voucher.validate');
        Route::post('/create/store', [VoucherController::class, 'store'])->name('voucher.store');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
