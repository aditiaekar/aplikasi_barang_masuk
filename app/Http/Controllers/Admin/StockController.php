<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $warehouseId = $request->warehouse_id;

        $warehouses = DB::table('warehouses')
            ->orderBy('name')
            ->get();

        $hasMinimumStock = Schema::hasColumn('items', 'minimum_stock');

        $stocks = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->join('warehouses', 'item_stocks.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('units', 'items.unit_id', '=', 'units.id')
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('item_stocks.warehouse_id', $warehouseId);
            })
            ->select([
                'item_stocks.id',
                'item_stocks.item_id',
                'item_stocks.warehouse_id',
                'item_stocks.quantity as stock_quantity',
                'items.item_code as item_code',
                'items.name as item_name',
                'warehouses.name as warehouse_name',
                'units.name as unit_name',
            ]);

        if ($hasMinimumStock) {
            $stocks->addSelect('items.minimum_stock');
        }

        $stocks = $stocks
            ->orderBy('warehouses.name')
            ->orderBy('items.name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.stocks.index', compact(
            'stocks',
            'warehouses',
            'warehouseId',
            'hasMinimumStock'
        ));
    }

    public function mutations(Request $request)
    {
        $warehouseId = $request->warehouse_id;
        $itemKeyword = $request->item;

        $warehouses = DB::table('warehouses')
            ->orderBy('name')
            ->get();

        $mutations = DB::table('stock_mutations')
            ->join('items', 'stock_mutations.item_id', '=', 'items.id')
            ->join('warehouses', 'stock_mutations.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('users', 'stock_mutations.created_by', '=', 'users.id')
            ->leftJoin('stock_ins', 'stock_mutations.stock_in_id', '=', 'stock_ins.id')
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('stock_mutations.warehouse_id', $warehouseId);
            })
            ->when($itemKeyword, function ($query) use ($itemKeyword) {
                $query->where(function ($subQuery) use ($itemKeyword) {
                    $subQuery->where('items.name', 'like', '%' . $itemKeyword . '%')
                        ->orWhere('items.item_code', 'like', '%' . $itemKeyword . '%');
                });
            })
            ->select([
                'stock_mutations.*',
                'items.item_code as item_code',
                'items.name as item_name',
                'warehouses.name as warehouse_name',
                'users.name as user_name',
                'stock_ins.stock_in_number',
            ])
            ->orderByDesc('stock_mutations.mutation_date')
            ->orderByDesc('stock_mutations.id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.stocks.mutations', compact(
            'mutations',
            'warehouses',
            'warehouseId',
            'itemKeyword'
        ));
    }
}
