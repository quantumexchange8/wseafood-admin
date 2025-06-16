<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ModifierController;
use App\Http\Controllers\ProductController;
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
        // Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('category.edit');
        // Route::put('/{category}', [CategoryController::class, 'update'])->name('category.update');
        // Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('category.destroy');
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
        // Route::get('/{modifier_group}/edit', [ModifierController::class, 'edit'])->name('modifier_group.edit');
        // Route::put('/{modifier_group}', [ModifierController::class, 'update'])->name('modifier_group.update');
        // Route::delete('/{modifier_group}', [ModifierController::class, 'destroy'])->name('modifier_group.destroy');
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
