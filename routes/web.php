<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Route khusus Super Admin
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:super_admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/users', function () {
                return 'Halaman kelola pengguna hanya untuk Super Admin.';
            })->name('users.index');
        });

    /*
    |--------------------------------------------------------------------------
    | Route untuk Super Admin dan Admin Gudang
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:super_admin,admin_gudang')
        ->prefix('warehouse')
        ->name('warehouse.')
        ->group(function () {
            Route::get('/stock-in-requests', function () {
                return 'Halaman pengajuan barang masuk untuk Super Admin dan Admin Gudang.';
            })->name('stock-in-requests.index');
        });
});

Route::middleware(['auth', 'role:super_admin,admin_gudang'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
