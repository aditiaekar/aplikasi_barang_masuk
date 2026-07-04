@php
    $modalId = $modalId ?? 'itemPickerModal';
    $tableId = $tableId ?? 'itemPickerTable';
    $showStock = $showStock ?? false;
@endphp

<div
    class="modal fade"
    id="{{ $modalId }}"
    tabindex="-1"
    aria-labelledby="{{ $modalId }}Label"
    aria-hidden="true"
>
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="{{ $modalId }}Label">
                        Pilih Barang
                    </h5>

                    <p class="text-muted small mb-0">
                        Cari lalu pilih barang yang akan ditambahkan.
                    </p>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Tutup"
                ></button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table
                        id="{{ $tableId }}"
                        class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th style="width: 60px">No.</th>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Satuan</th>

                                @if ($showStock)
                                    <th>Stok Tersedia</th>
                                @endif

                                <th style="width: 100px">Aksi</th>
                            </tr>
                        </thead>

                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-light border"
                    data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
