<?php

namespace App\Repositories;

use App\Models\StockInRequest;
use App\Models\StockInRequestItem;


class StockInRequestRepository
{
    public function generateRequestNumber(): string
    {
        $prefix = 'BM-' . now()->format('Ymd') . '-';

        $lastNumber = StockInRequest::query()
            ->where('request_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('request_number');

        $sequence = 1;

        if ($lastNumber) {
            $lastSequence = (int) substr($lastNumber, -3);
            $sequence = $lastSequence + 1;
        }

        return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public function store(array $data): StockInRequest
    {
        return StockInRequest::create($data);
    }

    public function storeItem(array $data): StockInRequestItem
    {
        return StockInRequestItem::create($data);
    }

    public function updateRequest(StockInRequest $stockInRequest, array $data): StockInRequest
    {
        $stockInRequest->update($data);
        return $stockInRequest;
    }

    public function delete(StockInRequest $stockInRequest) {
        $stockInRequest->delete();
    }

    public function deleteItem(StockInRequest $stockInRequest)
    {
        StockInRequestItem::where('stock_in_request_id', $stockInRequest->id)->delete();
    }

    public function reject(StockInRequest $stockInRequest, array $payload) {
        $stockInRequest->update($payload);

    }
}
