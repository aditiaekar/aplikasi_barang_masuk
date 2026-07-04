<?php

namespace App\Repositories;

use App\Models\Item;
use App\Models\StockOutRequest;
use App\Models\StockOutRequestItem;
use Illuminate\Support\Collection;

class StockOutRequestRepository
{
    public function generateRequestNumber(): string
    {
        $prefix = 'BK-' . now()->format('Ymd') . '-';
        $lastNumber = StockOutRequest::query()
            ->where('request_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('request_number');

        $sequence = $lastNumber ? ((int) substr($lastNumber, -3)) + 1 : 1;

        return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public function getAvailableItemsByWarehouse(int $warehouseId): Collection
    {
        return Item::query()
            ->join('item_stocks', 'items.id', '=', 'item_stocks.item_id')
            ->where('item_stocks.warehouse_id', $warehouseId)
            ->where('item_stocks.quantity', '>', 0)
            ->where('items.is_active', true)
            ->select('items.*', 'item_stocks.quantity as available_stock')
            ->orderBy('items.name')
            ->get();
    }

    public function store(array $data): StockOutRequest
    {
        return StockOutRequest::create($data);
    }

    public function storeItem(array $data): StockOutRequestItem
    {
        return StockOutRequestItem::create($data);
    }

    public function updateRequest(StockOutRequest $stockOutRequest, array $data): StockOutRequest
    {
        $stockOutRequest->update($data);

        return $stockOutRequest;
    }

    public function deleteItems(StockOutRequest $stockOutRequest): void
    {
        StockOutRequestItem::where('stock_out_request_id', $stockOutRequest->id)->delete();
    }

    public function delete(StockOutRequest $stockOutRequest): void
    {
        $stockOutRequest->delete();
    }

    public function reject(StockOutRequest $stockOutRequest, array $payload) {
        $stockOutRequest->update($payload);

    }
}
