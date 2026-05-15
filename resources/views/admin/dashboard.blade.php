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
        font-size: 1.35rem;
        font-weight: 800;
        margin-bottom: 0.45rem;
    }

    .dashboard-hero p {
        max-width: 620px;
        margin: 0;
        color: rgba(255, 255, 255, 0.86);
        line-height: 1.65;
    }

    .stat-card {
        height: 100%;
        padding: 1.25rem;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(229, 231, 235, 0.8);
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.055);
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
    }

    .quick-card {
        padding: 1.35rem;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(229, 231, 235, 0.8);
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.055);
    }

    .quick-card h5 {
        font-weight: 800;
        margin-bottom: 0.35rem;
    }

    .quick-card p {
        color: #6b7280;
        margin-bottom: 1.1rem;
    }

    .quick-action {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.85rem 0.95rem;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        text-decoration: none;
        color: #374151;
        font-weight: 700;
        transition: all 0.2s ease;
    }

    .quick-action:not(:last-child) {
        margin-bottom: 0.75rem;
    }

    .quick-action i {
        width: 38px;
        height: 38px;
        border-radius: 13px;
        background: #fff1f2;
        color: #9f1239;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .quick-action:hover {
        border-color: rgba(159, 18, 57, 0.28);
        background: #fff7f8;
        color: #9f1239;
        transform: translateX(3px);
    }

    .activity-empty {
        padding: 2rem;
        text-align: center;
        color: #6b7280;
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

    @media (max-width: 575.98px) {
        .dashboard-hero {
            padding: 1.25rem;
        }

        .dashboard-hero h3 {
            font-size: 1.15rem;
        }

        .stat-card {
            padding: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-hero mb-4">
    <h3>Selamat Datang, {{ Auth::user()->name ?? 'Admin' }}</h3>
    <p>
        Dashboard ini digunakan untuk memantau ringkasan awal aplikasi pencatatan barang masuk.
        Data statistik akan ditampilkan setelah modul barang, supplier, dan transaksi barang masuk dibuat.
    </p>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bx bx-box"></i>
            </div>
            <span>Total Barang</span>
            <h4>{{ $stats['total_barang'] }}</h4>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bx bx-log-in-circle"></i>
            </div>
            <span>Barang Masuk Hari Ini</span>
            <h4>{{ $stats['barang_masuk_hari_ini'] }}</h4>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bx bx-store"></i>
            </div>
            <span>Total Supplier</span>
            <h4>{{ $stats['total_supplier'] }}</h4>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bx bx-file"></i>
            </div>
            <span>Laporan Bulan Ini</span>
            <h4>{{ $stats['laporan_bulan_ini'] }}</h4>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="quick-card h-100">
            <h5>Akses Cepat</h5>
            <p>Menu ini masih berupa tampilan awal dan akan dihubungkan setelah modul utama dibuat.</p>

            <a href="javascript:void(0)" class="quick-action">
                <i class="bx bx-plus"></i>
                <span>Tambah Barang Masuk</span>
            </a>

            <a href="javascript:void(0)" class="quick-action">
                <i class="bx bx-package"></i>
                <span>Kelola Data Barang</span>
            </a>

            <a href="javascript:void(0)" class="quick-action">
                <i class="bx bx-file-find"></i>
                <span>Lihat Laporan</span>
            </a>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="quick-card h-100">
            <h5>Aktivitas Terbaru</h5>
            <p>Aktivitas transaksi barang masuk akan muncul pada bagian ini.</p>

            <div class="activity-empty">
                <i class="bx bx-time-five"></i>
                <h6 class="fw-bold mb-1">Belum Ada Aktivitas</h6>
                <div>Data aktivitas akan tersedia setelah transaksi barang masuk dibuat.</div>
            </div>
        </div>
    </div>
</div>
@endsection