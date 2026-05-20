@extends('layouts.admin')

@section('title', 'Edit ' . $config['title'])
@section('page-title', 'Edit ' . $config['title'])
@section('page-subtitle', $config['subtitle'])

@include('admin.master-data.partials.form-style')

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <h5>Form Edit {{ $config['title'] }}</h5>
        <p>Perbarui data sesuai kebutuhan.</p>
    </div>

    <div class="form-card-body">
        <form action="{{ route('admin.master-data.update', [$type, $item->id]) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.master-data.partials.form-fields', [
                'fields' => $fields,
                'item' => $item,
            ])

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.master-data.index', $type) }}" class="btn btn-light rounded-4 px-4">
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