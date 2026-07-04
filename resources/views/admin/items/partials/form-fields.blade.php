<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
        <input type="text" name="item_code" value="{{ old('item_code', $item->item_code ?? '') }}"
            class="form-control @error('item_code') is-invalid @enderror" placeholder="Contoh: BRG-001">
        @error('item_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $item->name ?? '') }}"
            class="form-control @error('name') is-invalid @enderror" placeholder="Masukkan nama barang">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Kategori</label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
            <option value="">Pilih Kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    {{ old('category_id', $item->category_id ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Satuan</label>
        <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
            <option value="">Pilih Satuan</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}"
                    {{ old('unit_id', $item->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                    {{ $unit->name }}
                </option>
            @endforeach
        </select>
        @error('unit_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Barcode</label>
        <input type="text" name="barcode" value="{{ old('barcode', $item->barcode ?? '') }}"
            class="form-control @error('barcode') is-invalid @enderror" placeholder="Masukkan barcode jika ada">
        @error('barcode')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Stok Minimum</label>
        <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $item->minimum_stock ?? 0) }}"
            min="0" class="form-control @error('minimum_stock') is-invalid @enderror">
        @error('minimum_stock')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="price" class="form-label">Harga</label>
        <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror"
            value="{{ old('price', (int)$item->price ?? 0) }}" min="0" required>

        @error('price')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Status</label>
        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
            <option value="1" {{ old('is_active', $item->is_active ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ old('is_active', $item->is_active ?? 1) == 0 ? 'selected' : '' }}>Nonaktif
            </option>
        </select>
        @error('is_active')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Foto Barang</label>
        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if (!empty($item?->image))
            <div class="mt-3">
                <img src="{{ asset('storage/' . $item->image) }}" class="image-preview" alt="Foto Barang">
            </div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Deskripsi</label>
        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
            placeholder="Masukkan deskripsi barang">{{ old('description', $item->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="stock-box">
            <h6 class="fw-bold mb-1">Stok Awal per Gudang</h6>
            <p class="text-muted small mb-3">
                Isi stok awal jika barang sudah memiliki stok. Jika belum ada, biarkan 0.
            </p>
            <div class="row g-3">
                @foreach ($warehouses as $warehouse)
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ $warehouse->name }}</label>
                        <input type="number" name="stocks[{{ $warehouse->id }}]"
                            value="{{ old('stocks.' . $warehouse->id, $stocks[$warehouse->id] ?? 0) }}" min="0"
                            class="form-control" placeholder="0">
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
