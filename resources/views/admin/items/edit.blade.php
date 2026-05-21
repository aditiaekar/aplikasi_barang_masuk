@extends('layouts.admin')

@section('title', 'Edit Barang')
@section('page-title', 'Edit Barang')
@section('page-subtitle', 'Perbarui data barang dan stok awal.')

@include('admin.items.partials.form-style')

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <h5>Form Edit Barang</h5>
        <p>Perbarui data barang sesuai kebutuhan.</p>
    </div>

    <div class="form-card-body">
        <form action="{{ route('admin.items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('admin.items.partials.form-fields', [
                'item' => $item,
                'stocks' => $stocks,
            ])

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.items.index') }}" class="btn btn-light rounded-4 px-4">
                    Kembali
                </a>

                <button type="submit" class="btn btn-red px-4">
                    <i class="bx bx-save me-1"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection