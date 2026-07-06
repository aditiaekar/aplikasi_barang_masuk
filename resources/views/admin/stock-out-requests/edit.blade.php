@extends('layouts.admin')

@section('title', 'Edit Barang Keluar')
@section('page-title', 'Edit Barang Keluar')
@section('page-subtitle', 'Perbarui transaksi pengajuan barang keluar.')

@include('admin.stock-out-requests.partials.form-style')

@php
    $requestDateValue = $stockOutRequest->request_date
        ? \Carbon\Carbon::parse($stockOutRequest->request_date)->format('Y-m-d')
        : now()->format('Y-m-d');

    $noteValue = $stockOutRequest->note;

    $availableItemsById = $items->keyBy('id');

    $detailRows = $stockOutRequest->items->map(function ($detail) use ($availableItemsById) {
        $availableItem = $availableItemsById->get($detail->item_id);

        return [
            'item_id' => $detail->item_id,
            'item_code' => $detail->item?->item_code,
            'name' => $detail->item?->name,
            'available_stock' => $availableItem?->available_stock ?? $detail->quantity,
            'quantity' => $detail->quantity,
            'note' => $detail->note,
        ];
    })->toArray();
@endphp

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <h5>Form Edit Barang Keluar</h5>
        <p>Perbarui data transaksi barang keluar.</p>
    </div>

    <div class="form-card-body">
        <form action="{{ route('admin.stock-out-requests.update', $stockOutRequest->id) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.stock-out-requests.partials.form-fields', [
                'stockOutRequest' => $stockOutRequest,
                'requestDateValue' => $requestDateValue,
                'noteValue' => $noteValue,
                'detailRows' => $detailRows,
            ])

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.stock-out-requests.index') }}" class="btn btn-light rounded-4 px-4">
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
