@if (count($errors))
    <div class="form-group">
        <div class="alert alert-danger">
            <ul>
                @if ($errors->all())
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
@endif
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Tanggal Barang Keluar <span class="text-danger">*</span></label>
        <input type="date" name="request_date"
            value="{{ old('request_date', $requestDateValue ?? now()->format('Y-m-d')) }}"
            class="form-control @error('request_date') is-invalid @enderror">
        @error('request_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Gudang Asal <span class="text-danger">*</span></label>
        <select name="warehouse_id" id="warehouseId" class="form-select @error('warehouse_id') is-invalid @enderror">
            <option value="">Pilih Gudang</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $stockOutRequest->warehouse_id ?? '') == $warehouse->id)>
                    {{ $warehouse->name }}
                </option>
            @endforeach
        </select>
        @error('warehouse_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Catatan</label>
        <textarea name="note" rows="3" class="form-control @error('note') is-invalid @enderror"
            placeholder="Masukkan catatan jika diperlukan">{{ old('note', $noteValue ?? '') }}</textarea>
        @error('note')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="detail-box">

            @include('admin.partials.item-picker-modal', [
                'modalId' => 'stockOutItemPickerModal',
                'tableId' => 'stockOutItemPickerTable',
                'showStock' => true,
            ])

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-1">Detail Barang</h6>
                    <p class="text-muted small mb-0">Hanya barang yang memiliki stok di gudang asal yang dapat dipilih.
                    </p>
                </div>
                <button type="button" class="btn btn-red btn-sm" id="openStockOutItemPicker" data-bs-toggle="modal"
                    data-bs-target="#stockOutItemPickerModal" disabled>
                    <i class="bx bx-search me-1"></i>
                    Pilih Barang
                </button>
            </div>
            <div id="itemRows"></div>
            <div id="warehouseHint" class="text-muted small">Pilih gudang terlebih dahulu.</div>
            @error('item_id')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
            @error('item_id.*')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
            @error('quantity.*')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const warehouseSelect = document.getElementById('warehouseId');
            const pickerButton = document.getElementById('openStockOutItemPicker');
            const itemRows = document.getElementById('itemRows');
            const warehouseHint = document.getElementById('warehouseHint');
            const modalElement = document.getElementById('stockOutItemPickerModal');

            const itemTable = $('#stockOutItemPickerTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: @json(route('admin.stock-out-requests.items.data')),
                    data: function(request) {
                        request.warehouse_id = warehouseSelect.value;
                    },
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'item_code',
                        name: 'items.item_code',
                        defaultContent: '-',
                    },
                    {
                        data: 'name',
                        name: 'items.name',
                    },
                    {
                        data: 'category_name',
                        name: 'categories.name',
                        defaultContent: '-',
                    },
                    {
                        data: 'unit_name',
                        name: 'units.name',
                        defaultContent: '-',
                    },
                    {
                        data: 'available_stock',
                        name: 'item_stocks.quantity',
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function() {
                            return `
                        <button
                            type="button"
                            class="btn btn-red btn-sm choose-stock-out-item"
                        >
                            Pilih
                        </button>
                    `;
                        },
                    },
                ],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ barang',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ barang',
                    infoEmpty: 'Tidak ada barang',
                    zeroRecords: 'Barang tidak ditemukan',
                    processing: 'Memuat barang...',
                    paginate: {
                        previous: 'Sebelumnya',
                        next: 'Berikutnya',
                    },
                },
            });

            function updateWarehouseState() {
                const warehouseSelected = Boolean(warehouseSelect.value);

                pickerButton.disabled = !warehouseSelected;
                warehouseHint.textContent = warehouseSelected ?
                    'Pilih barang yang tersedia di gudang ini.' :
                    'Pilih gudang terlebih dahulu.';
            }

            warehouseSelect.addEventListener('change', function() {
                if (itemRows.querySelector('.detail-row')) {
                    const confirmed = confirm(
                        'Mengganti gudang akan menghapus barang yang sudah dipilih. Lanjutkan?'
                    );

                    if (!confirmed) {
                        return;
                    }
                }

                itemRows.innerHTML = '';
                updateWarehouseState();
                itemTable.ajax.reload();
            });

            modalElement.addEventListener('show.bs.modal', function(event) {
                if (!warehouseSelect.value) {
                    event.preventDefault();
                    alert('Pilih gudang asal terlebih dahulu.');
                    return;
                }

                itemTable.ajax.reload();
            });

            modalElement.addEventListener('shown.bs.modal', function() {
                itemTable.columns.adjust().responsive.recalc();
            });

            $('#stockOutItemPickerTable').on(
                'click',
                '.choose-stock-out-item',
                function() {
                    const item = itemTable.row($(this).closest('tr')).data();

                    if (!item) {
                        return;
                    }

                    if (isItemSelected(item.id)) {
                        alert('Barang tersebut sudah ditambahkan.');
                        return;
                    }

                    addItemRow(item);

                    bootstrap.Modal
                        .getOrCreateInstance(modalElement)
                        .hide();
                }
            );

            function isItemSelected(itemId) {
                return Array.from(
                    itemRows.querySelectorAll('[name="item_id[]"]')
                ).some(function(input) {
                    return String(input.value) === String(itemId);
                });
            }

            function addItemRow(item) {
                const row = document.createElement('div');

                row.className = 'detail-row';
                row.dataset.itemId = item.id;

                row.innerHTML = `
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        Barang <span class="text-danger">*</span>
                    </label>

                    <input
                        type="hidden"
                        name="item_id[]"
                        value="${escapeHtml(item.id)}"
                    >

                    <input
                        type="text"
                        class="form-control"
                        value="${escapeHtml(item.item_code ?? '-')} - ${escapeHtml(item.name)} - Tersedia ${escapeHtml(item.available_stock)}"
                        readonly
                    >
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        Qty <span class="text-danger">*</span>
                    </label>

                    <input
                        type="number"
                        name="quantity[]"
                        min="1"
                        max="${escapeHtml(item.available_stock)}"
                        class="form-control"
                        placeholder="0"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Catatan Item
                    </label>

                    <input
                        type="text"
                        name="item_note[]"
                        class="form-control"
                        placeholder="Opsional"
                    >
                </div>

                <div class="col-md-1 d-grid">
                    <button
                        type="button"
                        class="btn btn-light border remove-row"
                    >
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            </div>
        `;

                itemRows.appendChild(row);
            }

            document.addEventListener('click', function(event) {
                const removeButton = event.target.closest('.remove-row');

                if (removeButton) {
                    removeButton.closest('.detail-row')?.remove();
                }
            });

            function escapeHtml(value) {
                const element = document.createElement('div');
                element.textContent = value ?? '';

                return element.innerHTML;
            }

            updateWarehouseState();
        });
    </script>
