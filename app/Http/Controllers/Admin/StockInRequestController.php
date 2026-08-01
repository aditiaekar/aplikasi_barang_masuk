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
use Yajra\DataTables\Facades\DataTables;

use App\Repositories\ItemRepository;
use App\Repositories\WarehouseRepository;
use App\Repositories\SupplierRepository;
use App\Service\StockInRequestService;

class StockInRequestController extends Controller
{
    protected $itemRepo;
    protected $supplierRepo;
    protected $warehouseRepo;
    protected $stockInRequestService;

    public function __construct(
        ItemRepository $itemRepo,
        SupplierRepository $supplierRepo,
        WarehouseRepository $warehouseRepo,
        StockInRequestService $stockInRequestService
    ) {
        $this->itemRepo = $itemRepo;
        $this->supplierRepo = $supplierRepo;
        $this->warehouseRepo = $warehouseRepo;
        $this->stockInRequestService = $stockInRequestService;
    }

    public function data(Request $request)
    {
        $query = StockInRequest::query()
            ->with(['supplier', 'warehouse', 'items.item'])
            ->withSum('items as total_qty', 'quantity');

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $searchDate = null;

            foreach (['m-d-Y', 'Y-m-d'] as $format) {
                $parsedDate = \DateTimeImmutable::createFromFormat('!' . $format, $keyword);
                $dateErrors = \DateTimeImmutable::getLastErrors();

                if (
                    $parsedDate !== false
                    && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
                    && $parsedDate->format($format) === $keyword
                ) {
                    $searchDate = $parsedDate->format('Y-m-d');
                    break;
                }
            }

            $query->where(function ($q) use ($keyword, $searchDate) {
                $q->where('request_number', 'like', "%{$keyword}%")
                    ->orWhereHas('supplier', function ($supplier) use ($keyword) {
                        $supplier->where('name', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('warehouse', function ($warehouse) use ($keyword) {
                        $warehouse->where('name', 'like', "%{$keyword}%");
                    });

                if ($searchDate !== null) {
                    $q->orWhereDate('request_date', $searchDate);
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('request_number', function ($row) {
                return "<span class='transaction-code'> " . $row->request_number . " </span>";
            })
            ->addColumn('total_item', function ($row) {
                return $row->items->count();
            })
            ->addColumn('total_qty', function ($row) {
                return $row->total_qty;
            })
            ->addColumn('status', function ($row) {
                return '<span class="badge-status badge-' . e($row->status) . '">' . e(ucfirst($row->status)) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $showUrl = route('admin.stock-in-requests.show', $row->id);

                $action = '
                <a href="' . $showUrl . '"
                   class="action-btn"
                   title="Detail">
                    <i class="bx bx-show"></i>
                </a>
            ';

                if ($row->status === 'pending') {
                    $approveUrl = route('admin.stock-in-requests.approve', $row->id);
                    $rejectUrl = route('admin.stock-in-requests.reject', $row->id);
                    $editUrl = route('admin.stock-in-requests.edit', $row->id);
                    $deleteUrl = route('admin.stock-in-requests.destroy', $row->id);

                    $csrf = csrf_field();
                    $methodDelete = method_field('DELETE');

                    $action .= '
                    <form action="' . $approveUrl . '"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm(\'Yakin ingin menyetujui transaksi barang masuk ini? Stok akan otomatis bertambah.\')">
                        ' . $csrf . '

                        <button type="submit" class="action-btn" title="Approve">
                            <i class="bx bx-check"></i>
                        </button>
                    </form>

                    <form action="' . $rejectUrl . '"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm(\'Yakin ingin menolak transaksi barang masuk ini?\')">
                        ' . $csrf . '

                        <button type="submit" class="action-btn" title="Reject">
                            <i class="bx bx-x"></i>
                        </button>
                    </form>

                    <a href="' . $editUrl . '"
                       class="action-btn"
                       title="Edit">
                        <i class="bx bx-edit"></i>
                    </a>

                    <form action="' . $deleteUrl . '"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm(\'Yakin ingin menghapus transaksi barang masuk ini?\')">
                        ' . $csrf . '
                        ' . $methodDelete . '

                        <button type="submit" class="action-btn" title="Hapus">
                            <i class="bx bx-trash"></i>
                        </button>
                    </form>
                ';
                }

                return '<div class="d-flex align-items-center gap-1">' . $action . '</div>';
            })

            ->rawColumns(['request_number', 'status', 'action'])

            ->make(true);
    }

    public function itemData(Request $request)
    {
        $query = Item::query()
            ->with([
                'category:id,name',
                'unit:id,name',
            ])
            ->where('is_active', true)
            ->select([
                'id',
                'item_code',
                'name',
                'category_id',
                'unit_id',
            ]);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('category_name', function (Item $item) {
                return $item->category?->name ?? '-';
            })
            ->addColumn('unit_name', function (Item $item) {
                return $item->unit?->name ?? '-';
            })
            ->make(true);
    }

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
        // $numberColumn = $this->requestNumberColumn();
        // $dateColumn = $this->requestDateColumn();
        // $noteColumn = $this->requestNoteColumn();
        // $statusColumn = $this->statusColumn();
        // $quantityColumn = $this->quantityColumn();

        // $stockInRequests = StockInRequest::query()
        //     ->with(['supplier', 'warehouse', 'items.item'])
        //     ->when($request->search, function ($query, $search) use ($numberColumn, $noteColumn) {
        //         $query->where(function ($query) use ($search, $numberColumn, $noteColumn) {
        //             if ($numberColumn) {
        //                 $query->orWhere($numberColumn, 'like', "%{$search}%");
        //             }

        //             if ($noteColumn) {
        //                 $query->orWhere($noteColumn, 'like', "%{$search}%");
        //             }

        //             $query->orWhereHas('supplier', function ($supplierQuery) use ($search) {
        //                 $supplierQuery->where('name', 'like', "%{$search}%");
        //             });

        //             $query->orWhereHas('warehouse', function ($warehouseQuery) use ($search) {
        //                 $warehouseQuery->where('name', 'like', "%{$search}%");
        //             });
        //         });
        //     })
        //     ->when($request->status && $statusColumn, function ($query) use ($request, $statusColumn) {
        //         $query->where($statusColumn, $request->status);
        //     })
        //     ->latest()
        //     ->paginate(10)
        //     ->withQueryString();

        return view('admin.stock-in-requests.new-index');
    }

    public function create()
    {
        $suppliers = $this->supplierRepo->getAll();
        $warehouses = $this->warehouseRepo->getAllActive();
        $items = $this->itemRepo->getAllActive();

        return view('admin.stock-in-requests.create', compact(
            'suppliers',
            'warehouses',
            'items'
        ));
    }

    private function validateRequest(Request $request) :array
    {
        $itemCount = count((array) $request->input('item_id'));
        return $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'request_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'item_id' => ['required', 'array', 'size:' . $itemCount],
            'item_id.*' => ['required', 'exists:items,id', 'distinct'],
            'quantity' => ['required', 'array', 'size:' . $itemCount],
            'quantity.*' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'array', 'size:' . $itemCount],
            'price.*' => ['required', 'numeric', 'min:1'],
            'item_note' => ['nullable', 'array', 'size:' . $itemCount],
            'item_note.*' => ['nullable', 'string'],
        ], [
            'item_id.required' => 'Paling tidak satu item dibutuhkan',
            'item_id.min' => 'Paling tidak satu item dibutuhkan',
            'item_id.*.required' => 'Paling tidak satu item dibutuhkan',
            'item_id.*.distinct' => 'Tidak bisa diisi dengan Item yang Sama',
            'quantity.required' => 'Paling tidak satu item dibutuhkan',
            'quantity.min' => 'Paling tidak satu item dibutuhkan',
            'quantity.*.required' => 'Paling tidak satu item dibutuhkan',
            'price.min' => 'Paling tidak satu item dibutuhkan',
            'price.*.required' => 'Paling tidak satu item dibutuhkan'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        try {
            $stockInRequest = $this->stockInRequestService->store($validated);
            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('success', 'Transaksi barang masuk berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data karena masalah sistem. Silakan coba lagi.');
        }
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
        ));
    }

    public function edit(StockInRequest $stockInRequest)
    {
        // cek status
        if ($stockInRequest->status == 'approved' || $stockInRequest->status == 'rejected') {
            return redirect()->route('admin.stock-in-requests.index')->with('error', 'Request sudah di Approve / Direject');
        }

        $stockInRequest->load(['items.item']);

        $suppliers = $this->supplierRepo->getAll();
        $warehouses = $this->warehouseRepo->getAllActive();
        $items = $this->itemRepo->getAllActive();

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
        // cek status
        if ($stockInRequest->status == 'approved' || $stockInRequest->status == 'rejected') {
            return redirect()->route('admin.stock-in-requests.index')->with('error', 'Request sudah di Approve / Direject');
        }
        $validated = $this->validateRequest($request);
        try {
            $stockInRequest = $this->stockInRequestService->update($stockInRequest, $validated);

            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('success', 'Transaksi barang masuk berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengedit data karena masalah sistem. Silakan coba lagi.');
        }
    }

    public function approve(StockInRequest $stockInRequest)
    {
        // cek status
        if ($stockInRequest->status == 'approved' || $stockInRequest->status == 'rejected') {
            return redirect()->route('admin.stock-in-requests.index')->with('error', 'Request sudah di Approve / Direject');
        }

        try {
            $updated = $this->stockInRequestService->approve($stockInRequest);
            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('success', 'Request barang masuk berhasil disetujui dan stok berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('error', 'Request barang masuk gagal disetujui.');
        }
    }

    public function reject(Request $request, StockInRequest $stockInRequest)
    {
        // cek status
        if ($stockInRequest->status == 'approved' || $stockInRequest->status == 'rejected') {
            return redirect()->route('admin.stock-in-requests.index')->with('error', 'Request sudah di Approve / Direject');
        }

        try {
            $validated = $request->validate([
                'rejected_reason' => ['nullable', 'string', 'max:500'],
            ]);
            $updated = $this->stockInRequestService->reject($stockInRequest, $validated);
            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('success', 'Transaksi barang masuk berhasil ditolak.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('error', 'Transaksi barang masuk gagal ditolak.');
        }
    }

    public function destroy(StockInRequest $stockInRequest)
    {
        // cek status
        if ($stockInRequest->status == 'approved' || $stockInRequest->status == 'rejected') {
            return redirect()->route('admin.stock-in-requests.index')->with('error', 'Request sudah di Approve / Direject');
        }

        try {
            $deleted = $this->stockInRequestService->destroy($stockInRequest);

            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('success', 'Transaksi barang masuk berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.stock-in-requests.index')
                ->with('error', 'Transaksi barang masuk gagal dihapus.');
        }
    }
}
