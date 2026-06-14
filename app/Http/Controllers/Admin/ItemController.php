<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    private function itemColumns(): array
    {
        return Schema::getColumnListing('items');
    }

    private function hasItemColumn(string $column): bool
    {
        return Schema::hasColumn('items', $column);
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

        $items = Item::query()
            ->with(['category', 'unit', 'stocks.warehouse'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    if ($this->hasItemColumn('item_code')) {
                        $query->orWhere('item_code', 'like', "%{$search}%");
                    }

                    if ($this->hasItemColumn('barcode')) {
                        $query->orWhere('barcode', 'like', "%{$search}%");
                    }

                    if ($this->hasItemColumn('name')) {
                        $query->orWhere('name', 'like', "%{$search}%");
                    }
                });
            })
            ->when($request->category_id && $this->hasItemColumn('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->unit_id && $this->hasItemColumn('unit_id'), function ($query) use ($request) {
                $query->where('unit_id', $request->unit_id);
            })
            ->when($request->status !== null && $request->status !== '' && $this->hasItemColumn('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = $this->activeQuery(Category::class)->orderBy('name')->get();
        $units = $this->activeQuery(Unit::class)->orderBy('name')->get();

        return view('admin.items.index', compact(
            'items',
            'categories',
            'units',
            'columns',
            'stockColumn'
        ));
    }

    public function create()
    {
        $columns = $this->itemColumns();
        $stockColumn = $this->stockColumn();

        $categories = $this->activeQuery(Category::class)->orderBy('name')->get();
        $units = $this->activeQuery(Unit::class)->orderBy('name')->get();
        $warehouses = $this->activeQuery(Warehouse::class)->orderBy('name')->get();

        return view('admin.items.create', compact(
            'columns',
            'stockColumn',
            'categories',
            'units',
            'warehouses'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $payload = $this->itemPayload($validated);

        if ($request->hasFile('image') && $this->hasItemColumn('image')) {
            $payload['image'] = $request->file('image')->store('items', 'public');
        }

        $item = new Item();
        $item->forceFill($payload);
        $item->save();

        $this->syncStocks($item, $request);

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Data barang berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $columns = $this->itemColumns();
        $stockColumn = $this->stockColumn();

        $categories = $this->activeQuery(Category::class)->orderBy('name')->get();
        $units = $this->activeQuery(Unit::class)->orderBy('name')->get();
        $warehouses = $this->activeQuery(Warehouse::class)->orderBy('name')->get();

        $stocks = [];

        if ($stockColumn) {
            $stocks = $item->stocks()
                ->pluck($stockColumn, 'warehouse_id')
                ->toArray();
        }

        return view('admin.items.edit', compact(
            'item',
            'columns',
            'stockColumn',
            'categories',
            'units',
            'warehouses',
            'stocks'
        ));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate($this->rules($item->id));

        $payload = $this->itemPayload($validated);

        if ($request->hasFile('image') && $this->hasItemColumn('image')) {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }

            $payload['image'] = $request->file('image')->store('items', 'public');
        }

        $item->forceFill($payload);
        $item->save();

        $this->syncStocks($item, $request);

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        try {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }

            $item->delete();

            return redirect()
                ->route('admin.items.index')
                ->with('success', 'Data barang berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()
                ->route('admin.items.index')
                ->with('error', 'Data barang tidak dapat dihapus karena sudah digunakan pada data lain.');
        }
    }

    private function rules(?int $ignoreId = null): array
    {
        $rules = [];

        if ($this->hasItemColumn('category_id')) {
            $rules['category_id'] = ['nullable', 'exists:categories,id'];
        }

        if ($this->hasItemColumn('unit_id')) {
            $rules['unit_id'] = ['nullable', 'exists:units,id'];
        }

        if ($this->hasItemColumn('item_code')) {
            $uniqueCode = Rule::unique('items', 'item_code');

            if ($ignoreId) {
                $uniqueCode->ignore($ignoreId);
            }

            $rules['item_code'] = ['required', 'string', 'max:100', $uniqueCode];
        }

        if ($this->hasItemColumn('barcode')) {
            $uniqueBarcode = Rule::unique('items', 'barcode');

            if ($ignoreId) {
                $uniqueBarcode->ignore($ignoreId);
            }

            $rules['barcode'] = ['nullable', 'string', 'max:100', $uniqueBarcode];
        }

        if ($this->hasItemColumn('name')) {
            $rules['name'] = ['required', 'string', 'max:255'];
        }

        if ($this->hasItemColumn('minimum_stock')) {
            $rules['minimum_stock'] = ['required', 'integer', 'min:0'];
        }

        if ($this->hasItemColumn('price')) {
            $rules['price'] = ['required', 'numeric', 'min:0'];
        }

        if ($this->hasItemColumn('image')) {
            $rules['image'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
        }

        if ($this->hasItemColumn('description')) {
            $rules['description'] = ['nullable', 'string'];
        }

        if ($this->hasItemColumn('is_active')) {
            $rules['is_active'] = ['required', 'boolean'];
        }

        if (Schema::hasTable('item_stocks') && $this->stockColumn()) {
            $rules['stocks'] = ['nullable', 'array'];
            $rules['stocks.*'] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
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

    private function syncStocks(Item $item, Request $request): void
    {
        $stockColumn = $this->stockColumn();

        if (!$stockColumn || !$request->has('stocks')) {
            return;
        }

        foreach ($request->stocks as $warehouseId => $stockValue) {
            if ($stockValue === null || $stockValue === '') {
                $stockValue = 0;
            }

            ItemStock::updateOrCreate(
                [
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouseId,
                ],
                [
                    $stockColumn => (int) $stockValue,
                ]
            );
        }
    }
}