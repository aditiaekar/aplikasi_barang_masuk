<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $stockInDateColumn = Schema::hasColumn('stock_ins', 'stock_in_date')
            ? 'stock_in_date'
            : 'created_at';

        $mutationDateColumn = Schema::hasColumn('stock_mutations', 'mutation_date')
            ? 'mutation_date'
            : 'created_at';

        $stats = [
            'total_barang' => Schema::hasTable('items')
                ? DB::table('items')->count()
                : 0,

            'barang_masuk_hari_ini' => Schema::hasTable('stock_ins')
                ? DB::table('stock_ins')->whereDate($stockInDateColumn, $today)->count()
                : 0,

            'total_supplier' => Schema::hasTable('suppliers')
                ? DB::table('suppliers')->count()
                : 0,

            'laporan_bulan_ini' => Schema::hasTable('stock_mutations')
                ? DB::table('stock_mutations')
                ->whereBetween($mutationDateColumn, [$startOfMonth, $endOfMonth])
                ->count()
                : 0,

            'transaksi_pending' => Schema::hasTable('stock_in_requests')
                ? DB::table('stock_in_requests')->where('status', 'pending')->count()
                : 0,

            'transaksi_approved' => Schema::hasTable('stock_in_requests')
                ? DB::table('stock_in_requests')->where('status', 'approved')->count()
                : 0,

            'transaksi_rejected' => Schema::hasTable('stock_in_requests')
                ? DB::table('stock_in_requests')->where('status', 'rejected')->count()
                : 0,

            'stok_rendah' => Schema::hasTable('item_stocks') && Schema::hasTable('items') && Schema::hasColumn('items', 'minimum_stock')
                ? DB::table('item_stocks')
                ->join('items', 'item_stocks.item_id', '=', 'items.id')
                ->whereColumn('item_stocks.quantity', '<=', 'items.minimum_stock')
                ->count()
                : 0,
        ];

        $latestMutations = collect();

        if (Schema::hasTable('stock_mutations')) {
            $latestMutations = DB::table('stock_mutations')
                ->join('items', 'stock_mutations.item_id', '=', 'items.id')
                ->join('warehouses', 'stock_mutations.warehouse_id', '=', 'warehouses.id')
                ->leftJoin('users', 'stock_mutations.created_by', '=', 'users.id')
                ->select([
                    'stock_mutations.id',
                    'stock_mutations.mutation_type',
                    'stock_mutations.quantity',
                    'stock_mutations.description',
                    'stock_mutations.mutation_date',
                    'stock_mutations.created_at',
                    'items.item_code',
                    'items.name as item_name',
                    'warehouses.name as warehouse_name',
                    'users.name as user_name',
                ])
                ->orderByDesc($mutationDateColumn)
                ->orderByDesc('stock_mutations.id')
                ->limit(5)
                ->get();
        }

        return view('admin.dashboard', compact('stats', 'latestMutations'));
    }
}
