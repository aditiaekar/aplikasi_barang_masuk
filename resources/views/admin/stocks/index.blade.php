@extends('layouts.admin')

@section('title', 'Stok Barang')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="fw-bold mb-1">Stok Barang</h4>
                <p class="text-muted mb-0">
                    Menampilkan jumlah stok barang berdasarkan gudang.
                </p>
            </div>

            <a href="{{ route('admin.stock-mutations.index') }}" class="btn btn-dark">
                Riwayat Mutasi
            </a>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <label for="warehouse_id" class="form-label">Filter Gudang</label>
                    <div class="col-md-4 my-auto">
                        <select name="warehouse_id" id="filterWarehouse" class="form-select">
                            <option value="">Semua Gudang</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 my-auto">
                        <button type="button" id="btnFilter" class="btn btn-danger">
                            Filter
                        </button>

                        <button type="button" id="btnReset" class="btn btn-light border">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="datatable" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Gudang</th>
                                <th>Satuan</th>
                                <th class="text-end">Stok</th>
                                <th class="text-end">Minimum</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        {{-- <tbody>
                        @forelse ($stocks as $stock)
                            @php
                                $isLowStock = $hasMinimumStock && $stock->stock_quantity <= $stock->minimum_stock;
                            @endphp

                            <tr>
                                <td>{{ $stocks->firstItem() + $loop->index }}</td>
                                <td>{{ $stock->item_code ?? '-' }}</td>
                                <td>
                                    <strong>{{ $stock->item_name ?? '-' }}</strong>
                                </td>
                                <td>{{ $stock->warehouse_name ?? '-' }}</td>
                                <td>{{ $stock->unit_name ?? '-' }}</td>
                                <td class="text-end fw-bold">
                                    {{ number_format($stock->stock_quantity, 0, ',', '.') }}
                                </td>

                                @if ($hasMinimumStock)
                                    <td class="text-end">
                                        {{ number_format($stock->minimum_stock, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @if ($isLowStock)
                                            <span class="badge bg-danger">Stok Rendah</span>
                                        @else
                                            <span class="badge bg-success">Aman</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $hasMinimumStock ? 8 : 6 }}" class="text-center text-muted py-4">
                                    Belum ada data stok barang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody> --}}
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,

                searching: false,
                lengthChange: false,
                pageLength: 10,

                stateSaveParams: function(settings, data) {
                    data.filterWarehouse = $('#filterWarehouse').val();
                },

                stateLoadParams: function(settings, data) {
                    $('#filterWarehouse').val(data.filterWarehouse || '');
                },

                ajax: {
                    url: "{{ route('admin.stocks.data') }}",
                    data: function(d) {
                        d.warehouse = $('#filterWarehouse').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'item_code',
                        name: 'items.item_code',
                    },
                    {
                        data: 'item_name',
                        name: 'items.name'
                    },
                    {
                        data: 'warehouse_name',
                        name: 'warehouses.name'
                    },
                    {
                        data: 'unit_name',
                        name: 'units.name'
                    },
                    {
                        data: 'total_stock',
                        name: 'total_stock',
                    },
                    {
                        data: 'minimum_stock',
                        name: 'items.minimum_stock',
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                ]
            });

            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });

            $('#btnReset').on('click', function() {
                $('#filterWarehouse').val('');

                table.state.clear();
                table.ajax.reload(null, true);
            });
        });
    </script>
@endpush
