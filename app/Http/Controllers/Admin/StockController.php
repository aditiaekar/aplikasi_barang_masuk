<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\WarehouseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    protected $warehouseRepo;

    public function __construct(WarehouseRepository $warehouseRepo)
    {
        $this->warehouseRepo = $warehouseRepo;
    }

    public function data(Request $request)
    {
        $query = DB::table('item_stocks')
            ->join('items', 'item_stocks.item_id', '=', 'items.id')
            ->join('warehouses', 'item_stocks.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('units', 'items.unit_id', '=', 'units.id')
            ->select([
                'item_stocks.id',
                'item_stocks.item_id',
                'item_stocks.warehouse_id',
                'items.item_code as item_code',
                'items.name as item_name',
                'warehouses.name as warehouse_name',
                'units.name as unit_name',
                'items.minimum_stock',
                DB::raw('SUM(item_stocks.quantity) as total_stock')
            ])
            ->groupBy(
                'item_stocks.item_id',
                'item_stocks.warehouse_id',
                'items.item_code',
                'items.name',
                'items.minimum_stock',
                'warehouses.name',
                'units.name'
            );

        if ($request->filled('warehouse')) {
            $query->where('item_stocks.warehouse_id', $request->warehouse);
        }

        return DataTables::query($query)
            ->addIndexColumn()
            ->addColumn('status', function ($row) {
                if ($row->total_stock <= 0) {
                    return '<span class="badge bg-danger">Habis</span>';
                }
                if ($row->total_stock <= $row->minimum_stock) {
                    return '<span class="badge bg-danger">Stok Rendah</span>';
                }
                return '<span class="badge bg-success">Aman</span>';
            })
            ->rawColumns(['status'])
            ->make();


        // $stocks = DB::table('item_stocks')
        //     ->join('items', 'item_stocks.item_id', '=', 'items.id')
        //     ->join('warehouses', 'item_stocks.warehouse_id', '=', 'warehouses.id')
        //     ->leftJoin('units', 'items.unit_id', '=', 'units.id')
        //     ->when($warehouseId, function ($query) use ($warehouseId) {
        //         $query->where('item_stocks.warehouse_id', $warehouseId);
        //     })
        //     ->select([
        //         'item_stocks.id',
        //         'item_stocks.item_id',
        //         'item_stocks.warehouse_id',
        //         'item_stocks.quantity as stock_quantity',
        //         'items.item_code as item_code',
        //         'items.name as item_name',
        //         'warehouses.name as warehouse_name',
        //         'units.name as unit_name',
        //     ]);

        //     $stocks->addSelect('items.minimum_stock');
    }

    public function index(Request $request)
    {
        $warehouses = $this->warehouseRepo->getAllActive();

        return view('admin.stocks.index', compact(
            'warehouses'
        ));
    }

    public function mutation_data(Request $request)
    {
        $query = DB::table('stock_mutations')
            ->join('items', 'stock_mutations.item_id', '=', 'items.id')
            ->join('warehouses', 'stock_mutations.warehouse_id', '=', 'warehouses.id')
            ->leftJoin('users', 'stock_mutations.created_by', '=', 'users.id')
            ->leftJoin('stock_ins', 'stock_mutations.stock_in_id', '=', 'stock_ins.id')
            ->leftJoin('stock_outs', 'stock_mutations.stock_out_id', '=', 'stock_outs.id')
            ->select([
                'stock_mutations.id',
                'stock_mutations.mutation_date',
                'stock_mutations.mutation_type',
                'stock_mutations.quantity',
                'stock_mutations.stock_before',
                'stock_mutations.stock_after',
                'stock_mutations.description',
                'stock_mutations.warehouse_id',

                'items.item_code',
                'items.name as item_name',
                'warehouses.name as warehouse_name',
                'users.name as user_name',

                'stock_ins.stock_in_number',
                'stock_outs.stock_out_number',
            ]);
        if ($request->filled('warehouse')) {
            $query->where(
                'stock_mutations.warehouse_id',
                $request->warehouse
            );
        }
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($sub) use ($keyword) {
                $sub->where('items.name', 'like', "%{$keyword}%")
                    ->orWhere('items.item_code', 'like', "%{$keyword}%");
            });
        }
        $query->orderByDesc('stock_mutations.mutation_date')
            ->orderByDesc('stock_mutations.id');

        return DataTables::query($query)
            ->addIndexColumn()

            ->editColumn('mutation_date', function ($row) {
                return \Carbon\Carbon::parse($row->mutation_date)
                    ->format('d-m-Y H:i');
            })

            ->addColumn('mutation_status', function ($row) {
                if ($row->mutation_type === 'in') {
                    return '<span class="badge bg-success">Masuk</span>';
                }

                if ($row->mutation_type === 'out') {
                    return '<span class="badge bg-danger">Keluar</span>';
                }

                return '<span class="badge bg-secondary">-</span>';
            })

            ->addColumn('reference_number', function ($row) {
                if ($row->mutation_type === 'in') {
                    return $row->stock_in_number ?? '-';
                }

                if ($row->mutation_type === 'out') {
                    return $row->stock_out_number ?? '-';
                }

                return '-';
            })

            ->rawColumns(['mutation_status'])
            ->make(true);
    }

    public function mutations(Request $request)
    {
        $warehouses = $this->warehouseRepo->getAllActive();

        return view('admin.stocks.mutations', compact(
            'warehouses',
        ));
    }
}
