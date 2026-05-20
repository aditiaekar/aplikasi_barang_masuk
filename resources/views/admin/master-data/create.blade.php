@extends('layouts.admin')

@section('title', 'Tambah ' . $config['title'])
@section('page-title', 'Tambah ' . $config['title'])
@section('page-subtitle', $config['subtitle'])

@include('admin.master-data.partials.form-style')

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <h5>Form Tambah {{ $config['title'] }}</h5>
        <p>Lengkapi data dengan benar.</p>
    </div>

    <div class="form-card-body">
        <form action="{{ route('admin.master-data.store', $type) }}" method="POST">
            @csrf

            @include('admin.master-data.partials.form-fields', [
                'fields' => $fields,
                'item' => null,
            ])

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.master-data.index', $type) }}" class="btn btn-light rounded-4 px-4">
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