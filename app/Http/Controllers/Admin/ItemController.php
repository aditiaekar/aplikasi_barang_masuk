<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Repositories\CategoryRepository;
use App\Repositories\UnitRepository;
use App\Repositories\WarehouseRepository;
use App\Service\ItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ItemController extends Controller
{
    protected $itemService;
    protected $categoryRepo;
    protected $unitRepo;
    protected $warehouseRepo;

    public function __construct(
        ItemService $itemService,
        CategoryRepository $categoryRepo,
        UnitRepository $unitRepo,
        WarehouseRepository $warehouseRepo
    ) {
        $this->itemService = $itemService;
        $this->categoryRepo = $categoryRepo;
        $this->unitRepo = $unitRepo;
        $this->warehouseRepo = $warehouseRepo;
    }

    public function data(Request $request)
    {
        $stockColumn = $this->stockColumn();
        $query = Item::query()->with(['category', 'unit', 'stocks.warehouse']);
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $priceKeyword = preg_replace('/[^0-9]/', '', $keyword); // hanya ambil angka

            $query->where(function ($q) use ($keyword, $priceKeyword) {
                $q->where('item_code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('barcode', 'like', "%{$keyword}%")
                    ->orWhere('minimum_stock', 'like', "%{$keyword}%")

                    // ->orWhereHas('category', function ($category) use ($keyword) {
                    //     $category->where('name','like',"%{$keyword}%");
                    // })

                    // ->orWhereHas('unit',function ($unit) use ($keyword) {
                    //     $unit->where('name','like',"%{$keyword}%");
                    // })

                    ->orWhereHas('stocks.warehouse', function ($warehouse) use ($keyword) {
                        $warehouse->where('name', 'like', "%{$keyword}%");
                    });

                if ($priceKeyword !== '') {
                    $q->orWhereRaw('CAST(price as CHAR) LIKE ?', ["%{$keyword}%"]);
                }
            });
        }

        if ($request->filled('unit')) {
            $query->where('unit_id', $request->unit);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('image', function ($row) {
                $el = '-';
                if ($row->image) {
                    $imagePath = asset("storage/{$row->image}");
                    $el = '<img src="' . $imagePath . '" class="item-photo" alt="Gambar Barang">';
                }
                return $el;
            })
            ->addColumn('status', function ($row) {
                $el = '<span class="badge-inactive">Nonaktif</span>';
                if ($row->is_active) {
                    $el = '<span class="badge-active">Aktif</span>';
                }
                return $el;
            })
            ->addColumn('total_stock', function ($row) use ($stockColumn) {
                return $stockColumn ? $row->stocks->sum($stockColumn) : 0;
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('admin.items.edit', $row->id);
                $deleteUrl = route('admin.items.destroy', $row->id);
                $csrf = csrf_field();
                $methodDelete = method_field('DELETE');

                $action = '
                <a href="' . $editUrl . '"
                    class="action-btn" title="Edit">
                    <i class="bx bx-edit"></i>
                </a>
                ';

                $action .= '
                <form action="' . $deleteUrl . '"
                            method="POST"
                            class="d-inline"
                            onsubmit="return confirm(\'Yakin ingin menghapus data barang ini?\')">
                            ' . $csrf . '
                            ' . $methodDelete . '
                            <button type="submit" class="action-btn" title="Hapus">
                                <i class="bx bx-trash"></i>
                            </button>
                        </form>
                ';

                return '<div class="d-flex align-items-center gap-1">' . $action . '</div>';
            })

            ->rawColumns(['image', 'status', 'action'])

            ->make(true);
    }

    private function itemColumns(): array
    {
        return Schema::getColumnListing('items');
    }

    private function stockColumn(): ?string
    {
        foreach (['stock', 'quantity', 'qty', 'current_stock'] as $column) {
            if (Schema::hasColumn('item_stocks', $column)) {
                return $column;
            }
        }

        return null;
    }

    private function activeQuery($model)
    {
        $query = $model::query();

        if (Schema::hasColumn((new $model)->getTable(), 'is_active')) {
            $query->where('is_active', true);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $columns = $this->itemColumns();
        $stockColumn = $this->stockColumn();

        // $items = Item::query()
        //     ->with(['category', 'unit', 'stocks.warehouse'])
        //     ->when($request->search, function ($query, $search) {
        //         $query->where(function ($query) use ($search) {
        //             if ($this->hasItemColumn('item_code')) {
        //                 $query->orWhere('item_code', 'like', "%{$search}%");
        //             }

        //             if ($this->hasItemColumn('barcode')) {
        //                 $query->orWhere('barcode', 'like', "%{$search}%");
        //             }

        //             if ($this->hasItemColumn('name')) {
        //                 $query->orWhere('name', 'like', "%{$search}%");
        //             }
        //         });
        //     })
        //     ->when($request->category_id && $this->hasItemColumn('category_id'), function ($query) use ($request) {
        //         $query->where('category_id', $request->category_id);
        //     })
        //     ->when($request->unit_id && $this->hasItemColumn('unit_id'), function ($query) use ($request) {
        //         $query->where('unit_id', $request->unit_id);
        //     })
        //     ->when($request->status !== null && $request->status !== '' && $this->hasItemColumn('is_active'), function ($query) use ($request) {
        //         $query->where('is_active', $request->status);
        //     })
        //     ->latest()
        //     ->paginate(10)
        //     ->withQueryString();

        $categories = $this->activeQuery(Category::class)->orderBy('name')->get();
        $units = $this->activeQuery(Unit::class)->orderBy('name')->get();

        return view('admin.items.new-index', compact(
            'categories',
            'units',
            'stockColumn'
        ));
    }

    public function create()
    {
        $categories = $this->categoryRepo->getAllActive();
        $units = $this->unitRepo->getAllActive();
        $warehouses = $this->warehouseRepo->getAllActive();

        return view('admin.items.create', compact(
            'categories',
            'units',
            'warehouses'
        ));
    }

    public function store(Request $request)
    {
        $item = $this->itemService->store($request);

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Data barang berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $categories = $this->categoryRepo->getAllActive();
        $units = $this->unitRepo->getAllActive();
        $warehouses = $this->warehouseRepo->getAllActive();

        $stocks = [];

        $stocks = $item->stocks()
            ->pluck('quantity', 'warehouse_id')
            ->toArray();

        return view('admin.items.edit', compact(
            'item',
            'categories',
            'units',
            'warehouses',
            'stocks'
        ));
    }

    public function update(Request $request, Item $item)
    {
        $item = $this->itemService->update($item,$request);

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        $item = $this->itemService->destroy($item);

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Data barang berhasil dihapus.');
    }

    private function itemPayload(array $validated): array
    {
        $payload = [];

        foreach ($this->itemColumns() as $column) {
            if (array_key_exists($column, $validated) && !in_array($column, ['id', 'created_at', 'updated_at', 'image'])) {
                $payload[$column] = $validated[$column];
            }
        }

        return $payload;
    }
}
