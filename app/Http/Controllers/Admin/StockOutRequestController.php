<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockOutRequest;
use App\Repositories\StockOutRequestRepository;
use App\Repositories\WarehouseRepository;
use App\Service\StockOutRequestService;
use Dotenv\Parser\Value;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Item;

class StockOutRequestController extends Controller
{
    public function __construct(
        protected WarehouseRepository $warehouseRepo,
        protected StockOutRequestRepository $stockOutRequestRepo,
        protected StockOutRequestService $stockOutRequestService,
    ) {}

    public function data(Request $request)
    {
        $query = StockOutRequest::query()
            ->with(['warehouse', 'items.item'])
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
            $query->where(function ($q) use ($keyword,$searchDate) {
                $q->where('request_number', 'like', "%{$keyword}%")
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
                return $row->items->sum($row->total_qty ?? 0);
            })
            ->addColumn('status', function ($row) {
                return '<span class="badge-status badge-' . e($row->status) . '">' . e(ucfirst($row->status)) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $showUrl = route('admin.stock-out-requests.show', $row->id);

                $action = '
                <a href="' . $showUrl . '"
                   class="action-btn"
                   title="Detail">
                    <i class="bx bx-show"></i>
                </a>
            ';

                if ($row->status === 'pending') {
                    $approveUrl = route('admin.stock-out-requests.approve', $row->id);
                    $rejectUrl = route('admin.stock-out-requests.reject', $row->id);
                    $editUrl = route('admin.stock-out-requests.edit', $row->id);
                    $deleteUrl = route('admin.stock-out-requests.destroy', $row->id);

                    $csrf = csrf_field();
                    $methodDelete = method_field('DELETE');

                    $action .= '
                    <form action="' . $approveUrl . '"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm(\'Yakin ingin menyetujui transaksi barang keluar ini? Stok akan otomatis berkurang.\')">
                        ' . $csrf . '

                        <button type="submit" class="action-btn" title="Approve">
                            <i class="bx bx-check"></i>
                        </button>
                    </form>

                    <form action="' . $rejectUrl . '"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm(\'Yakin ingin menolak transaksi barang keluar ini?\')">
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
                          onsubmit="return confirm(\'Yakin ingin menghapus transaksi barang keluar ini?\')">
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
        $warehouseId = $request->integer('warehouse_id');

        $query = Item::query()
            ->join('item_stocks', 'item_stocks.item_id', '=', 'items.id')
            ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
            ->leftJoin('units', 'units.id', '=', 'items.unit_id')
            ->where('items.is_active', true)
            ->where('item_stocks.quantity', '>', 0)
            ->select([
                'items.id',
                'items.item_code',
                'items.name',
                'categories.name as category_name',
                'units.name as unit_name',
                'item_stocks.quantity as available_stock',
            ]);

        if ($warehouseId) {
            $query->where('item_stocks.warehouse_id', $warehouseId);
        } else {
            $query->whereRaw('1 = 0');
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->make(true);
    }

    public function index(Request $request)
    {
        // $stockOutRequests = StockOutRequest::query()
        //     ->with(['supplier', 'warehouse', 'items.item'])
        //     ->when($request->search, function ($query, $search) {
        //         $query->where(function ($query) use ($search) {
        //             $query->where('request_number', 'like', "%{$search}%")
        //                 ->orWhere('note', 'like', "%{$search}%")
        //                 ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$search}%"))
        //                 ->orWhereHas('warehouse', fn($q) => $q->where('name', 'like', "%{$search}%"));
        //         });
        //     })
        //     ->when($request->status, fn($query, $status) => $query->where('status', $status))
        //     ->latest()
        //     ->paginate(10)
        //     ->withQueryString();

        // $numberColumn = 'request_number';
        // $dateColumn = 'request_date';
        // $noteColumn = 'note';
        // $statusColumn = 'status';
        // $quantityColumn = 'quantity';

        return view('admin.stock-out-requests.index');
    }

    public function create()
    {
        $warehouseId = old('warehouse_id');

        return view('admin.stock-out-requests.create', [
            'warehouses' => $this->warehouseRepo->getAllActive(),
            'items' => $warehouseId
                ? $this->stockOutRequestRepo->getAvailableItemsByWarehouse((int) $warehouseId)
                : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $this->stockOutRequestService->store($request);

        return redirect()->route('admin.stock-out-requests.index')
            ->with('success', 'Transaksi barang keluar berhasil dibuat.');
    }

    public function show(StockOutRequest $stockOutRequest)
    {
        $stockOutRequest->load(['warehouse', 'requestedBy', 'items.item', 'items.unit']);

        return view('admin.stock-out-requests.show', compact('stockOutRequest'));
    }

    public function edit(StockOutRequest $stockOutRequest)
    {
        $stockOutRequest->load(['items.item']);
        $warehouseId = (int) old('warehouse_id', $stockOutRequest->warehouse_id);

        return view('admin.stock-out-requests.edit', [
            'stockOutRequest' => $stockOutRequest,
            'warehouses' => $this->warehouseRepo->getAllActive(),
            'items' => $this->stockOutRequestRepo->getAvailableItemsByWarehouse($warehouseId),
        ]);
    }

    public function update(Request $request, StockOutRequest $stockOutRequest)
    {
        $this->stockOutRequestService->update($stockOutRequest, $request);

        return redirect()->route('admin.stock-out-requests.index')
            ->with('success', 'Transaksi barang keluar berhasil diperbarui.');
    }

    public function destroy(StockOutRequest $stockOutRequest)
    {
        $this->stockOutRequestService->destroy($stockOutRequest);

        return redirect()->route('admin.stock-out-requests.index')
            ->with('success', 'Transaksi barang keluar berhasil dihapus.');
    }

    public function warehouseItems(int $warehouse)
    {
        return response()->json($this->stockOutRequestRepo->getAvailableItemsByWarehouse($warehouse));
    }

    public function approve(StockOutRequest $stockOutRequest)
    {
        try {
            $updated = $this->stockOutRequestService->approve($stockOutRequest);

            return redirect()
                ->route('admin.stock-out-requests.index')
                ->with('success', 'Transaksi barang keluar berhasil disetujui dan stok berhasil diperbarui.');
        } catch (ValidationException $e) {
            return redirect()
                ->route('admin.stock-out-requests.index')
                ->with('error', 'Transaksi barang keluar gagal diterima. ' . collect($e->errors())->flatten()->first());
        } catch (\Throwable $th) {
            return redirect()
                ->route('admin.stock-out-requests.index')
                ->with('error', 'Transaksi barang keluar gagal diterima');
        }
    }
    public function reject(StockOutRequest $stockOutRequest, Request $request)
    {
        $validated = $request->validate([
            'rejected_reason' => ['nullable', 'string', 'max:500'],
        ]);
        try {
            $updated = $this->stockOutRequestService->reject($stockOutRequest, $validated);

            return redirect()
                ->route('admin.stock-out-requests.index')
                ->with('success', 'Transaksi barang keluar berhasil ditolak.');
        } catch (ValidationException $e) {
            return redirect()
                ->route('admin.stock-out-requests.index')
                ->with('error', 'Transaksi barang keluar gagal ditolak. ' . collect($e->errors())->flatten()->first());
        } catch (\Throwable $th) {
            return redirect()
                ->route('admin.stock-out-requests.index')
                ->with('error', 'Transaksi barang keluar gagal ditolak.');
        }
    }
}
