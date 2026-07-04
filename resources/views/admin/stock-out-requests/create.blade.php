@extends('layouts.admin')

@section('title', 'Tambah Barang Keluar')
@section('page-title', 'Tambah Barang Keluar')
@section('page-subtitle', 'Buat transaksi pengajuan barang keluar.')

@include('admin.stock-in-requests.partials.form-style')

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <h5>Form Barang Keluar</h5>
        <p>Lengkapi data transaksi barang keluar.</p>
    </div>

    <div class="form-card-body">
        <form action="{{ route('admin.stock-out-requests.store') }}" method="POST">
            @csrf

            @include('admin.stock-out-requests.partials.form-fields', [
                'stockOutRequest' => null,
                'requestDateValue' => now()->format('Y-m-d'),
                'noteValue' => '',
                'detailRows' => [],
            ])

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.stock-out-requests.index') }}" class="btn btn-light rounded-4 px-4">
                    Kembali
                </a>

                <button type="submit" class="btn btn-red px-4">
                    <i class="bx bx-save me-1"></i>
                    Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
