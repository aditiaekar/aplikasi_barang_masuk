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
            <form method="GET" action="{{ route('admin.stock-mutations.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="warehouse_id" class="form-label">Filter Gudang</label>
                    <select name="warehouse_id" id="warehouse_id" class="form-select">
                        <option value="">Semua Gudang</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ (string) $warehouseId === (string) $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="item" class="form-label">Cari Barang</label>
                    <input type="text"
                           name="item"
                           id="item"
                           class="form-control"
                           value="{{ $itemKeyword }}"
                           placeholder="Nama atau kode barang">
                </div>

                <div class="col-md-4">
                    <button type="submit" class="btn btn-danger">
                        Filter
                    </button>

                    <a href="{{ route('admin.stock-mutations.index') }}" class="btn btn-light border">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                    <tbody>
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
                                        <span class="badge bg-secondary">{{ ucfirst($mutation->mutation_type ?? '-') }}</span>
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
                    </tbody>
                </table>
            </div>
        </div>

        @if ($mutations->hasPages())
            <div class="card-footer bg-white">
                {{ $mutations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection