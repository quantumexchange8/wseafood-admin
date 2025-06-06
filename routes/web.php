<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ModifierGroupController;
use App\Http\Controllers\ProductController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
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
        Route::get('/fetch_category', [CategoryController::class, 'fetchCategory'])->name('category.fetch');
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
        Route::get('/fetch_product', [ProductController::class, 'fetchProduct'])->name('product.fetch');
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
        Route::get('/', [ModifierGroupController::class, 'index'])->name('modifier_group.index');
        Route::get('/create', [ModifierGroupController::class, 'create'])->name('modifier_group.create');
        Route::post('/create', [ModifierGroupController::class, 'store'])->name('modifier_group.store');
        Route::get('/fetch_modifier_group', [ModifierGroupController::class, 'fetchModifierGroup'])->name('modifier_group.fetch');
        // Route::get('/{modifier_group}/edit', [ModifierGroupController::class, 'edit'])->name('modifier_group.edit');
        // Route::put('/{modifier_group}', [ModifierGroupController::class, 'update'])->name('modifier_group.update');
        // Route::delete('/{modifier_group}', [ModifierGroupController::class, 'destroy'])->name('modifier_group.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
