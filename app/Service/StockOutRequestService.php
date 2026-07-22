<?php

namespace App\Service;

use App\Models\StockOutRequest;
use App\Repositories\ItemRepository;
use App\Repositories\StockOutRequestRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\ItemStock;
use App\Repositories\ItemStockRepository;
use App\Repositories\StockMutationRepository;
use App\Repositories\StockOutRepository;
use App\Models\StockOutItemLayer;
use App\Models\StockLayer;

class StockOutRequestService
{
    public function __construct(
        protected StockOutRequestRepository $stockOutRequestRepo,
        protected ItemRepository $itemRepo,
        protected StockMutationRepository $stockMutationRepo,
        protected ItemStockRepository $itemStockRepo,
        protected StockOutRepository $stockOutRepo
    ) {}

    public function store(Request $request): StockOutRequest
    {
        $validated = $this->validate($request);
        $this->validateWarehouseStock($validated);

        return DB::transaction(function () use ($validated) {
            $stockOutRequest = $this->stockOutRequestRepo->store([
                'request_number' => $this->stockOutRequestRepo->generateRequestNumber(),
                'warehouse_id' => $validated['warehouse_id'],
                'request_date' => $validated['request_date'],
                'note' => $validated['note'] ?? null,
                'requested_by' => Auth::id(),
                'status' => 'pending',
                'recipient_name' => $validated['recipient_name'],
                'recipient_postal_code' => $validated['recipient_postal_code'],
                'recipient_address' => $validated['recipient_address'],
                'recipient_phone' => $validated['recipient_phone'],
                'ems_number' => $validated['ems_number'],
                'sender_name' => $validated['sender_name'],
            ]);

            $this->storeItems($stockOutRequest, $validated);

            return $stockOutRequest;
        });
    }

    public function update(StockOutRequest $stockOutRequest, Request $request): StockOutRequest
    {
        $validated = $this->validate($request);
        $this->validateWarehouseStock($validated);

        return DB::transaction(function () use ($stockOutRequest, $validated) {
            $this->stockOutRequestRepo->updateRequest($stockOutRequest, [
                'warehouse_id' => $validated['warehouse_id'],
                'request_date' => $validated['request_date'],
                'note' => $validated['note'] ?? null,
                'recipient_name' => $validated['recipient_name'],
                'recipient_postal_code' => $validated['recipient_postal_code'],
                'recipient_address' => $validated['recipient_address'],
                'recipient_phone' => $validated['recipient_phone'],
                'ems_number' => $validated['ems_number'],
                'sender_name' => $validated['sender_name'],
            ]);

            $this->stockOutRequestRepo->deleteItems($stockOutRequest);
            $this->storeItems($stockOutRequest, $validated);

            return $stockOutRequest;
        });
    }

    public function destroy(StockOutRequest $stockOutRequest): void
    {
        DB::transaction(function () use ($stockOutRequest) {
            $this->stockOutRequestRepo->deleteItems($stockOutRequest);
            $this->stockOutRequestRepo->delete($stockOutRequest);
        });
    }

