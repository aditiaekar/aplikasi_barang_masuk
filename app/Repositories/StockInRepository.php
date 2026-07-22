<?php

namespace App\Repositories;

use App\Models\StockIn;
use App\Models\StockInItem;

class StockInRepository
{
    public function generateStockInNumber(): string
    {
        $prefix = 'SIN-' . now()->format('Ymd') . '-';

        $lastNumber = StockIn::where('stock_in_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('stock_in_number');

        $sequence = 1;

        if ($lastNumber) {
            $lastSequence = (int) substr($lastNumber, -3);
            $sequence = $lastSequence + 1;
        }

        return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public function store(array $payload): StockIn {
        $stockIn = new StockIn($payload);
        $stockIn->save();

        return $stockIn;
    }

    public function storeItem(array $payload) {
        $stockInItem = new StockInItem($payload);
        $stockInItem->save();
        return $stockInItem;
    }
}
