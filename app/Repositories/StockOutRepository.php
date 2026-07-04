<?php

namespace App\Repositories;

use App\Models\StockOut;
use App\Models\StockOutItem;

class StockOutRepository
{
    public function generateStockOutNumber(): string
    {
        $prefix = 'SOUT-' . now()->format('Ymd') . '-';

        $lastNumber = StockOut::where('stock_out_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('stock_out_number');

        $sequence = 1;

        if ($lastNumber) {
            $lastSequence = (int) substr($lastNumber, -3);
            $sequence = $lastSequence + 1;
        }

        return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public function store(array $payload): StockOut {
        $stockOut= new StockOut($payload);
        $stockOut->save();

        return $stockOut;
    }

    public function storeItem(array $payload) {
        $stockOutItem = new StockOutItem($payload);
        $stockOutItem->save();
        return $stockOutItem;
    }
}
