<?php

namespace App\Repositories;

use App\Models\Item;

class ItemRepository
{
    public function getAllActive()
    {
        return Item::where('is_active', 1)->orderBy('name')->get();
    }

    public function getItemWithUnit(string $itemId) {
        return Item::with('unit')->where('id',$itemId)->first();
    }

    public function store(array $data): Item
    {
        $item = new Item($data);
        $item->save();

        return $item;
    }

    public function update(Item $item, array $data): Item
    {
        $item->update($data);
        return $item;
    }

    public function destroy(Item $item): bool
    {
        $item->delete();
        return true;
    }
}
