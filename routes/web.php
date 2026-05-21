<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\StockInRequestController;
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

Route::middleware('role:super_admin,admin_gudang')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('items', ItemController::class);

        Route::resource('stock-in-requests', StockInRequestController::class);

        Route::post('/stock-in-requests/{stockInRequest}/approve', [StockInRequestController::class, 'approve'])
            ->name('stock-in-requests.approve');

        Route::post('/stock-in-requests/{stockInRequest}/reject', [StockInRequestController::class, 'reject'])
            ->name('stock-in-requests.reject');

        Route::prefix('master-data')
            ->name('master-data.')
            ->group(function () {
                Route::get('/{type}', [MasterDataController::class, 'index'])->name('index');
                Route::get('/{type}/create', [MasterDataController::class, 'create'])->name('create');
                Route::post('/{type}', [MasterDataController::class, 'store'])->name('store');
                Route::get('/{type}/{id}/edit', [MasterDataController::class, 'edit'])->name('edit');
                Route::put('/{type}/{id}', [MasterDataController::class, 'update'])->name('update');
                Route::delete('/{type}/{id}', [MasterDataController::class, 'destroy'])->name('destroy');
            });
    });
