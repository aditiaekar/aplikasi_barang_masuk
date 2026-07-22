@extends('layouts.admin')

@section('title', 'Stock Out Request')
@section('page-title', 'Stock Out Request')
@section('page-subtitle', 'Kelola transaksi pengajuan barang keluar.')

@push('styles')
    <style>
        .transaction-card {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(229, 231, 235, 0.85);
            border-radius: 22px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .transaction-card-header {
            padding: 1.25rem 1.35rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .transaction-card-header h5 {
            margin: 0;
            font-weight: 800;
            color: #1f2937;
        }

        .transaction-card-header p {
            margin: 0.25rem 0 0;
            color: #6b7280;
            font-size: 0.88rem;
        }

        .btn-red {
            background: linear-gradient(135deg, #9f1239, #be123c);
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 0.7rem 1rem;
            font-weight: 700;
            box-shadow: 0 12px 24px rgba(159, 18, 57, 0.2);
        }

        .btn-red:hover {
            color: #fff;
        }

        .filter-box {
            padding: 1rem 1.35rem;
            border-bottom: 1px solid #e5e7eb;
            background: #fff;
        }

        .form-control,
        .form-select {
            border-radius: 14px;
            border-color: #e5e7eb;
            padding: 0.72rem 0.9rem;
            font-size: 0.92rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #be123c;
            box-shadow: 0 0 0 0.2rem rgba(190, 18, 60, 0.12);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f9fafb;
            color: #374151;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.95rem 1rem;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f5f9;
            color: #374151;
        }

        .transaction-code {
            display: inline-flex;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            background: #fff1f2;
            color: #9f1239;
            font-weight: 800;
            font-size: 0.78rem;
        }

        .badge-status {
            border-radius: 999px;
            padding: 0.4rem 0.7rem;
            font-weight: 700;
            font-size: 0.78rem;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-approved {
            background: #dcfce7;
            color: #166534;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #374151;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background: #fff1f2;
            color: #9f1239;
            border-color: rgba(159, 18, 57, 0.25);
        }

        .empty-state {
            padding: 2.5rem 1rem;
            text-align: center;
            color: #6b7280;
        }

        .empty-state i {
            width: 62px;
            height: 62px;
            border-radius: 20px;
            background: #f3f4f6;
            color: #9ca3af;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="transaction-card">
        <div class="transaction-card-header">
            <div>
                <h5>Daftar Barang Keluar</h5>
                <p>Data transaksi pengajuan barang keluar.</p>
            </div>

            <a href="{{ route('admin.stock-out-requests.create') }}" class="btn btn-red">
                <i class="bx bx-plus me-1"></i>
                Tambah Barang Keluar
            </a>
        </div>

        <div class="filter-box">
            <form action="{{ route('admin.stock-out-requests.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-8 col-md-8">
                        <label class="form-label fw-semibold">Pencarian</label>
                        <input type="text" id="filterKeyword" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Cari nomor transaksi, gudang, atau tanggal">
                    </div>

                    <div class="col-lg-2 col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select id="filterStatus" name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected
                            </option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-4 d-grid">
                        <button type="button" id="btnFilter" class="btn btn-dark rounded-4">
                            <i class="bx bx-search me-1"></i>
                            Filter
                        </button>
                    </div>
                </div>
            </form>
            <div class="mt-2">
                <form id="printPdfForm" method="POST" action="{{ route('admin.stock.out.invoice') }}" target="_blank">
                    @csrf
                    <div id="selectedPdfIds"></div>
                    <button type="submit" id="printPdfButton" class="btn btn-dark rounded-4" disabled>
                        <i class="me-1"></i>
                        Print
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success m-3 rounded-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger m-3 rounded-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-responsive">
        <table id="datatable" class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 10px;">#</th>
                    <th style="width: 20px;">No</th>
                    <th>No. Transaksi</th>
                    <th>Tanggal</th>
                    <th>Gudang</th>
                    <th>Total Item</th>
                    <th>Total Qty</th>
                    <th>Status</th>
                    <th class="text-end" style="width: 120px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                {{-- @forelse ($stockOutRequests as $requestItem)
                @php
                $totalQty = $requestItem->items->sum($quantityColumn);
                $status = $statusColumn ? $requestItem->{$statusColumn} : 'pending';
                @endphp

                <tr>
                    <td>{{ $stockOutRequests->firstItem() + $loop->index }}</td>
                    <td>
                        <span class="transaction-code">
                            {{ $numberColumn ? $requestItem->{$numberColumn} : 'BK-' . str_pad($requestItem->id, 3, '0', STR_PAD_LEFT) }}
                        </span>
                    </td>
                    <td>
                        {{ $dateColumn && $requestItem->{$dateColumn}
                                ? \Carbon\Carbon::parse($requestItem->{$dateColumn})->format('d/m/Y')
                                : '-' }}
                    </td>
                    <td>{{ $requestItem->supplier->name ?? '-' }}</td>
                    <td>{{ $requestItem->warehouse->name ?? '-' }}</td>
                    <td>{{ $requestItem->items->count() }}</td>
                    <td><strong>{{ $totalQty }}</strong></td>

                    @if ($statusColumn)
                    <td>
                        <span class="badge-status badge-{{ $status }}">
                            {{ ucfirst($status) }}
                        </span>
                    </td>
                    @endif

                    <td class="text-end">
                        <a href="{{ route('admin.stock-out-requests.show', $requestItem->id) }}"
                            class="action-btn"
                            title="Detail">
                            <i class="bx bx-show"></i>
                        </a>

                        @if ($status === 'pending')
                        <a href="{{ route('admin.stock-out-requests.edit', $requestItem->id) }}"
                            class="action-btn"
                            title="Edit">
                            <i class="bx bx-edit"></i>
                        </a>

                        <form action="{{ route('admin.stock-out-requests.destroy', $requestItem->id) }}"
                            method="POST"
                            class="d-inline"
                            onsubmit="return confirm('Yakin ingin menghapus transaksi barang keluar ini?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="action-btn" title="Hapus">
                                <i class="bx bx-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="bx bx-log-in-circle"></i>
                            <h6 class="fw-bold mb-1">Belum Ada Transaksi</h6>
                            <div>Silakan tambahkan transaksi barang keluar terlebih dahulu.</div>
                        </div>
                    </td>
                </tr>
                @endforelse --}}
            </tbody>
        </table>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            let selectedIds = new Set();

            const table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,

                searching: false,
                lengthChange: false,
                pageLength: 10,

                stateSaveParams: function(settings, data) {
                    data.filterKeyword = $('#filterKeyword').val();
                    data.filterStatus = $('#filterStatus').val();
                },

                stateLoadParams: function(settings, data) {
                    $('#filterKeyword').val(data.filterKeyword || '');
                    $('#filterStatus').val(data.filterStatus || '');
                },

                drawCallback: function() {
                    $('.stock-out-check').each(function() {
                        this.checked = selectedIds.has(this.value);
                    });
                },

                ajax: {
                    url: "{{ route('admin.stock-out-requests.data') }}",
                    data: function(d) {
                        d.keyword = $('#filterKeyword').val();
                        d.status = $('#filterStatus').val();
                    }
                },

                columns: [{
                        data: 'id',
                        name: 'select',
                        orderable: false,
                        searchable: false,
                        render: function(id) {
                            return `<input type="checkbox" class="stock-out-check" value="${id}">`;
                        }
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'request_number',
                        name: 'request_number',
                    },
                    {
                        data: 'request_date',
                        name: 'request_date',
                        render: function(data, type, row) {
                            var dateSplit = data.split('-');
                            return type === "display" || type === "filter" ?
                                dateSplit[1] + '-' + dateSplit[2] + '-' + dateSplit[0] :
                                data;
                        }
                    },
                    {
                        data: 'warehouse.name',
                        name: 'warehouse.name'
                    },
                    {
                        data: 'total_item',
                        name: 'total_item',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'total_qty',
                        name: 'total_qty',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end text-nowrap'
                    }
                ]
            });

            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });

            $('#datatable').on('change', '.stock-out-check', function() {
                if (this.checked) {
                    selectedIds.add(this.value);
                } else {
                    selectedIds.delete(this.value);
                }
                console.log(selectedIds);
                $('#printPdfButton').prop('disabled',selectedIds.size === 0);
            });

            $('#printPdfForm').on('submit', function() {
                const container = $('#selectedPdfIds');
                container.empty();

                selectedIds.forEach(function (id) {
                    container.append(`<input type="hidden" name="ids[]" value="${id}">`);
                });

                return selectedIds.size > 0;
            });
        });
    </script>
@endpush
