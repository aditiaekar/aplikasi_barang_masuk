@extends('layouts.admin')

@section('title', 'Tambah Barang')
@section('page-title', 'Tambah Barang')
@section('page-subtitle', 'Tambahkan data barang baru dan stok awal.')

@include('admin.items.partials.form-style')

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <h5>Form Tambah Barang</h5>
        <p>Lengkapi data barang sesuai kebutuhan.</p>
    </div>

    <div class="form-card-body">
        <form action="{{ route('admin.items.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @include('admin.items.partials.form-fields', [
                'item' => null,
                'stocks' => [],
            ])

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.items.index') }}" class="btn btn-light rounded-4 px-4">
                    Kembali
                </a>

                <button type="submit" class="btn btn-red px-4">
                    <i class="bx bx-save me-1"></i>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
