<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\StockInRequestController;
use App\Http\Controllers\Admin\StockOutRequestController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\WarehouseController;
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

        route::get('items/data', [ItemController::class, 'data'])->name('items.data');
        Route::resource('items', ItemController::class);

        Route::get(
            'stock-in-requests/items/data',
            [StockInRequestController::class, 'itemData']
        )->name('stock-in-requests.items.data');

        Route::get(
            'stock-out-requests/items/data',
            [StockOutRequestController::class, 'itemData']
        )->name('stock-out-requests.items.data');

        route::get('stock-in-requests/data', [StockInRequestController::class, 'data'])->name('stock-in-requests.data');
        Route::resource('stock-in-requests', StockInRequestController::class);

        Route::post('/stock-in-requests/{stockInRequest}/approve', [StockInRequestController::class, 'approve'])
            ->name('stock-in-requests.approve');

        Route::post('/stock-in-requests/{stockInRequest}/reject', [StockInRequestController::class, 'reject'])
            ->name('stock-in-requests.reject');


        Route::post('/stock-out-requests/{stockOutRequest}/approve', [StockOutRequestController::class, 'approve'])
            ->name('stock-out-requests.approve');

        Route::post('/stock-out-requests/{stockOutRequest}/reject', [StockOutRequestController::class, 'reject'])
            ->name('stock-out-requests.reject');

        route::get('stock-out-requests/data', [StockOutRequestController::class, 'data'])->name('stock-out-requests.data');
        Route::get('/stock-out-requests/warehouse/{warehouse}/items', [StockOutRequestController::class, 'warehouseItems'])
            ->name('stock-out-requests.warehouse-items');
        Route::resource('stock-out-requests', StockOutRequestController::class)
            ->parameters(['stock-out-requests' => 'stockOutRequest']);

        // Stok barang
        Route::get('/stocks/data', [StockController::class, 'data'])->name('stocks.data');
        Route::get('/stocks', [StockController::class, 'index'])
            ->name('stocks.index');

        // Riwayat mutasi stok
        Route::get('/stock-mutations/data', [StockController::class, 'mutation_data'])->name('stocks.mutations.data');
        Route::get('/stock-mutations', [StockController::class, 'mutations'])
            ->name('stock-mutations.index');

        Route::get('/reports/stocks/export', [ReportController::class, 'exportStocks'])
            ->name('reports.stocks.export');

        Route::get('/reports/stock-mutations/export', [ReportController::class, 'exportStockMutations'])
            ->name('reports.stock-mutations.export');

        Route::get('/categories/data', [CategoryController::class, 'data'])->name('categories.data');
        Route::resource('/categories', CategoryController::class);
        Route::get('/units/data', [UnitController::class, 'data'])->name('units.data');
        Route::resource('/units', UnitController::class);
        Route::get('/suppliers/data', [SupplierController::class, 'data'])->name('suppliers.data');
        Route::resource('/suppliers', SupplierController::class);
        Route::get('/warehouses/data', [WarehouseController::class, 'data'])->name('warehouses.data');
        Route::resource('/warehouses', WarehouseController::class);

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
