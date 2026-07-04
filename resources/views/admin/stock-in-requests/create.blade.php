@extends('layouts.admin')

@section('title', 'Tambah Barang Masuk')
@section('page-title', 'Tambah Barang Masuk')
@section('page-subtitle', 'Buat transaksi pengajuan barang masuk.')

@include('admin.stock-in-requests.partials.form-style')

@section('content')
    <div class="form-card">
        <div class="form-card-header">
            <h5>Form Barang Masuk</h5>
            <p>Lengkapi data transaksi barang masuk.</p>
        </div>
        @if ($errors->any())
            @if ($errors->has('item_id') || $errors->has('quantity') || $errors->has('item_id.*') || $errors->has('quantity.*'))
                <div class="alert alert-danger">
                    {{ $errors->first('item_id') ?: ($errors->first('quantity') ?: $errors->first('item_id.*')) }}
                </div>
            @else
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif

        <div class="form-card-body">
            <form action="{{ route('admin.stock-in-requests.store') }}" method="POST">
                @csrf

                @include('admin.stock-in-requests.partials.form-fields', [
                    'stockInRequest' => null,
                    'requestDateValue' => now()->format('Y-m-d'),
                    'noteValue' => '',
                    'detailRows' => [],
                ])

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.stock-in-requests.index') }}" class="btn btn-light rounded-4 px-4">
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
