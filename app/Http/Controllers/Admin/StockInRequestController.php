<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockInRequest;
use App\Models\StockInRequestItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class StockInRequestController extends Controller
{
    private string $requestTable = 'stock_in_requests';
    private string $requestItemTable = 'stock_in_request_items';
    private string $stockInTable = 'stock_ins';
    private string $stockInItemTable = 'stock_in_items';
    private string $itemStockTable = 'item_stocks';
    private string $stockMutationTable = 'stock_mutations';

    private function column(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function requestNumberColumn(): ?string
    {
        return $this->column($this->requestTable, [
            'request_number',
            'number',
            'code',
            'transaction_number',
            'reference_number',
            'reference_no',
        ]);
    }

    private function requestDateColumn(): ?string
    {
        return $this->column($this->requestTable, [
            'request_date',
            'date',
            'transaction_date',
            'received_date',
        ]);
    }

    private function requestNoteColumn(): ?string
    {
        return $this->column($this->requestTable, [
            'note',
            'notes',
            'description',
            'remarks',
        ]);
    }

    private function createdByColumn(): ?string
    {
        return $this->column($this->requestTable, [
            'created_by',
            'requested_by',
            'user_id',
        ]);
    }

    private function statusColumn(): ?string
    {
        return $this->column($this->requestTable, [
            'status',
        ]);
    }

    private function requestItemForeignKey(): string
    {
        return $this->column($this->requestItemTable, [
            'stock_in_request_id',
            'request_id',
            'stock_request_id',
        ]) ?? 'stock_in_request_id';
    }

    private function quantityColumn(): string
    {
        return $this->column($this->requestItemTable, [
            'quantity',
            'qty',
            'qty_received',
            'amount',
        ]) ?? 'quantity';
    }

    private function stockInNumberColumn(): ?string
    {
        return $this->column($this->stockInTable, [
            'stock_in_number',
            'number',
            'code',
            'transaction_number',
            'reference_number',
            'reference_no',
        ]);
    }

    private function stockInDateColumn(): ?string
    {
        return $this->column($this->stockInTable, [
            'stock_in_date',
            'date',
            'transaction_date',
            'received_date',
        ]);
    }

    private function stockInNoteColumn(): ?string
    {
        return $this->column($this->stockInTable, [
            'note',
            'notes',
            'description',
            'remarks',
        ]);
    }

    private function stockInRequestForeignKey(): ?string
    {
        return $this->column($this->stockInTable, [
            'stock_in_request_id',
            'request_id',
            'stock_request_id',
        ]);
    }

    private function stockInItemForeignKey(): string
    {
        return $this->column($this->stockInItemTable, [
            'stock_in_id',
            'stock_id',
        ]) ?? 'stock_in_id';
    }

    private function stockInItemQuantityColumn(): string
    {
        return $this->column($this->stockInItemTable, [
            'quantity',
            'qty',
            'qty_received',
            'amount',
        ]) ?? 'quantity';
    }

    private function stockInItemNoteColumn(): ?string
    {
        return $this->column($this->stockInItemTable, [
            'note',
            'notes',
            'description',
            'remarks',
        ]);
    }

    private function itemStockQuantityColumn(): string
    {
        return $this->column($this->itemStockTable, [
            'qty_on_hand',
            'quantity',
            'qty',
            'stock',
            'current_stock',
        ]) ?? 'qty_on_hand';
    }

    private function approvedByColumn(): ?string
    {
        return $this->column($this->requestTable, [
            'approved_by',
            'processed_by',
        ]);
    }

    private function approvedAtColumn(): ?string
    {
        return $this->column($this->requestTable, [
            'approved_at',
            'processed_at',
        ]);
    }

    private function rejectedReasonColumn(): ?string
    {
        return $this->column($this->requestTable, [
            'rejected_reason',
            'reject_reason',
            'reason',
        ]);
    }

    private function generateStockInNumber(): string
    {
        $prefix = 'SIN-' . now()->format('Ymd') . '-';

        $numberColumn = $this->stockInNumberColumn();

        if (!$numberColumn) {
            return $prefix . '001';
        }

        $lastNumber = DB::table($this->stockInTable)
            ->where($numberColumn, 'like', $prefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value($numberColumn);

        $sequence = 1;

        if ($lastNumber) {
            $lastSequence = (int) substr($lastNumber, -3);
            $sequence = $lastSequence + 1;
        }

        return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    private function itemNoteColumn(): ?string
    {
        return $this->column($this->requestItemTable, [
            'note',
            'notes',
            'description',
            'remarks',
        ]);
    }

    private function generateRequestNumber(): string
    {
        $prefix = 'BM-' . now()->format('Ymd') . '-';

        $numberColumn = $this->requestNumberColumn();

        if (!$numberColumn) {
            return $prefix . '001';
        }

        $lastNumber = StockInRequest::query()
            ->where($numberColumn, 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value($numberColumn);

        $sequence = 1;

        if ($lastNumber) {
            $lastSequence = (int) substr($lastNumber, -3);
            $sequence = $lastSequence + 1;
        }

        return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    private function activeQuery(string $modelClass)
    {
        $query = $modelClass::query();
        $table = (new $modelClass)->getTable();

        if (Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', true);
        }

        return $query;
    }

    private function detailPayload(int $stockInRequestId, int $itemId, int $quantity, ?string $note = null): array
    {
        $foreignKey = $this->requestItemForeignKey();
        $quantityColumn = $this->quantityColumn();
        $itemNoteColumn = $this->itemNoteColumn();

        $payload = [
            $foreignKey => $stockInRequestId,
            'item_id' => $itemId,
            $quantityColumn => $quantity,
        ];

        if (Schema::hasColumn($this->requestItemTable, 'unit_id')) {
            if (!Schema::hasColumn('items', 'unit_id')) {
                throw ValidationException::withMessages([
                    'item_id' => 'Kolom unit_id tidak ditemukan pada tabel barang.',
                ]);
            }

            $unitId = Item::where('id', $itemId)->value('unit_id');

            if (!$unitId) {
                throw ValidationException::withMessages([
                    'item_id' => 'Barang yang dipilih belum memiliki satuan. Silakan lengkapi satuan barang terlebih dahulu.',
                ]);
            }

            $payload['unit_id'] = $unitId;
        }

        if ($itemNoteColumn) {
            $payload[$itemNoteColumn] = $note;
        }

        return $payload;
    }

    public function index(Request $request)
    {
        $numberColumn = $this->requestNumberColumn();
        $dateColumn = $this->requestDateColumn();
        $noteColumn = $this->requestNoteColumn();
        $statusColumn = $this->statusColumn();
        $quantityColumn = $this->quantityColumn();

        $stockInRequests = StockInRequest::query()
            ->with(['supplier', 'warehouse', 'items.item'])
            ->when($request->search, function ($query, $search) use ($numberColumn, $noteColumn) {
                $query->where(function ($query) use ($search, $numberColumn, $noteColumn) {
                    if ($numberColumn) {
                        $query->orWhere($numberColumn, 'like', "%{$search}%");
                    }

                    if ($noteColumn) {
                        $query->orWhere($noteColumn, 'like', "%{$search}%");
                    }

                    $query->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('name', 'like', "%{$search}%");
                    });

                    $query->orWhereHas('warehouse', function ($warehouseQuery) use ($search) {
                        $warehouseQuery->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->status && $statusColumn, function ($query) use ($request, $statusColumn) {
                $query->where($statusColumn, $request->status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.stock-in-requests.index', compact(
            'stockInRequests',
            'numberColumn',
            'dateColumn',
            'noteColumn',
            'statusColumn',
            'quantityColumn'
        ));
    }

    public function create()
    {
        $suppliers = $this->activeQuery(Supplier::class)->orderBy('name')->get();
        $warehouses = $this->activeQuery(Warehouse::class)->orderBy('name')->get();
        $items = $this->activeQuery(Item::class)->orderBy('name')->get();

        return view('admin.stock-in-requests.create', compact(
            'suppliers',
            'warehouses',
            'items'
        ));
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
            'item_note' => ['nullable', 'array'],
            'item_note.*' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $headerPayload = [];

            if ($numberColumn = $this->requestNumberColumn()) {
                $headerPayload[$numberColumn] = $this->generateRequestNumber();
            }

            if (Schema::hasColumn($this->requestTable, 'supplier_id')) {
                $headerPayload['supplier_id'] = $validated['supplier_id'];
            }

            if (Schema::hasColumn($this->requestTable, 'warehouse_id')) {
                $headerPayload['warehouse_id'] = $validated['warehouse_id'];
            }

            if ($dateColumn = $this->requestDateColumn()) {
                $headerPayload[$dateColumn] = $validated['request_date'];
            }

            if ($noteColumn = $this->requestNoteColumn()) {
                $headerPayload[$noteColumn] = $validated['note'] ?? null;
            }

            if ($createdByColumn = $this->createdByColumn()) {
                $headerPayload[$createdByColumn] = Auth::id();
            }

            if ($statusColumn = $this->statusColumn()) {
                $headerPayload[$statusColumn] = 'pending';
            }

            $stockInRequest = StockInRequest::create($headerPayload);

            foreach ($validated['item_id'] as $index => $itemId) {
                StockInRequestItem::create(
                    $this->detailPayload(
                        $stockInRequest->id,
                        (int) $itemId,
                        (int) $validated['quantity'][$index],
                        $validated['item_note'][$index] ?? null
                    )
                );
            }
        });

        return redirect()
            ->route('admin.stock-in-requests.index')
            ->with('success', 'Transaksi barang masuk berhasil dibuat.');
    }

    public function show(StockInRequest $stockInRequest)
    {
        $stockInRequest->load([
            'supplier',
            'warehouse',
            'items.item',
        ]);

        $numberColumn = $this->requestNumberColumn();
        $dateColumn = $this->requestDateColumn();
        $noteColumn = $this->requestNoteColumn();
        $statusColumn = $this->statusColumn();
        $quantityColumn = $this->quantityColumn();
        $itemNoteColumn = $this->itemNoteColumn();

        return view('admin.stock-in-requests.show', compact(
            'stockInRequest',
            'numberColumn',
            'dateColumn',
            'noteColumn',
            'statusColumn',
            'quantityColumn',
            'itemNoteColumn'
        ));
    }

    public function edit(StockInRequest $stockInRequest)
    {
        $stockInRequest->load(['items.item']);

        $suppliers = $this->activeQuery(Supplier::class)->orderBy('name')->get();
        $warehouses = $this->activeQuery(Warehouse::class)->orderBy('name')->get();
        $items = $this->activeQuery(Item::class)->orderBy('name')->get();

        $numberColumn = $this->requestNumberColumn();
        $dateColumn = $this->requestDateColumn();
        $noteColumn = $this->requestNoteColumn();
        $quantityColumn = $this->quantityColumn();
        $itemNoteColumn = $this->itemNoteColumn();

        return view('admin.stock-in-requests.edit', compact(
            'stockInRequest',
            'suppliers',
            'warehouses',
            'items',
            'numberColumn',
            'dateColumn',
            'noteColumn',
            'quantityColumn',
            'itemNoteColumn'
        ));
    }

    public function update(Request $request, StockInRequest $stockInRequest)
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
            'item_note' => ['nullable', 'array'],
            'item_note.*' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $stockInRequest) {
            $headerPayload = [];

            if (Schema::hasColumn($this->requestTable, 'supplier_id')) {
                $headerPayload['supplier_id'] = $validated['supplier_id'];
            }

            if (Schema::hasColumn($this->requestTable, 'warehouse_id')) {
                $headerPayload['warehouse_id'] = $validated['warehouse_id'];
            }

            if ($dateColumn = $this->requestDateColumn()) {
                $headerPayload[$dateColumn] = $validated['request_date'];
            }

            if ($noteColumn = $this->requestNoteColumn()) {
                $headerPayload[$noteColumn] = $validated['note'] ?? null;
            }

            $stockInRequest->update($headerPayload);

            $foreignKey = $this->requestItemForeignKey();

            StockInRequestItem::where($foreignKey, $stockInRequest->id)->delete();

            foreach ($validated['item_id'] as $index => $itemId) {
                StockInRequestItem::create(
                    $this->detailPayload(
                        $stockInRequest->id,
                        (int) $itemId,
                        (int) $validated['quantity'][$index],
                        $validated['item_note'][$index] ?? null
                    )
                );
            }
        });

        return redirect()
            ->route('admin.stock-in-requests.index')
            ->with('success', 'Transaksi barang masuk berhasil diperbarui.');
    }

    public function approve(StockInRequest $stockInRequest)
    {
        try {
            DB::transaction(function () use ($stockInRequest) {
                $statusColumn = $this->statusColumn();
                $foreignKey = $this->requestItemForeignKey();
                $quantityColumn = $this->quantityColumn();

                $lockedRequest = StockInRequest::query()
                    ->whereKey($stockInRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($statusColumn && $lockedRequest->{$statusColumn} !== 'pending') {
                    throw ValidationException::withMessages([
                        'status' => 'Transaksi barang masuk ini sudah diproses.',
                    ]);
                }

                $requestItems = DB::table($this->requestItemTable)
                    ->where($foreignKey, $lockedRequest->id)
                    ->get();

                if ($requestItems->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => 'Transaksi barang masuk belum memiliki detail barang.',
                    ]);
                }

                $stockInPayload = [];

                if ($stockInRequestForeignKey = $this->stockInRequestForeignKey()) {
                    $stockInPayload[$stockInRequestForeignKey] = $lockedRequest->id;
                }

                if ($numberColumn = $this->stockInNumberColumn()) {
                    $stockInPayload[$numberColumn] = $this->generateStockInNumber();
                }

                if (Schema::hasColumn($this->stockInTable, 'supplier_id')) {
                    $stockInPayload['supplier_id'] = $lockedRequest->supplier_id;
                }

                if (Schema::hasColumn($this->stockInTable, 'warehouse_id')) {
                    $stockInPayload['warehouse_id'] = $lockedRequest->warehouse_id;
                }

                if (Schema::hasColumn($this->stockInTable, 'received_by')) {
                    $stockInPayload['received_by'] = Auth::id();
                }

                if (Schema::hasColumn($this->stockInTable, 'approved_by')) {
                    $stockInPayload['approved_by'] = Auth::id();
                }

                if (Schema::hasColumn($this->stockInTable, 'created_by')) {
                    $stockInPayload['created_by'] = Auth::id();
                }

                if ($stockInDateColumn = $this->stockInDateColumn()) {
                    $stockInPayload[$stockInDateColumn] = now()->toDateString();
                }

                if ($stockInNoteColumn = $this->stockInNoteColumn()) {
                    $requestNoteColumn = $this->requestNoteColumn();
                    $stockInPayload[$stockInNoteColumn] = $requestNoteColumn ? $lockedRequest->{$requestNoteColumn} : null;
                }

                if (Schema::hasColumn($this->stockInTable, 'created_at')) {
                    $stockInPayload['created_at'] = now();
                }

                if (Schema::hasColumn($this->stockInTable, 'updated_at')) {
                    $stockInPayload['updated_at'] = now();
                }

                $stockInId = DB::table($this->stockInTable)->insertGetId($stockInPayload);

                foreach ($requestItems as $requestItem) {
                    $quantity = (int) $requestItem->{$quantityColumn};

                    $stockInItemPayload = [
                        $this->stockInItemForeignKey() => $stockInId,
                        'item_id' => $requestItem->item_id,
                        $this->stockInItemQuantityColumn() => $quantity,
                    ];

                    if (Schema::hasColumn($this->stockInItemTable, 'unit_id')) {
                        if (isset($requestItem->unit_id) && $requestItem->unit_id) {
                            $stockInItemPayload['unit_id'] = $requestItem->unit_id;
                        } else {
                            $stockInItemPayload['unit_id'] = Item::where('id', $requestItem->item_id)->value('unit_id');
                        }
                    }

                    if ($stockInItemNoteColumn = $this->stockInItemNoteColumn()) {
                        $requestItemNoteColumn = $this->itemNoteColumn();
                        $stockInItemPayload[$stockInItemNoteColumn] = $requestItemNoteColumn
                            ? ($requestItem->{$requestItemNoteColumn} ?? null)
                            : null;
                    }

                    if (Schema::hasColumn($this->stockInItemTable, 'created_at')) {
                        $stockInItemPayload['created_at'] = now();
                    }

                    if (Schema::hasColumn($this->stockInItemTable, 'updated_at')) {
                        $stockInItemPayload['updated_at'] = now();
                    }

                    DB::table($this->stockInItemTable)->insert($stockInItemPayload);

                    $stockQuantityColumn = $this->itemStockQuantityColumn();

                    $itemStock = DB::table($this->itemStockTable)
                        ->where('warehouse_id', $lockedRequest->warehouse_id)
                        ->where('item_id', $requestItem->item_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$itemStock) {
                        $itemStockPayload = [
                            'warehouse_id' => $lockedRequest->warehouse_id,
                            'item_id' => $requestItem->item_id,
                            $stockQuantityColumn => 0,
                        ];

                        if (Schema::hasColumn($this->itemStockTable, 'created_at')) {
                            $itemStockPayload['created_at'] = now();
                        }

                        if (Schema::hasColumn($this->itemStockTable, 'updated_at')) {
                            $itemStockPayload['updated_at'] = now();
                        }

                        DB::table($this->itemStockTable)->insert($itemStockPayload);

                        $itemStock = DB::table($this->itemStockTable)
                            ->where('warehouse_id', $lockedRequest->warehouse_id)
                            ->where('item_id', $requestItem->item_id)
                            ->lockForUpdate()
                            ->first();
                    }

                    $stockBefore = (int) $itemStock->{$stockQuantityColumn};
                    $stockAfter = $stockBefore + $quantity;

                    $itemStockUpdatePayload = [
                        $stockQuantityColumn => $stockAfter,
                    ];

                    if (Schema::hasColumn($this->itemStockTable, 'updated_at')) {
                        $itemStockUpdatePayload['updated_at'] = now();
                    }

                    DB::table($this->itemStockTable)
                        ->where('id', $itemStock->id)
                        ->update($itemStockUpdatePayload);

                    $mutationPayload = [];

                    if (Schema::hasColumn($this->stockMutationTable, 'item_id')) {
                        $mutationPayload['item_id'] = $requestItem->item_id;
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'warehouse_id')) {
                        $mutationPayload['warehouse_id'] = $lockedRequest->warehouse_id;
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'stock_in_id')) {
                        $mutationPayload['stock_in_id'] = $stockInId;
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'reference_type')) {
                        $mutationPayload['reference_type'] = 'stock_in';
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'reference_id')) {
                        $mutationPayload['reference_id'] = $stockInId;
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'mutation_type')) {
                        $mutationPayload['mutation_type'] = 'in';
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'type')) {
                        $mutationPayload['type'] = 'in';
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'quantity')) {
                        $mutationPayload['quantity'] = $quantity;
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'qty')) {
                        $mutationPayload['qty'] = $quantity;
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'stock_before')) {
                        $mutationPayload['stock_before'] = $stockBefore;
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'stock_after')) {
                        $mutationPayload['stock_after'] = $stockAfter;
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'mutation_date')) {
                        $mutationPayload['mutation_date'] = now();
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'date')) {
                        $mutationPayload['date'] = now()->toDateString();
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'description')) {
                        $requestNumberColumn = $this->requestNumberColumn();

                        $mutationPayload['description'] = 'Barang masuk dari approval transaksi '
                            . ($requestNumberColumn ? $lockedRequest->{$requestNumberColumn} : $lockedRequest->id);
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'created_by')) {
                        $mutationPayload['created_by'] = Auth::id();
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'created_at')) {
                        $mutationPayload['created_at'] = now();
                    }

                    if (Schema::hasColumn($this->stockMutationTable, 'updated_at')) {
                        $mutationPayload['updated_at'] = now();
                    }

                    DB::table($this->stockMutationTable)->insert($mutationPayload);
                }

                $requestUpdatePayload = [];

                if ($statusColumn) {
                    $requestUpdatePayload[$statusColumn] = 'approved';
                }

                if ($approvedByColumn = $this->approvedByColumn()) {
                    $requestUpdatePayload[$approvedByColumn] = Auth::id();
                }

                if ($approvedAtColumn = $this->approvedAtColumn()) {
                    $requestUpdatePayload[$approvedAtColumn] = now();
                }

                if ($rejectedReasonColumn = $this->rejectedReasonColumn()) {
                    $requestUpdatePayload[$rejectedReasonColumn] = null;
                }

                $lockedRequest->update($requestUpdatePayload);
            });

            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('success', 'Transaksi barang masuk berhasil disetujui dan stok berhasil diperbarui.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('error', 'Transaksi barang masuk gagal disetujui. Pastikan tabel stock_ins, stock_in_items, item_stocks, dan stock_mutations sudah sesuai.');
        }
    }

    public function reject(Request $request, StockInRequest $stockInRequest)
    {
        $validated = $request->validate([
            'rejected_reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($stockInRequest, $validated) {
                $statusColumn = $this->statusColumn();

                $lockedRequest = StockInRequest::query()
                    ->whereKey($stockInRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($statusColumn && $lockedRequest->{$statusColumn} !== 'pending') {
                    throw ValidationException::withMessages([
                        'status' => 'Transaksi barang masuk ini sudah diproses.',
                    ]);
                }

                $requestUpdatePayload = [];

                if ($statusColumn) {
                    $requestUpdatePayload[$statusColumn] = 'rejected';
                }

                if ($approvedByColumn = $this->approvedByColumn()) {
                    $requestUpdatePayload[$approvedByColumn] = Auth::id();
                }

                if ($approvedAtColumn = $this->approvedAtColumn()) {
                    $requestUpdatePayload[$approvedAtColumn] = now();
                }

                if ($rejectedReasonColumn = $this->rejectedReasonColumn()) {
                    $requestUpdatePayload[$rejectedReasonColumn] = $validated['rejected_reason'] ?? null;
                }

                $lockedRequest->update($requestUpdatePayload);
            });

            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('success', 'Transaksi barang masuk berhasil ditolak.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('error', 'Transaksi barang masuk gagal ditolak.');
        }
    }

    public function destroy(StockInRequest $stockInRequest)
    {
        try {
            DB::transaction(function () use ($stockInRequest) {
                $foreignKey = $this->requestItemForeignKey();

                StockInRequestItem::where($foreignKey, $stockInRequest->id)->delete();

                $stockInRequest->delete();
            });

            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('success', 'Transaksi barang masuk berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('error', 'Transaksi barang masuk tidak dapat dihapus karena sudah digunakan pada data lain.');
        }
    }
}
