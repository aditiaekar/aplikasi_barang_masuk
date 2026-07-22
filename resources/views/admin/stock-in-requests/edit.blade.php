@extends('layouts.admin')

@section('title', 'Edit Barang Masuk')
@section('page-title', 'Edit Barang Masuk')
@section('page-subtitle', 'Perbarui transaksi pengajuan barang masuk.')

@include('admin.stock-in-requests.partials.form-style')

@php
    $requestDateValue = $dateColumn && $stockInRequest->{$dateColumn}
        ? \Carbon\Carbon::parse($stockInRequest->{$dateColumn})->format('Y-m-d')
        : now()->format('Y-m-d');

    $noteValue = $noteColumn ? $stockInRequest->{$noteColumn} : '';

    $detailRows = $stockInRequest->items->map(function ($detail) use ($quantityColumn, $itemNoteColumn) {
        return [
            'item_id' => $detail->item_id,
            'quantity' => $detail->{$quantityColumn},
            'price' => $detail->price,
            'note' => $itemNoteColumn ? $detail->{$itemNoteColumn} : '',
        ];
    })->toArray();
@endphp

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <h5>Form Edit Barang Masuk</h5>
        <p>Perbarui data transaksi barang masuk.</p>
    </div>

    <div class="form-card-body">
        <form action="{{ route('admin.stock-in-requests.update', $stockInRequest->id) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.stock-in-requests.partials.form-fields', [
                'stockInRequest' => $stockInRequest,
                'requestDateValue' => $requestDateValue,
                'noteValue' => $noteValue,
                'detailRows' => $detailRows,
            ])

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.stock-in-requests.index') }}" class="btn btn-light rounded-4 px-4">
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
