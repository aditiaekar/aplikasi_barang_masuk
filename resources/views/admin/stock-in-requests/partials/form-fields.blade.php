<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label fw-semibold">Tanggal Barang Masuk <span class="text-danger">*</span></label>
        <input type="date"
               name="request_date"
               value="{{ old('request_date', $requestDateValue ?? now()->format('Y-m-d')) }}"
               class="form-control @error('request_date') is-invalid @enderror">
        @error('request_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
        <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
            <option value="">Pilih Supplier</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}"
                    {{ old('supplier_id', $stockInRequest->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                    {{ $supplier->name }}
                </option>
            @endforeach
        </select>
        @error('supplier_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Gudang Tujuan <span class="text-danger">*</span></label>
        <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror">
            <option value="">Pilih Gudang</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}"
                    {{ old('warehouse_id', $stockInRequest->warehouse_id ?? '') == $warehouse->id ? 'selected' : '' }}>
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
        <textarea name="note"
                  rows="3"
                  class="form-control @error('note') is-invalid @enderror"
                  placeholder="Masukkan catatan jika diperlukan">{{ old('note', $noteValue ?? '') }}</textarea>
        @error('note')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="detail-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-1">Detail Barang</h6>
                    <p class="text-muted small mb-0">Tambahkan barang yang masuk pada transaksi ini.</p>
                </div>

                <button type="button" class="btn btn-red btn-sm" id="addItemRow">
                    <i class="bx bx-plus me-1"></i>
                    Tambah Baris
                </button>
            </div>

            <div id="itemRows">
                @if (!empty($detailRows) && count($detailRows))
                    @foreach ($detailRows as $detail)
                        <div class="detail-row">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Barang <span class="text-danger">*</span></label>
                                    <select name="item_id[]" class="form-select">
                                        <option value="">Pilih Barang</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item->id }}" {{ $detail['item_id'] == $item->id ? 'selected' : '' }}>
                                                {{ $item->code ?? '-' }} - {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Qty <span class="text-danger">*</span></label>
                                    <input type="number"
                                           name="quantity[]"
                                           value="{{ $detail['quantity'] }}"
                                           min="1"
                                           class="form-control"
                                           placeholder="0">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Catatan Item</label>
                                    <input type="text"
                                           name="item_note[]"
                                           value="{{ $detail['note'] ?? '' }}"
                                           class="form-control"
                                           placeholder="Opsional">
                                </div>

                                <div class="col-md-1 d-grid">
                                    <button type="button" class="btn btn-light border remove-row">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="detail-row">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Barang <span class="text-danger">*</span></label>
                                <select name="item_id[]" class="form-select">
                                    <option value="">Pilih Barang</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->code ?? '-' }} - {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Qty <span class="text-danger">*</span></label>
                                <input type="number"
                                       name="quantity[]"
                                       min="1"
                                       class="form-control"
                                       placeholder="0">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Catatan Item</label>
                                <input type="text"
                                       name="item_note[]"
                                       class="form-control"
                                       placeholder="Opsional">
                            </div>

                            <div class="col-md-1 d-grid">
                                <button type="button" class="btn btn-light border remove-row">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @error('item_id')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror

            @error('quantity')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
    const itemOptions = `
        <option value="">Pilih Barang</option>
        @foreach ($items as $item)
            <option value="{{ $item->id }}">{{ $item->code ?? '-' }} - {{ $item->name }}</option>
        @endforeach
    `;

    const itemRows = document.getElementById('itemRows');
    const addItemRow = document.getElementById('addItemRow');

    addItemRow.addEventListener('click', function () {
        const row = document.createElement('div');
        row.classList.add('detail-row');

        row.innerHTML = `
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Barang <span class="text-danger">*</span></label>
                    <select name="item_id[]" class="form-select">
                        ${itemOptions}
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Qty <span class="text-danger">*</span></label>
                    <input type="number"
                           name="quantity[]"
                           min="1"
                           class="form-control"
                           placeholder="0">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Catatan Item</label>
                    <input type="text"
                           name="item_note[]"
                           class="form-control"
                           placeholder="Opsional">
                </div>

                <div class="col-md-1 d-grid">
                    <button type="button" class="btn btn-light border remove-row">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            </div>
        `;

        itemRows.appendChild(row);
    });

    document.addEventListener('click', function (event) {
        if (event.target.closest('.remove-row')) {
            const rows = document.querySelectorAll('.detail-row');

            if (rows.length <= 1) {
                alert('Minimal harus ada satu baris barang.');
                return;
            }

            event.target.closest('.detail-row').remove();
        }
    });
</script>
@endpush