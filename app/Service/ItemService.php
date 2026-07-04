<?php

namespace App\Service;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

use App\Models\Item;

use App\Repositories\ItemRepository;
use App\Repositories\ItemStockRepository;

class ItemService
{
    protected $itemStockRepo;
    protected $itemRepo;

    public function __construct(
        ItemRepository $itemRepo,
        ItemStockRepository $itemStockRepo
    ) {
        $this->itemStockRepo = $itemStockRepo;
        $this->itemRepo = $itemRepo;
    }

    private function itemStockColumn(): string
    {
        return 'quantity';
    }

    private function syncStocks(Item $item, array $stocks): void
    {
        foreach ($stocks as $warehouseId => $stockValue) {
            if ($stockValue === null || $stockValue === '') {
                $stockValue = 0;
            }
            $itemStock = $this->itemStockRepo->updateOrCreate($item->id, $warehouseId, $stockValue);
        }
    }

    private function hasItemColumn(string $column): bool
    {
        return Schema::hasColumn('items', $column);
    }

    public function store(Request $request): Item
    {
        try {
            $payload = $request->validate($this->rules());

            if ($request->hasFile('image') && $this->hasItemColumn('image')) {
                $payload['image'] = $request->file('image')->store('items', 'public');
            }

            $item = $this->itemRepo->store($payload);
            $validStocks = array_filter($payload['stocks'], function ($value) {
                return is_numeric($value);
            });

            if ($validStocks) {
                $this->syncStocks($item, $payload['stocks']);
            }

            return $item;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(Item $item, Request $request)
    {
        try {
            $payload = $request->validate($this->rules($item->id));

            if ($request->hasFile('image') && $this->hasItemColumn('image')) {
                if ($item->image && Storage::disk('public')->exists($item->image)) {
                    Storage::disk('public')->delete($item->image);
                }

                $payload['image'] = $request->file('image')->store('items', 'public');
            }
            $this->itemRepo->update($item, $payload);
            $validStocks = array_filter($payload['stocks'], function ($value) {
                return is_numeric($value);
            });

            if ($validStocks) {
                $this->syncStocks($item, $payload['stocks']);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy(Item $item)
    {
        try {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }

            $this->itemRepo->destroy($item);
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

        if ($this->hasItemColumn('image')) {
            $rules['image'] = [
                'nullable',
                'image',
                'mimes:jpg,jpeg,pngpeg,webp',
                'max:5024',
            ];
        }

        if (Schema::hasTable('item_stocks') && $this->itemStockColumn()) {
            $rules['stocks'] = ['nullable', 'array'];
            $rules['stocks.*'] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
    }
}
