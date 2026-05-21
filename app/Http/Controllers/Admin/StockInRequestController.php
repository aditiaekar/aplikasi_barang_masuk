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