<?php

namespace App\Service;

use App\Models\StockInRequest;
use App\Models\ItemStock;
use App\Models\StockMutation;
use App\Models\StockLayer;
use App\Repositories\ItemRepository;
use App\Repositories\ItemStockRepository;
use App\Repositories\StockInRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Repositories\StockMutationRepository;
use App\Repositories\StockInRequestRepository;

class StockInRequestService
{
    protected $itemRepo;
    protected $stockInRequestRepo;
    protected $stockInRepo;
    protected $itemStockRepo;
    protected $stockMutationRepo;

    public function __construct(
        StockInRequestRepository $stockInRequestRepo,
        ItemRepository $itemRepo,
        StockInRepository $stockInRepo,
        ItemStockRepository $itemStockRepo,
        StockMutationRepository $stockMutationRepo,
    ) {
        $this->stockInRequestRepo = $stockInRequestRepo;
        $this->itemRepo = $itemRepo;
        $this->stockInRepo = $stockInRepo;
        $this->itemStockRepo = $itemStockRepo;
        $this->stockMutationRepo = $stockMutationRepo;
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'request_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'item_id' => ['required', 'array', 'min:1'],
            'item_id.*' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'array', 'min:1'],
            'quantity.*' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'array', 'min:1'],
            'price.*' => ['required', 'numeric', 'min:1'],
            'item_note' => ['nullable', 'array'],
            'item_note.*' => ['nullable', 'string'],
        ], [
            'item_id.required' => 'At least one item is required',
            'item_id.min' => 'At least one item is required',
            'item_id.*.required' => 'At least one item is required',
            'quantity.required' => 'At least one item is required',
            'quantity.min' => 'At least one item is required',
            'quantity.*.required' => 'At least one item is required',
            'price.min' => 'At least one item is required',
            'price.*.required' => 'At least one item is required'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $stockInRequestPayload = [];

                $stockInRequestPayload['request_number'] = $this->stockInRequestRepo->generateRequestNumber();
                $stockInRequestPayload['supplier_id'] = $validated['supplier_id'];
                $stockInRequestPayload['warehouse_id'] = $validated['warehouse_id'];
                $stockInRequestPayload['request_date'] = $validated['request_date'];
                $stockInRequestPayload['note'] = $validated['note'] ?? null;
                $stockInRequestPayload['requested_by'] = Auth::id();
                $stockInRequestPayload['status'] = 'pending';

                $stockInRequest = $this->stockInRequestRepo->store($stockInRequestPayload);

                foreach ($validated['item_id'] as $index => $itemId) {
                    $item = $this->itemRepo->getItemWithUnit($validated['item_id'][$index]);

                    $stockInRequestItemPayload = [];

                    $stockInRequestItemPayload['stock_in_request_id'] = $stockInRequest->id;
                    $stockInRequestItemPayload['item_id'] = $itemId;
                    $stockInRequestItemPayload['unit_id'] = $item->unit->id;
                    $stockInRequestItemPayload['quantity'] = $validated['quantity'][$index];
                    $stockInRequestItemPayload['price'] = $validated['price'][$index];
                    $stockInRequestItemPayload['note'] = $validated['item_note'][$index] ?? null;

                    $this->stockInRequestRepo->storeItem($stockInRequestItemPayload);
                }
            });
        } catch (\Throwable $th) {
            throw $th;
        } catch (QueryException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data karena masalah sistem. Silakan coba lagi.');
        }
    }

    public function update(StockInRequest $stockInRequest, Request $request)
    {
        $validated = $request->validate(
            [
                'supplier_id' => ['required', 'exists:suppliers,id'],
                'warehouse_id' => ['required', 'exists:warehouses,id'],
                'request_date' => ['required', 'date'],
                'note' => ['nullable', 'string'],
                'item_id' => ['required', 'array', 'min:1'],
                'item_id.*' => ['required', 'exists:items,id'],
                'quantity' => ['required', 'array', 'min:1'],
                'quantity.*' => ['required', 'integer', 'min:1'],
                'price' => ['required', 'array', 'min:1'],
                'price.*' => ['required', 'numeric', 'min:1'],
                'item_note' => ['nullable', 'array'],
                'item_note.*' => ['nullable', 'string'],
            ],
            [
                'item_id.required' => 'At least one item is required',
                'item_id.min' => 'At least one item is required',
                'item_id.*.required' => 'At least one item is required',
                'quantity.required' => 'At least one item is required',
                'quantity.min' => 'At least one item is required',
                'quantity.*.required' => 'At least one item is required'
            ]
        );

        DB::transaction(function () use ($validated, $stockInRequest) {
            $stockInRequestPayload = [];

            $stockInRequestPayload['supplier_id'] = $validated['supplier_id'];
            $stockInRequestPayload['warehouse_id'] = $validated['warehouse_id'];
            $stockInRequestPayload['request_date'] = $validated['request_date'];
            $stockInRequestPayload['note'] = $validated['note'] ?? null;

            $updated = $this->stockInRequestRepo->updateRequest($stockInRequest, $stockInRequestPayload);
            $this->stockInRequestRepo->deleteItem($updated);

            foreach ($validated['item_id'] as $index => $itemId) {
                $item = $this->itemRepo->getItemWithUnit($validated['item_id'][$index]);

                $stockInRequestItemPayload = [];

                $stockInRequestItemPayload['stock_in_request_id'] = $stockInRequest->id;
                $stockInRequestItemPayload['item_id'] = $itemId;
                $stockInRequestItemPayload['unit_id'] = $item->unit->id;
                $stockInRequestItemPayload['quantity'] = $validated['quantity'][$index];
                $stockInRequestItemPayload['price'] = $validated['price'][$index];

                $stockInRequestItemPayload['note'] = $validated['item_note'][$index] ?? null;

                $this->stockInRequestRepo->storeItem($stockInRequestItemPayload);
            }
        });
    }

    public function destroy(StockInRequest $stockInRequest)
    {
        try {
            DB::transaction(function () use ($stockInRequest) {
                $this->stockInRequestRepo->deleteItem($stockInRequest);

                $this->stockInRequestRepo->delete($stockInRequest);
            });
        } catch (QueryException $e) {
            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('error', 'Transaksi barang masuk tidak dapat dihapus karena sudah digunakan pada data lain.');
        }
    }

    public function approve(StockInRequest $stockInRequest)
    {
        try {
            DB::transaction(function () use ($stockInRequest) {
                $lockedRequest = StockInRequest::query()
                    ->whereKey($stockInRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedRequest->{'status'} !== 'pending') {
                    throw ValidationException::withMessages([
                        'status' => 'Transaksi barang masuk ini sudah diproses.',
                    ]);
                }

                $requestItems = DB::table('stock_in_request_items')
                    ->where('stock_in_request_id', $lockedRequest->id)
                    ->get();

                if ($requestItems->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => 'Transaksi barang masuk belum memiliki detail barang.',
                    ]);
                }

                $stockInPayload = [];

                $stockInPayload['stock_in_request_id'] = $lockedRequest->id;
                $stockInPayload['stock_in_number'] = $this->stockInRepo->generateStockInNumber();
                $stockInPayload['supplier_id'] = $lockedRequest->supplier_id;
                $stockInPayload['warehouse_id'] = $lockedRequest->warehouse_id;
                $stockInPayload['received_by'] = Auth::id();
                $stockInPayload['approved_by'] = Auth::id();
                $stockInPayload['created_by'] = Auth::id();
                $stockInPayload['stock_in_date'] = now()->toDateString();
                $stockInPayload['note'] = $lockedRequest->note ?? null;

                $stockIn = $this->stockInRepo->store($stockInPayload);

                foreach ($requestItems as $requestItem) {
                    $quantity = (int) $requestItem->{'quantity'};

                    $stockInItemPayload = [
                        'stock_in_id' => $stockIn->id,
                        'item_id' => $requestItem->item_id,
                        'quantity' => $quantity,
                    ];
                    $stockInItemPayload['unit_id'] = $requestItem->unit_id;
                    $stockInItemPayload['note'] = $requestItem->note ?? null;
                    $stockInItemPayload['price'] = $requestItem->price;
                    $stockInItem = $this->stockInRepo->storeItem($stockInItemPayload);

                    // stock layer
                    StockLayer::create([
                        'item_id' => $requestItem->item_id,
                        'warehouse_id' => $lockedRequest->warehouse_id,
                        'stock_in_item_id' => $stockInItem->id,
                        'quantity_in' => $quantity,
                        'quantity_remaining' => $quantity,
                        'price' => $requestItem->price,
                        'received_at' => $stockIn->stock_in_date,
                    ]);

                    // item stock
                    $itemStock = ItemStock::where('warehouse_id', $lockedRequest->warehouse_id)
                        ->where('item_id', $requestItem->item_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$itemStock) {
                        $itemStockPayload = [
                            'warehouse_id' => $lockedRequest->warehouse_id,
                            'item_id' => $requestItem->item_id,
                            'quantity' => 0,
                        ];

                        $itemStock = $this->itemStockRepo->store($itemStockPayload);
                    }

                    $stockBefore = (int) $itemStock->quantity;
                    $stockAfter = $stockBefore + $quantity;

                    $itemStockUpdatePayload = [
                        'quantity' => $stockAfter,
                    ];

                    $this->itemStockRepo->update($itemStock, $itemStockUpdatePayload);

                    //mutation
                    $mutationPayload = [];

                    $mutationPayload['item_id'] = $requestItem->item_id;
                    $mutationPayload['warehouse_id'] = $lockedRequest->warehouse_id;
                    $mutationPayload['stock_in_id'] = $stockIn->id;
                    $mutationPayload['reference_type'] = 'stock_in';
                    $mutationPayload['reference_id'] = $stockIn->id;
                    $mutationPayload['mutation_type'] = 'in';
                    $mutationPayload['type'] = 'in';
                    $mutationPayload['quantity'] = $quantity;
                    $mutationPayload['qty'] = $quantity;
                    $mutationPayload['stock_before'] = $stockBefore;
                    $mutationPayload['stock_after'] = $stockAfter;
                    $mutationPayload['mutation_date'] = now();
                    $mutationPayload['date'] = now()->toDateString();
                    $requestNumberColumn = $lockedRequest->request_number;
                    $mutationPayload['description'] = 'Barang masuk dari approval transaksi ' . ($requestNumberColumn ? $requestNumberColumn : $lockedRequest->id);
                    $mutationPayload['created_by'] = Auth::id();

                    $mutation = $this->stockMutationRepo->store($mutationPayload);
                }

                $requestUpdatePayload = [];

                $requestUpdatePayload['status'] = 'approved';
                $requestUpdatePayload['approved_by'] = Auth::id();
                $requestUpdatePayload['approved_at'] = now();
                $requestUpdatePayload['rejected_reason'] = null;

                $this->stockInRequestRepo->updateRequest($lockedRequest, $requestUpdatePayload);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function reject(StockInRequest $stockInRequest, array $data)
    {
        try {
            DB::transaction(function () use ($stockInRequest, $data) {
                $lockedRequest = StockInRequest::query()
                    ->whereKey($stockInRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedRequest->{'status'} !== 'pending') {
                    throw ValidationException::withMessages([
                        'status' => 'Transaksi barang masuk ini sudah diproses.',
                    ]);
                }

                $requestUpdatePayload = [];
                $requestUpdatePayload['status'] = 'rejected';
                $requestUpdatePayload['approved_by'] = Auth::id();
                $requestUpdatePayload['approved_at'] = now();
                $requestUpdatePayload['rejected_reason'] = $data['rejected_reason'] ?? null;

                $this->stockInRequestRepo->reject($stockInRequest, $requestUpdatePayload);
            });
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('error', 'Transaksi barang masuk gagal ditolak.');
        }
    }
}
