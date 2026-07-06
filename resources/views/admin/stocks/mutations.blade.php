@extends('layouts.admin')

@section('title', 'Riwayat Mutasi Stok')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="fw-bold mb-1">Riwayat Mutasi Stok</h4>
                <p class="text-muted mb-0">
                    Menampilkan histori pergerakan stok barang masuk dan keluar.
                </p>
            </div>

            <a href="{{ route('admin.stocks.index') }}" class="btn btn-light border">
                Kembali ke Stok
            </a>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 my-auto">
                        <label for="warehouse_id">Filter Gudang</label>
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
                        <label for="item">Cari Barang</label>
                        <input type="text" name="item" id="filterKeyword" class="form-control"
                            placeholder="Nama atau kode barang">
                    </div>

                    <div class="col-md-4 pt-4">
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
                                <th>Tanggal</th>
                                <th>Barang</th>
                                <th>Gudang</th>
                                <th>Jenis</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-end">Sebelum</th>
                                <th class="text-end">Sesudah</th>
                                <th>Keterangan</th>
                                <th>Dibuat Oleh</th>
                            </tr>
                        </thead>
                        {{-- <tbody>
                            @forelse ($mutations as $mutation)
                                <tr>
                                    <td>{{ $mutations->firstItem() + $loop->index }}</td>
                                    <td>
                                        {{ $mutation->mutation_date ? \Carbon\Carbon::parse($mutation->mutation_date)->format('d-m-Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        <strong>{{ $mutation->item_name ?? '-' }}</strong>
                                        <div class="small text-muted">
                                            {{ $mutation->item_code ?? '-' }}
                                        </div>
                                    </td>
                                    <td>{{ $mutation->warehouse_name ?? '-' }}</td>
                                    <td>
                                        @if ($mutation->mutation_type === 'in')
                                            <span class="badge bg-success">Masuk</span>
                                        @elseif ($mutation->mutation_type === 'out')
                                            <span class="badge bg-danger">Keluar</span>
                                        @else
                                            <span
                                                class="badge bg-secondary">{{ ucfirst($mutation->mutation_type ?? '-') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($mutation->quantity, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($mutation->stock_before, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($mutation->stock_after, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        {{ $mutation->description ?? '-' }}

                                        @if (!empty($mutation->stock_in_number))
                                            <div class="small text-muted">
                                                Ref: {{ $mutation->stock_in_number }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $mutation->user_name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        Belum ada riwayat mutasi stok.
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
                    data.filterKeyword = $('#filterKeyword').val();
                    data.filterWarehouse = $('#filterWarehouse').val();
                },

                stateLoadParams: function(settings, data) {
                    $('#filterKeyword').val(data.filterKeyword || '');
                    $('#filterWarehouse').val(data.filterWarehouse || '');
                },

                ajax: {
                    url: "{{ route('admin.stocks.mutations.data') }}",
                    data: function(d) {
                        d.keyword = $('#filterKeyword').val();
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
                        data: 'mutation_date',
                        name: 'stock_mutations.mutation_date'
                    },
                    {
                        data: 'item_name',
                        name: 'items.name',
                        render: function(data, type, row) {
                            return `
                <strong>${data ?? '-'}</strong>
                <div class="small text-muted">
                    ${row.item_code ?? '-'}
                </div>
            `;
                        }
                    },
                    {
                        data: 'warehouse_name',
                        name: 'warehouses.name'
                    },
                    {
                        data: 'mutation_status',
                        name: 'stock_mutations.mutation_type',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'quantity',
                        name: 'stock_mutations.quantity',
                        className: 'text-end fw-bold'
                    },
                    {
                        data: 'stock_before',
                        name: 'stock_mutations.stock_before',
                        className: 'text-end'
                    },
                    {
                        data: 'stock_after',
                        name: 'stock_mutations.stock_after',
                        className: 'text-end'
                    },
                    {
                        data: 'description',
                        name: 'stock_mutations.description',
                        render: function(data, type, row) {
                            return `
                ${data ?? '-'}
                <div class="small text-muted">
                    Ref: ${row.reference_number ?? '-'}
                </div>
            `;
                        }
                    },
                    {
                        data: 'user_name',
                        name: 'users.name'
                    }
                ]
            });

            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });

            $('#btnReset').on('click', function() {
                $('#filterKeyword').val('');
                $('#filterWarehouse').val('');

                table.state.clear();
                table.ajax.reload(null, true);
            });
        });
    </script>
@endpush
