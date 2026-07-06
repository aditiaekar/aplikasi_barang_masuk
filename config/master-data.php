<?php

use App\Models\Category;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\Warehouse;

return [
    'categories' => [
        'title' => 'Kategori',
        'subtitle' => 'Kelola data kategori barang.',
        'table' => 'categories',
        'model' => Category::class,
        'icon' => 'bx-category',
        'fields' => [
            ['name' => 'code', 'label' => 'Kode Kategori', 'type' => 'text', 'required' => false, 'unique' => true, 'searchable' => true, 'placeholder' => 'Contoh: KAT-001'],
            ['name' => 'name', 'label' => 'Nama Kategori', 'type' => 'text', 'required' => true, 'searchable' => true, 'placeholder' => 'Masukkan nama kategori'],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'required' => false, 'searchable' => true, 'placeholder' => 'Masukkan deskripsi kategori'],
            ['name' => 'is_active', 'label' => 'Status', 'type' => 'select_status', 'required' => true],
        ],
        'rules' => [
            'code' => ['nullable', 'string', 'max:255', 'unique:categories,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ],
        'columns' => [
            ['data' => 'DT_RowIndex', 'title' => 'No', 'className' => 'text-center'],
            ['data' => 'code', 'title' => 'Kode Kategori', 'className' => 'text-center'],
            ['data' => 'name', 'title' => 'Nama Kategori'],
            ['data' => 'description', 'title' => 'Deskripsi'],
            ['data' => 'is_active', 'title' => 'Status', 'className' => 'text-center'],
            ['data' => 'action', 'title' => 'Aksi', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ],
    ],
    'units' => [
        'title' => 'Satuan',
        'subtitle' => 'Kelola data satuan barang.',
        'table' => 'units',
        'model' => Unit::class,
        'icon' => 'bx-ruler',
        'fields' => [
            ['name' => 'code', 'label' => 'Kode Satuan', 'type' => 'text', 'required' => false, 'unique' => true, 'searchable' => true, 'placeholder' => 'Contoh: PCS'],
            ['name' => 'name', 'label' => 'Nama Satuan', 'type' => 'text', 'required' => true, 'searchable' => true, 'placeholder' => 'Contoh: Pieces'],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'required' => false, 'searchable' => true, 'placeholder' => 'Masukkan deskripsi satuan'],
            ['name' => 'is_active', 'label' => 'Status', 'type' => 'select_status', 'required' => false],
        ],
        'rules' => [
            'code' => ['nullable', 'string', 'max:255', 'unique:units,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ],
        'columns' => [
            ['data' => 'DT_RowIndex', 'title' => 'No', 'className' => 'text-center'],
            ['data' => 'code', 'title' => 'Kode Unit', 'className' => 'text-center'],
            ['data' => 'name', 'title' => 'Nama Unit'],
            ['data' => 'description', 'title' => 'Deskripsi'],
            ['data' => 'is_active', 'title' => 'Status', 'className' => 'text-center'],
            ['data' => 'action', 'title' => 'Aksi', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ],
    ],
    'suppliers' => [
        'title' => 'Supplier',
        'subtitle' => 'Kelola data supplier barang masuk.',
        'table' => 'suppliers',
        'model' => Supplier::class,
        'icon' => 'bx-store',
        'fields' => [
            ['name' => 'code', 'label' => 'Kode Supplier', 'type' => 'text', 'required' => false, 'unique' => true, 'searchable' => true, 'placeholder' => 'Contoh: SUP-001'],
            ['name' => 'name', 'label' => 'Nama Supplier', 'type' => 'text', 'required' => true, 'searchable' => true, 'placeholder' => 'Masukkan nama supplier'],
            ['name' => 'phone', 'label' => 'No. Telepon', 'type' => 'text', 'required' => false, 'searchable' => true, 'placeholder' => 'Masukkan nomor telepon'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => false, 'searchable' => true, 'placeholder' => 'Masukkan email supplier'],
            ['name' => 'address', 'label' => 'Alamat', 'type' => 'textarea', 'required' => false, 'searchable' => true, 'placeholder' => 'Masukkan alamat supplier'],
            ['name' => 'is_active', 'label' => 'Status', 'type' => 'select_status', 'required' => false],
        ],
        'rules' => [
            'code' => ['nullable', 'string', 'max:255', 'unique:suppliers,code'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'address' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ],
        'columns' => [
            ['data' => 'DT_RowIndex', 'title' => 'No', 'className' => 'text-center'],
            ['data' => 'code', 'title' => 'Kode Supplier', 'className' => 'text-center'],
            ['data' => 'name', 'title' => 'Nama Supplier'],
            ['data' => 'phone', 'title' => 'Telp'],
            ['data' => 'email', 'title' => 'Email'],
            ['data' => 'address', 'title' => 'Alamat'],
            ['data' => 'is_active', 'title' => 'Status', 'className' => 'text-center'],
            ['data' => 'action', 'title' => 'Aksi', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ]
    ],
    'warehouses' => [
        'title' => 'Gudang',
        'subtitle' => 'Kelola data gudang penyimpanan barang.',
        'table' => 'warehouses',
        'model' => Warehouse::class,
        'icon' => 'bx-building-house',
        'fields' => [
            ['name' => 'code', 'label' => 'Kode Gudang', 'type' => 'text', 'required' => false, 'unique' => true, 'searchable' => true, 'placeholder' => 'Contoh: GDG-001'],
            ['name' => 'name', 'label' => 'Nama Gudang', 'type' => 'text', 'required' => true, 'searchable' => true, 'placeholder' => 'Masukkan nama gudang'],
            ['name' => 'location', 'label' => 'Alamat', 'type' => 'textarea', 'required' => false, 'searchable' => true, 'placeholder' => 'Masukkan alamat gudang'],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'required' => false, 'searchable' => true, 'placeholder' => 'Masukkan deskripsi gudang'],
            ['name' => 'is_active', 'label' => 'Status', 'type' => 'select_status', 'required' => false],
        ],
        'rules' => [
            'code' => ['nullable', 'string', 'max:255', 'unique:warehouses,code'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ],
        'columns' => [
            ['data' => 'DT_RowIndex', 'title' => 'No', 'className' => 'text-center'],
            ['data' => 'code', 'title' => 'Kode Gudang', 'className' => 'text-center'],
            ['data' => 'name', 'title' => 'Nama Gudang'],
            ['data' => 'location', 'title' => 'Alamat'],
            ['data' => 'is_active', 'title' => 'Status', 'className' => 'text-center'],
            ['data' => 'action', 'title' => 'Aksi', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ]
    ],
];