@endpush
{{-- @push('scripts')
<script>
    const warehouseSelect = document.getElementById('warehouseId');
    const itemRows = document.getElementById('itemRows');
    const addItemRow = document.getElementById('addItemRow');
    const warehouseHint = document.getElementById('warehouseHint');
    const itemsUrlTemplate = @json(route('admin.stock-out-requests.warehouse-items', ['warehouse' => '__WAREHOUSE__']));
    const initialRows = @json($detailRows ?? []);
    const oldItemIds = @json(old('item_id', []));
    const oldQuantities = @json(old('quantity', []));
    const oldNotes = @json(old('item_note', []));
    let availableItems = @json($items);

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value ?? '';
        return element.innerHTML;
    }

    function options(selectedId = '') {
        return '<option value="">Pilih Barang</option>' + availableItems.map(item => {
            const selected = String(item.id) === String(selectedId) ? ' selected' : '';
            const code = item.item_code ?? '-';
            return `<option value="${item.id}"${selected}>${escapeHtml(code)} - ${escapeHtml(item.name)} (stok: ${item.available_stock})</option>`;
        }).join('');
    }

    function addRow(detail = {}) {
        const row = document.createElement('div');
        row.className = 'detail-row';
        row.innerHTML = `
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Barang <span class="text-danger">*</span></label>
                    <select name="item_id[]" class="form-select">${options(detail.item_id)}</select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Qty <span class="text-danger">*</span></label>
                    <input type="number" name="quantity[]" value="${escapeHtml(detail.quantity)}" min="1" class="form-control" placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Catatan Item</label>
                    <input type="text" name="item_note[]" value="${escapeHtml(detail.note)}" class="form-control" placeholder="Opsional">
                </div>
                <div class="col-md-1 d-grid">
                    <button type="button" class="btn btn-light border remove-row"><i class="bx bx-trash"></i></button>
                </div>
            </div>`;
        itemRows.appendChild(row);
    }

    function submittedRows() {
        if (oldItemIds.length) {
            return oldItemIds.map((itemId, index) => ({
                item_id: itemId,
                quantity: oldQuantities[index] ?? '',
                note: oldNotes[index] ?? '',
            }));
        }
        return initialRows;
    }

    async function loadWarehouseItems(keepRows = false) {
        itemRows.innerHTML = '';
        const warehouseId = warehouseSelect.value;
        addItemRow.disabled = true;

        if (!warehouseId) {
            availableItems = [];
            warehouseHint.textContent = 'Pilih gudang terlebih dahulu.';
            return;
        }

        warehouseHint.textContent = 'Memuat barang...';
        const response = await fetch(itemsUrlTemplate.replace('__WAREHOUSE__', warehouseId), {
            headers: { 'Accept': 'application/json' },
        });
        availableItems = await response.json();

        if (!availableItems.length) {
            warehouseHint.textContent = 'Tidak ada barang dengan stok tersedia di gudang ini.';
            return;
        }

        warehouseHint.textContent = '';
        addItemRow.disabled = false;
        const rows = keepRows ? submittedRows() : [];
        (rows.length ? rows : [{}]).forEach(addRow);
    }

    addItemRow.addEventListener('click', () => addRow());
    warehouseSelect.addEventListener('change', () => loadWarehouseItems(false));
    document.addEventListener('click', event => {
        const button = event.target.closest('.remove-row');
        if (!button) return;
        if (itemRows.querySelectorAll('.detail-row').length === 1) {
            alert('Minimal harus ada satu baris barang.');
            return;
        }
        button.closest('.detail-row').remove();
    });

    if (warehouseSelect.value) loadWarehouseItems(true);
</script>
@endpush --}}
