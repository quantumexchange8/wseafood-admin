<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ModifierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HighlightController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('locale/{locale}', function ($locale) {
    App::setLocale($locale);
    Session::put("locale", $locale);

    return redirect()->back();
});

Route::get('/', function () {
    return Redirect::route('login');
});

Route::middleware('auth')->group(function () {
    /**
     * ==============================
     *           Dashboard
     * ==============================
     */
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    
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
        Route::post('/delete', [CategoryController::class, 'destroy'])->name('category.destroy');
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
        // Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('product.edit');
        // Route::put('/{product}', [ProductController::class, 'update'])->name('product.update');
        // Route::delete('/{product}', [ProductController::class, 'destroy'])->name('product.destroy');
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
        // Route::get('/{modifier_group}/edit', [ModifierController::class, 'edit'])->name('modifier_group.edit');
        // Route::put('/{modifier_group}', [ModifierController::class, 'update'])->name('modifier_group.update');
        Route::post('/delete', [ModifierController::class, 'destroyGroup'])->name('modifier.group.destroy');
    });

    /**
     * ==============================
     *           Modifier Item
     * ==============================
     */
    Route::prefix('modifier-item')->group(function () {
        Route::post('/create', [ModifierController::class, 'storeItem'])->name('modifier.item.store');
        Route::get('/fetch-modifier-item', [ModifierController::class, 'fetchModifierItem'])->name('modifier.item.fetch');
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
        Route::post('/delete', [MemberController::class, 'destroy'])->name('member.destroy');
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
        Route::post('/delete', [HighlightController::class, 'destroy'])->name('highlight.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
