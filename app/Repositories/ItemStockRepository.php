<?php

namespace App\Repositories;

use App\Models\ItemStock;

class ItemStockRepository
{
    public function updateOrCreate($itemId, $warehouseId, $stockVal): ItemStock
    {
        $itemStock = ItemStock::updateOrCreate(
            [
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'quantity' => (int) $stockVal,
            ]
        );

        return $itemStock;
    }

    public function store(array $data){
        $itemStock = new ItemStock($data);
        $itemStock->save();

        return $itemStock;
    }

    public function update(ItemStock $itemStock, array $data) {
        $itemStock->update($data);

        return $itemStock;
    }
}
