@extends('layouts.admin')

@section('title', 'Data Barang')
@section('page-title', 'Data Barang')
@section('page-subtitle', 'Kelola data barang dan stok awal.')

@push('styles')
    <style>
        .item-card {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(229, 231, 235, 0.85);
            border-radius: 22px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .item-card-header {
            padding: 1.25rem 1.35rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .item-card-header h5 {
            margin: 0;
            font-weight: 800;
            color: #1f2937;
        }

        .item-card-header p {
            margin: 0.25rem 0 0;
            color: #6b7280;
            font-size: 0.88rem;
        }

        .btn-red {
            background: linear-gradient(135deg, #9f1239, #be123c);
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 0.7rem 1rem;
            font-weight: 700;
            box-shadow: 0 12px 24px rgba(159, 18, 57, 0.2);
        }

        .btn-red:hover {
            color: #fff;
        }

        .filter-box {
            padding: 1rem 1.35rem;
            border-bottom: 1px solid #e5e7eb;
            background: #fff;
        }

        .form-control,
        .form-select {
            border-radius: 14px;
            border-color: #e5e7eb;
            padding: 0.72rem 0.9rem;
            font-size: 0.92rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #be123c;
            box-shadow: 0 0 0 0.2rem rgba(190, 18, 60, 0.12);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f9fafb;
            color: #374151;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.95rem 1rem;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f5f9;
            color: #374151;
        }

        .item-photo {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .item-photo-placeholder {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: #fff1f2;
            color: #9f1239;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        .item-code {
            display: inline-flex;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            background: #fff1f2;
            color: #9f1239;
            font-weight: 800;
            font-size: 0.78rem;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
            border-radius: 999px;
            padding: 0.4rem 0.7rem;
            font-weight: 700;
            font-size: 0.78rem;
        }

        .badge-inactive {
            background: #f3f4f6;
            color: #6b7280;
            border-radius: 999px;
            padding: 0.4rem 0.7rem;
            font-weight: 700;
            font-size: 0.78rem;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #374151;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background: #fff1f2;
            color: #9f1239;
            border-color: rgba(159, 18, 57, 0.25);
        }

        .empty-state {
            padding: 2.5rem 1rem;
            text-align: center;
            color: #6b7280;
        }

        .empty-state i {
            width: 62px;
            height: 62px;
            border-radius: 20px;
            background: #f3f4f6;
            color: #9ca3af;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 575.98px) {

            .item-card-header,
            .filter-box {
                padding: 1rem;
            }

            .btn-red {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="item-card">
        <div class="item-card-header">
            <div>
                <h5>Daftar Barang</h5>
                <p>Data barang yang digunakan pada proses pencatatan barang masuk.</p>
            </div>

            <a href="{{ route('admin.items.create') }}" class="btn btn-red">
                <i class="bx bx-plus me-1"></i>
                Tambah Barang
            </a>
        </div>

        <div class="filter-box">
            <form action="{{ route('admin.items.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label fw-semibold">Pencarian</label>
                        <input type="text" id="filterKeyword" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Cari kode, barcode, atau nama barang">
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold">Kategori</label>
                        <select id="filterCategory" name="category_id" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold">Satuan</label>
                        <select id="filterUnit" name="unit_id" class="form-select">
                            <option value="">Semua Satuan</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select id="filterStatus" name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 d-grid">
                        <button type="button" id="btnFilter" class="btn btn-dark rounded-4">
                            <i class="bx bx-search me-1"></i>
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if (session('success'))
            <div class="alert alert-success m-3 rounded-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger m-3 rounded-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="table-responsive">
            <table id="datatable" class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 70px;">No</th>
                        <th>Gambar</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Barcode</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Stok Minimum</th>
                        <th>Harga</th>
                        <th>Total Stok</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    {{-- @forelse ($items as $item)
                @php
                $totalStock = $stockColumn ? $item->stocks->sum($stockColumn) : 0;
                @endphp

                <tr>
                    <td>{{ $items->firstItem() + $loop->index }}</td>

                    @if (in_array('image', $columns))
                    <td>
                        @if ($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" class="item-photo" alt="Gambar Barang">
                        @else
                        <span class="item-photo-placeholder">
                            <i class="bx bx-package"></i>
                        </span>
                        @endif
                    </td>
                    @endif

                    @if (in_array('item_code', $columns))
                    <td>
                        <span class="item-code">{{ $item->item_code }}</span>
                    </td>
                    @endif

                    <td>
                        <div class="fw-bold">{{ $item->name }}</div>

                        @if (in_array('description', $columns) && $item->description)
                        <small class="text-muted">
                            {{ \Illuminate\Support\Str::limit($item->description, 60) }}
                        </small>
                        @endif
                    </td>

                    @if (in_array('barcode', $columns))
                    <td>{{ $item->barcode ?? '-' }}</td>
                    @endif

                    @if (in_array('category_id', $columns))
                    <td>{{ $item->category->name ?? '-' }}</td>
                    @endif

                    @if (in_array('unit_id', $columns))
                    <td>{{ $item->unit->name ?? '-' }}</td>
                    @endif

                    @if (in_array('minimum_stock', $columns))
                    <td>{{ $item->minimum_stock }}</td>
                    @endif

                    @if (in_array('price', $columns))
                    <td>Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</td>
                    @endif

                    <td>
                        <strong>{{ $totalStock }}</strong>
                    </td>

                    @if (in_array('is_active', $columns))
                    <td>
                        @if ($item->is_active)
                        <span class="badge-active">Aktif</span>
                        @else
                        <span class="badge-inactive">Nonaktif</span>
                        @endif
                    </td>
                    @endif

                    <td class="text-end">
                        <a href="{{ route('admin.items.edit', $item->id) }}" class="action-btn" title="Edit">
                            <i class="bx bx-edit"></i>
                        </a>

                        <form action="{{ route('admin.items.destroy', $item->id) }}"
                            method="POST"
                            class="d-inline"
                            onsubmit="return confirm('Yakin ingin menghapus data barang ini?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="action-btn" title="Hapus">
                                <i class="bx bx-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12">
                        <div class="empty-state">
                            <i class="bx bx-package"></i>
                            <h6 class="fw-bold mb-1">Belum Ada Data Barang</h6>
                            <div>Silakan tambahkan data barang terlebih dahulu.</div>
                        </div>
                    </td>
                </tr>
                @endforelse --}}
                </tbody>
            </table>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,

                searching: false,
                lengthChange: false,
                pageLength: 10,

                stateSaveParams: function(settings, data) {
                    data.filterKeyword = $('#filterKeyword').val();
                    data.filterCategory = $('#filterCategory').val();
                    data.filterUnit = $('#filterUnit').val();
                    data.filterStatus = $('#filterStatus').val();
                },

                stateLoadParams: function(settings, data) {
                    $('#filterKeyword').val(data.filterKeyword || '');
                    $('#filterCategory').val(data.filterCategory);
                    $('#filterUnit').val(data.filterUnit);
                    $('#filterStatus').val(data.filterStatus || '');
                },

                drawCallback: function(settings) {
                    let api = this.api();
                    let info = api.page.info();
                    if(info.page > 0 && info.page >= info.pages) {
                        api.page('previous').draw('page');
                    }
                },

                ajax: {
                    url: "{{ route('admin.items.data') }}",
                    data: function(d) {
                        d.keyword = $('#filterKeyword').val();
                        d.category = $('#filterCategory').val();
                        d.unit = $('#filterUnit').val();
                        d.status = $('#filterStatus').val();
                    }
                },

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'image',
                        name: 'image',
                    },
                    {
                        data: 'item_code',
                        name: 'item_code'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'barcode',
                        name: 'barcode'
                    },
                    {
                        data: 'category.name',
                        name: 'category.name'
                    },
                    {
                        data: 'unit.name',
                        name: 'unit.name'
                    },
                    {
                        data: 'minimum_stock',
                        name: 'minimum_stock',
                    },
                    {
                        data: 'price',
                        name: 'price',
                        render: function(data, type) {
                            let num = parseFloat(data) || 0;
                            return "Rp. " + num.toLocaleString("id-ID");
                        }
                    },
                    {
                        data: 'total_stock',
                        name: 'total_stock',
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end text-nowrap'
                    }
                ]
            });

            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });
        })
    </script>
@endpush
