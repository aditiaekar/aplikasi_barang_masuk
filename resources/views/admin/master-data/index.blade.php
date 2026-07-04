@extends('layouts.admin')

@section('title', 'Data ' . $config['title'])
@section('page-title', 'Data ' . $config['title'])
@section('page-subtitle', $config['subtitle'])

@push('styles')
    <style>
        .master-card {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(229, 231, 235, 0.85);
            border-radius: 22px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .master-card-header {
            padding: 1.25rem 1.35rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .master-card-header h5 {
            margin: 0;
            font-weight: 800;
            color: #1f2937;
        }

        .master-card-header p {
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

        .form-control {
            border-radius: 14px;
            border-color: #e5e7eb;
            padding: 0.72rem 0.9rem;
        }

        .form-control:focus {
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

        .master-code {
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

            .master-card-header,
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
    <div class="master-card">
        <div class="master-card-header">
            <div>
                <h5>Daftar {{ $config['title'] }}</h5>
                <p>{{ $config['subtitle'] }}</p>
            </div>

            <a href="{{ $config['createRoute'] }}" class="btn btn-red">
                <i class="bx bx-plus me-1"></i>
                Tambah {{ $config['title'] }}
            </a>
        </div>

        <div class="filter-box">
            <div class="row g-3 align-items-end">
                <div class="col-lg-8 col-md-8">
                    <label class="form-label fw-semibold">Pencarian</label>
                    <input type="text" id="filterKeyword" name="search" value="{{ request('search') }}"
                        class="form-control" placeholder="Cari data {{ strtolower($config['title']) }}">
                </div>

                <div class="col-lg-4 col-md-4 d-grid">
                    <button type="button" id="btnFilter" class="btn btn-dark rounded-4">
                        <i class="bx bx-search me-1"></i>
                        Cari Data
                    </button>
                </div>
            </div>
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
            <table id="masterDatatable" class="table align-middle">
                <thead>
                    <tr>
                        @foreach ($config['columns'] as $column)
                            <th>{{ $column['title'] }}</th>
                        @endforeach
                    </tr>
                </thead>

                {{-- <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $items->firstItem() + $loop->index }}</td>

                        @foreach ($fields as $field)
                            @if ($field['type'] !== 'textarea')
                                <td>
                                    @if ($field['name'] === 'code')
                                        <span class="master-code">{{ $item->{$field['name']} ?? '-' }}</span>
                                    @elseif ($field['name'] === 'is_active')
                                        @if ($item->{$field['name']})
                                            <span class="badge-active">Aktif</span>
                                        @else
                                            <span class="badge-inactive">Nonaktif</span>
                                        @endif
                                    @else
                                        {{ $item->{$field['name']} ?? '-' }}
                                    @endif
                                </td>
                            @endif
                        @endforeach

                        <td class="text-end">
                            <a href="{{ route('admin.master-data.edit', [$type, $item->id]) }}"
                               class="action-btn"
                               title="Edit">
                                <i class="bx bx-edit"></i>
                            </a>

                            <form action="{{ route('admin.master-data.destroy', [$type, $item->id]) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
                        <td colspan="{{ count($fields) + 2 }}">
                            <div class="empty-state">
                                <i class="bx {{ $config['icon'] }}"></i>
                                <h6 class="fw-bold mb-1">Belum Ada Data</h6>
                                <div>Silakan tambahkan data {{ strtolower($config['title']) }} terlebih dahulu.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody> --}}
            </table>
        </div>
    </div>
@endsection
@php
    $dataTableColumns = collect($config['columns'])
        ->map(
            fn($column) => [
                'data' => $column['data'],
                'name' => $column['data'],
                'className' => $column['className'] ?? '',
                'orderable' => $column['orderable'] ?? !in_array($column['data'], ['DT_RowIndex', 'action']),
                'searchable' => $column['searchable'] ?? !in_array($column['data'], ['DT_RowIndex', 'action']),
            ],
        )
        ->values();
@endphp
@push('scripts')
    <script>
        $(function() {
            const table = $('#masterDatatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                stateSave: true,

                searching: false,
                lengthChange: false,
                pageLength: 10,

                stateSaveParams: function(settings, data) {
                    data.filterKeyword = $('#filterKeyword').val();
                },

                stateLoadParams: function(settings, data) {
                    $('#filterKeyword').val(data.filterKeyword || '');
                },

                ajax: {
                    url: @json($config['dataRoute']),
                    data: function(data) {
                        data.keyword = $('#filterKeyword').val();
                    },
                },
                columns: @json($dataTableColumns),
            });

            $('#btnFilter').on('click', function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush
