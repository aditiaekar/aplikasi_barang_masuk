<?php

namespace App\Service;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
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

    public function store(array $validated): Item
    {
        if (array_key_exists('image', $validated)) {
            $validated['image'] = $validated['image']->store('items', 'public');
        }
        $item = $this->itemRepo->store($validated);
        return $item;
    }

    public function update(Item $item, array $validated)
    {
        if (array_key_exists('image', $validated)) {
            if ($item->image && Storage::disk('public')->exists($item->image)) {
                Storage::disk('public')->delete($item->image);
            }

            $validated['image'] = $validated['image']->store('items', 'public');
        }
        $this->itemRepo->update($item, $validated);
    }

    public function destroy(Item $item)
    {
        $this->itemRepo->destroy($item);
        if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }
    }
}
