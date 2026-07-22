@extends('layouts.admin')

@section('title', 'Detail Transaksi Barang Keluar')

@section('content')
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Transaksi Barang Keluar</h4>
                <p class="text-muted mb-0">
                    Informasi detail transaksi dan daftar barang keluar.
                </p>
            </div>

            <a href="{{ route('admin.stock-out-requests.index') }}" class="btn btn-light border">
                Kembali
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Nomor Request</small>
                        <strong>{{ $stockOutRequest->request_number }}</strong>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block">Tanggal Request</small>
                        <strong>{{ \Carbon\Carbon::parse($stockOutRequest->request_date)->format('d-m-Y') }}</strong>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block">Status</small>

                        @if ($stockOutRequest->status === 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif($stockOutRequest->status === 'approved')
                            <span class="badge bg-success">Approved</span>
                        @elseif($stockOutRequest->status === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($stockOutRequest->status) }}</span>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block">Gudang</small>
                        <strong>{{ $stockOutRequest->warehouse->name ?? '-' }}</strong>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block">Dibuat Oleh</small>
                        <strong>{{ $stockOutRequest->requestedBy->name ?? '-' }}</strong>
                    </div>


                    <div class="col-md-4">
                        <small class="text-muted d-block">Pengirim</small>
                        <strong>{{ $stockOutRequest->sender_name ?? '-' }}</strong>
                    </div>


                    <div class="col-md-4">
                        <small class="text-muted d-block">Penerima</small>
                        <strong>{{ $stockOutRequest->recipient_name ?? '-' }}</strong>
                    </div>


                    <div class="col-md-4">
                        <small class="text-muted d-block">Kode Pos Tujuan</small>
                        <strong>{{ $stockOutRequest->recipient_postal_code ?? '-' }}</strong>
                    </div>


                    <div class="col-md-4">
                        <small class="text-muted d-block">Nomor Telp. Tujuan</small>
                        <strong>{{ $stockOutRequest->recipient_phone ?? '-' }}</strong>
                    </div>

                    <div class="col-md-4">
                    <small class="text-muted d-block">Nomor EMS</small>
                        <strong>{{ $stockOutRequest->ems_number ?? '-' }}</strong>
                    </div>

                    <div class="col-md-12">
                        <small class="text-muted d-block">Catatan</small>
                        <span>{{ $stockOutRequest->note ?? '-' }}</span>
                    </div>

                    <div class="col-md-12">
                        <small class="text-muted d-block">Alamat Tujuan</small>
                        <span>{{ $stockOutRequest->recipient_address ?? '-' }}</span>
                    </div>

                    @if ($stockOutRequest->status === 'rejected')
                        <div class="col-md-12">
                            <small class="text-muted d-block">Alasan Penolakan</small>
                            <span class="text-danger">{{ $stockOutRequest->rejected_reason ?? '-' }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0">Daftar Barang</h6>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Barang</th>
                                <th>Satuan</th>
                                <th class="text-end">Jumlah</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockOutRequest->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $item->item->name ?? '-' }}</strong>
                                        <div class="small text-muted">
                                            {{ $item->item->item_code ?? '-' }}
                                        </div>
                                    </td>
                                    <td>{{ $item->unit->name ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                                    <td>{{ $item->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada barang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if ($stockOutRequest->status === 'pending')
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Approval Transaksi</h6>

                    <div class="d-flex flex-wrap gap-2">
                        <form action="{{ route('admin.stock-out-requests.approve', $stockOutRequest->id) }}" method="POST"
                            onsubmit="return confirm('Yakin ingin menyetujui transaksi barang keluar ini? Stok akan otomatis berkurang.')">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                Approve
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('admin.stock-out-requests.reject', $stockOutRequest->id) }}" method="POST"
                class="modal-content border-0 shadow">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="rejectModalLabel">Tolak Transaksi Barang Keluar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <label for="rejected_reason" class="form-label">Alasan Penolakan</label>
                    <textarea name="rejected_reason" id="rejected_reason" rows="4"
                        class="form-control @error('rejected_reason') is-invalid @enderror" placeholder="Masukkan alasan penolakan">{{ old('rejected_reason') }}</textarea>

                    @error('rejected_reason')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
