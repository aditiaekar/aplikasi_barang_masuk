@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan awal aplikasi pencatatan barang masuk')

@push('styles')
<style>
    .dashboard-hero {
        padding: 1.6rem;
        border-radius: 24px;
        background:
            linear-gradient(135deg, rgba(159, 18, 57, 0.95), rgba(190, 18, 60, 0.88)),
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.24), transparent 32%);
        color: #fff;
        box-shadow: 0 20px 45px rgba(159, 18, 57, 0.22);
        overflow: hidden;
        position: relative;
    }

    .dashboard-hero::after {
        content: "";
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.13);
        position: absolute;
        right: -55px;
        bottom: -75px;
    }

    .dashboard-hero h3 {
        position: relative;
        z-index: 1;
        font-size: 1.35rem;
        font-weight: 800;
        margin-bottom: 0.45rem;
    }

    .dashboard-hero p {
        position: relative;
        z-index: 1;
        max-width: 720px;
        margin: 0;
        color: rgba(255, 255, 255, 0.88);
        line-height: 1.65;
        font-size: 0.95rem;
    }

    .stat-card,
    .quick-card {
        height: 100%;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(229, 231, 235, 0.85);
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.055);
    }

    .stat-card {
        padding: 1.25rem;
        transition: all 0.22s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 22px 44px rgba(15, 23, 42, 0.08);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: #fff1f2;
        color: #9f1239;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        flex-shrink: 0;
    }

    .stat-icon i {
        font-size: 1.45rem;
    }

    .stat-card span {
        display: block;
        color: #6b7280;
        font-size: 0.86rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
    }

    .stat-card h4 {
        margin: 0;
        color: #1f2937;
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .quick-card {
        padding: 1.35rem;
    }

    .quick-card h5 {
        color: #1f2937;
        font-weight: 800;
        margin-bottom: 0.35rem;
        font-size: 1.05rem;
    }

    .quick-card > p {
        color: #6b7280;
        margin-bottom: 1.1rem;
        line-height: 1.6;
        font-size: 0.92rem;
    }

    .quick-action {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 0.9rem 0.95rem;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        text-decoration: none;
        color: #374151;
        font-weight: 700;
        transition: all 0.2s ease;
        background: #fff;
        min-width: 0;
    }

    .quick-action:not(:last-child) {
        margin-bottom: 0.75rem;
    }

    .quick-action i {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 13px;
        background: #fff1f2;
        color: #9f1239;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .quick-action span {
        min-width: 0;
        line-height: 1.35;
    }

    .quick-action strong {
        color: #1f2937;
        font-size: 0.95rem;
    }

    .quick-action small {
        font-weight: 500;
        line-height: 1.5;
        word-break: break-word;
    }

    a.quick-action:hover {
        border-color: rgba(159, 18, 57, 0.28);
        background: #fff7f8;
        color: #9f1239;
        transform: translateX(3px);
    }

    .dashboard-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-top: 1rem;
    }

    .dashboard-actions .btn {
        border-radius: 14px;
        padding: 0.68rem 1rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
    }

    .activity-empty {
        padding: 2rem 1rem;
        text-align: center;
        color: #6b7280;
        border: 1px dashed #e5e7eb;
        border-radius: 18px;
        background: #fafafa;
    }

    .activity-empty i {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        background: #f3f4f6;
        color: #9ca3af;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        margin-bottom: 0.8rem;
    }

    @media (max-width: 991.98px) {
        .dashboard-hero {
            padding: 1.35rem;
            border-radius: 22px;
        }

        .dashboard-hero h3 {
            font-size: 1.25rem;
        }

        .stat-card,
        .quick-card {
            border-radius: 20px;
        }
    }

    @media (max-width: 767.98px) {
        .dashboard-hero {
            padding: 1.2rem;
            border-radius: 20px;
        }

        .dashboard-hero::after {
            width: 135px;
            height: 135px;
            right: -55px;
            bottom: -55px;
        }

        .dashboard-hero h3 {
            font-size: 1.1rem;
        }

        .dashboard-hero p {
            font-size: 0.88rem;
            line-height: 1.6;
        }

        .stat-card {
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.9rem;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            margin-bottom: 0;
            border-radius: 14px;
        }

        .stat-card span {
            font-size: 0.8rem;
            margin-bottom: 0.2rem;
        }

        .stat-card h4 {
            font-size: 1.35rem;
        }

        .quick-card {
            padding: 1rem;
        }

        .quick-action {
            padding: 0.85rem;
            border-radius: 15px;
            gap: 0.75rem;
        }

        .quick-action i {
            width: 36px;
            height: 36px;
            min-width: 36px;
            font-size: 1.15rem;
        }

        .dashboard-actions {
            flex-direction: column;
        }

        .dashboard-actions .btn {
            width: 100%;
        }
    }

    @media (max-width: 575.98px) {
        .dashboard-hero {
            margin-left: -0.15rem;
            margin-right: -0.15rem;
        }

        .quick-action strong {
            font-size: 0.9rem;
        }

        .quick-action small {
            font-size: 0.78rem;
        }

        .activity-empty {
            padding: 1.5rem 0.85rem;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-hero mb-4">
    <h3>Selamat Datang, {{ Auth::user()->name ?? 'Admin' }}</h3>
    <p>
        Dashboard ini menampilkan ringkasan data terbaru dari aplikasi pencatatan barang masuk,
        stok barang, dan riwayat mutasi stok.
    </p>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bx bx-box"></i>
            </div>
            <div>
                <span>Total Barang</span>
                <h4>{{ number_format($stats['total_barang'] ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bx bx-log-in-circle"></i>
            </div>
            <div>
                <span>Barang Masuk Hari Ini</span>
                <h4>{{ number_format($stats['barang_masuk_hari_ini'] ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bx bx-store"></i>
            </div>
            <div>
                <span>Total Supplier</span>
                <h4>{{ number_format($stats['total_supplier'] ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bx bx-transfer-alt"></i>
            </div>
            <div>
                <span>Mutasi Bulan Ini</span>
                <h4>{{ number_format($stats['laporan_bulan_ini'] ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 align-items-stretch">
    <div class="col-xl-5 col-lg-5">
        <div class="quick-card">
            <h5>Ringkasan Barang Masuk</h5>
            <p>
                Menampilkan status transaksi barang masuk dan kondisi stok berdasarkan data terbaru.
            </p>

            <div class="quick-action">
                <i class="bx bx-time-five"></i>
                <span>
                    <strong>{{ number_format($stats['transaksi_pending'] ?? 0, 0, ',', '.') }}</strong>
                    <br>
                    <small class="text-muted">Transaksi menunggu approval</small>
                </span>
            </div>

            <div class="quick-action">
                <i class="bx bx-check-circle"></i>
                <span>
                    <strong>{{ number_format($stats['transaksi_approved'] ?? 0, 0, ',', '.') }}</strong>
                    <br>
                    <small class="text-muted">Transaksi barang masuk disetujui</small>
                </span>
            </div>

            <div class="quick-action">
                <i class="bx bx-x-circle"></i>
                <span>
                    <strong>{{ number_format($stats['transaksi_rejected'] ?? 0, 0, ',', '.') }}</strong>
                    <br>
                    <small class="text-muted">Transaksi barang masuk ditolak</small>
                </span>
            </div>

            <div class="quick-action">
                <i class="bx bx-error-circle"></i>
                <span>
                    <strong>{{ number_format($stats['stok_rendah'] ?? 0, 0, ',', '.') }}</strong>
                    <br>
                    <small class="text-muted">Barang dengan stok rendah</small>
                </span>
            </div>

            <div class="dashboard-actions">
                <a href="{{ route('admin.stock-in-requests.index') }}" class="btn btn-danger">
                    <i class="bx bx-list-ul"></i>
                    Lihat Transaksi
                </a>

                <a href="{{ route('admin.stocks.index') }}" class="btn btn-light border">
                    <i class="bx bx-package"></i>
                    Lihat Stok Barang
                </a>
            </div>
        </div>
    </div>

    <div class="col-xl-7 col-lg-7">
        <div class="quick-card">
            <h5>Aktivitas Terbaru</h5>
            <p>Menampilkan riwayat mutasi stok terbaru dari transaksi barang masuk.</p>

            @forelse (($latestMutations ?? collect()) as $mutation)
                @php
                    $isIn = $mutation->mutation_type === 'in';
                    $mutationLabel = $isIn ? 'Barang Masuk' : 'Barang Keluar';
                    $mutationDate = $mutation->mutation_date ?? $mutation->created_at;
                @endphp

                <div class="quick-action">
                    <i class="{{ $isIn ? 'bx bx-log-in-circle' : 'bx bx-log-out-circle' }}"></i>

                    <span>
                        <strong>{{ $mutationLabel }}</strong>
                        <br>
                        <small class="text-muted">
                            {{ $mutation->item_code ?? '-' }} - {{ $mutation->item_name ?? '-' }}
                            | {{ number_format($mutation->quantity ?? 0, 0, ',', '.') }}
                            | {{ $mutation->warehouse_name ?? '-' }}
                        </small>
                        <br>
                        <small class="text-muted">
                            {{ $mutationDate ? \Carbon\Carbon::parse($mutationDate)->format('d-m-Y H:i') : '-' }}
                            oleh {{ $mutation->user_name ?? 'Admin' }}
                        </small>
                    </span>
                </div>
            @empty
                <div class="activity-empty">
                    <i class="bx bx-time-five"></i>
                    <h6 class="fw-bold mb-1">Belum Ada Aktivitas</h6>
                    <div>Data aktivitas akan tersedia setelah transaksi barang masuk disetujui.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection