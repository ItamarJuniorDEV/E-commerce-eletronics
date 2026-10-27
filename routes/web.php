<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::get('admin/roles', function () {
        return response()->json([
            'message' => 'Acesso autorizado à gestão de papéis',
        ]);
    })->middleware('can:roles.manage')->name('admin.roles.index');
});

require __DIR__.'/settings.php';