    private function validate(Request $request): array
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'request_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'item_id' => ['required', 'array', 'min:1'],
            'item_id.*' => ['required', 'distinct', 'exists:items,id'],
            'quantity' => ['required', 'array', 'min:1'],
            'quantity.*' => ['required', 'integer', 'min:1'],
            'item_note' => ['nullable', 'array'],
            'item_note.*' => ['nullable', 'string'],
            'recipient_name' => ['required', 'string'],
            'recipient_postal_code' => ['required', 'string'],
            'recipient_phone' => ['required', 'string'],
            'ems_number' => ['required', 'string'],
            'recipient_address' => ['required', 'string'],
            'sender_name' => ['required', 'string'],
        ]);

        if (count($validated['item_id']) !== count($validated['quantity'])) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah data barang dan quantity tidak sesuai.',
            ]);
        }

        return $validated;
    }

    private function validateWarehouseStock(array $validated): void
    {
        $stocks = DB::table('item_stocks')
            ->where('warehouse_id', $validated['warehouse_id'])
            ->whereIn('item_id', $validated['item_id'])
            ->pluck('quantity', 'item_id');

        foreach ($validated['item_id'] as $index => $itemId) {
            $available = (int) ($stocks[$itemId] ?? 0);
            $requested = (int) $validated['quantity'][$index];

            if ($available < $requested) {
                throw ValidationException::withMessages([
                    "quantity.$index" => "Stok barang di gudang tidak mencukupi (tersedia: {$available}).",
                ]);
            }
        }
    }

    private function storeItems(StockOutRequest $stockOutRequest, array $validated): void
    {
        foreach ($validated['item_id'] as $index => $itemId) {
            $item = $this->itemRepo->getItemWithUnit((string) $itemId);

            if (!$item?->unit) {
                throw ValidationException::withMessages([
                    "item_id.$index" => 'Barang yang dipilih belum memiliki satuan.',
                ]);
            }

            $this->stockOutRequestRepo->storeItem([
                'stock_out_request_id' => $stockOutRequest->id,
                'item_id' => $itemId,
                'unit_id' => $item->unit->id,
                'quantity' => $validated['quantity'][$index],
                'note' => $validated['item_note'][$index] ?? null,
            ]);
        }
    }

    public function approve(StockOutRequest $stockOutRequest)
    {
        DB::transaction(function () use ($stockOutRequest) {
            $lockedRequest = StockOutRequest::query()
                ->whereKey($stockOutRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->{'status'} !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi barang ini sudah diproses.',
                ]);
            }

            $requestItems = DB::table('stock_out_request_items')
                ->where('stock_out_request_id', $lockedRequest->id)
                ->get();

            if ($requestItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Transaksi barang keluar belum memiliki detail barang.',
                ]);
            }

            $stockOutPayload = [];

            $stockOutPayload['stock_out_request_id'] = $lockedRequest->id;
            $stockOutPayload['stock_out_number'] = $this->stockOutRepo->generateStockOutNumber();
            $stockOutPayload['warehouse_id'] = $lockedRequest->warehouse_id;
            $stockOutPayload['received_by'] = Auth::id();
            $stockOutPayload['approved_by'] = Auth::id();
            $stockOutPayload['created_by'] = Auth::id();
            $stockOutPayload['stock_out_date'] = now()->toDateString();
            $stockOutPayload['note'] = $lockedRequest->note ?? null;

            $stockOut = $this->stockOutRepo->store($stockOutPayload);

            foreach ($requestItems as $requestItem) {
                $quantity = (int) $requestItem->{'quantity'};

                $stockOutItemPayload = [
                    'stock_out_id' => $stockOut->id,
                    'item_id' => $requestItem->item_id,
                    'quantity' => $quantity,
                ];
                $stockOutItemPayload['unit_id'] = $requestItem->unit_id;
                $stockOutItemPayload['note'] = $requestItem->note ?? null;

                $stockOutItem = $this->stockOutRepo->storeItem($stockOutItemPayload);
                $remainingQty = $quantity;
                $totalPrice = 0;

                $layers = StockLayer::query()
                    ->where('item_id', $requestItem->item_id)
                    ->where('warehouse_id', $lockedRequest->warehouse_id)
                    ->where('quantity_remaining', '>', 0)
                    ->orderBy('received_at')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                foreach ($layers as $layer) {
                    if ($remainingQty <= 0) {
                        break;
                    }

                    $takenQty = min($remainingQty, $layer->quantity_remaining);
                    $subtotal = $takenQty * $layer->price;

                    StockOutItemLayer::create([
                        'stock_out_item_id' => $stockOutItem->id,
                        'stock_layer_id' => $layer->id,
                        'stock_in_item_id' => $layer->stock_in_item_id,
                        'quantity' => $takenQty,
                        'price' => $layer->price,
                        'subtotal' => $subtotal,
                    ]);

                    $layer->update([
                        'quantity_remaining' => $layer->quantity_remaining - $takenQty,
                    ]);

                    $totalPrice += $subtotal;
                    $remainingQty -= $takenQty;
                }
                if ($remainingQty > 0) {
                    throw ValidationException::withMessages([
                        'stock' => "Stok FIFO barang ID ({$requestItem->item_id}) tidak mencukupi.",
                    ]);
                }

                $stockOutItem->update([
                    'total_price' => $totalPrice,
                    'unit_price' => $quantity > 0 ? $totalPrice / $quantity : 0,
                ]);

                // item stock
                $itemStock = ItemStock::where('warehouse_id', $lockedRequest->warehouse_id)
                    ->where('item_id', $requestItem->item_id)
                    ->lockForUpdate()
                    ->first();

                if (!$itemStock || $itemStock->quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Stok Barang dengan ID ({$requestItem->item_id}) tidak mencukupi."
                            . " Tersedia " . ($itemStock->quantity ?? 0) . ", "
                            . " Diminta " . ($quantity) . ".",
                    ]);
                }

                $stockBefore = (int) $itemStock->quantity;
                $stockAfter = $stockBefore - $quantity;

                $this->itemStockRepo->update($itemStock, [
                    'quantity' => $stockAfter,
                ]);

                //mutation
                $mutationPayload = [];

                $mutationPayload['item_id'] = $requestItem->item_id;
                $mutationPayload['warehouse_id'] = $lockedRequest->warehouse_id;
                $mutationPayload['stock_out_id'] = $stockOut->id;
                $mutationPayload['reference_type'] = 'stock_out';
                $mutationPayload['reference_id'] = $stockOut->id;
                $mutationPayload['mutation_type'] = 'out';
                $mutationPayload['type'] = 'out';
                $mutationPayload['quantity'] = $quantity;
                $mutationPayload['qty'] = $quantity;
                $mutationPayload['stock_before'] = $stockBefore;
                $mutationPayload['stock_after'] = $stockAfter;
                $mutationPayload['mutation_date'] = now();
                $mutationPayload['date'] = now()->toDateString();
                $requestNumberColumn = $lockedRequest->request_number;
                $mutationPayload['description'] = 'Barang keluar dari approval transaksi ' . ($requestNumberColumn ? $requestNumberColumn : $lockedRequest->id);
                $mutationPayload['created_by'] = Auth::id();

                $mutation = $this->stockMutationRepo->store($mutationPayload);
            }

            $requestUpdatePayload = [];

            $requestUpdatePayload['status'] = 'approved';
            $requestUpdatePayload['approved_by'] = Auth::id();
            $requestUpdatePayload['approved_at'] = now();
            $requestUpdatePayload['rejected_reason'] = null;

            $this->stockOutRequestRepo->updateRequest($lockedRequest, $requestUpdatePayload);
        });
    }

    public function reject(StockOutRequest $stockOutRequest, array $data)
    {
        DB::transaction(function () use ($stockOutRequest, $data) {
            $lockedRequest = StockOutRequest::query()
                ->whereKey($stockOutRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRequest->{'status'} !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi barang ini sudah diproses.',
                ]);
            }

            $requestUpdatePayload = [];
            $requestUpdatePayload['status'] = 'rejected';
            $requestUpdatePayload['approved_by'] = Auth::id();
            $requestUpdatePayload['approved_at'] = now();
            $requestUpdatePayload['rejected_reason'] = $data['rejected_reason'] ?? null;

            $this->stockOutRequestRepo->reject($stockOutRequest, $requestUpdatePayload);
        });
    }
}
