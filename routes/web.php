<?php

use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')
        ->name('dashboard');

    Route::get('admin/roles', [RoleController::class, 'index'])
        ->middleware('can:roles.manage')
        ->name('admin.roles.index');

    Route::post('admin/roles', [RoleController::class, 'store'])
        ->middleware('can:roles.manage')
        ->name('admin.roles.store');
});

require __DIR__.'/settings.php';
