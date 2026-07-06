<div class="row g-3">
    @foreach ($fields as $field)
        @php
            $fieldName = $field['name'];
            $oldValue = old($fieldName, $item->{$fieldName} ?? null);
        @endphp

        @if ($field['type'] === 'textarea')
            <div class="col-12">
                <label class="form-label fw-semibold">
                    {{ $field['label'] }}
                    @if ($field['required'] ?? false)
                        <span class="text-danger">*</span>
                    @endif
                </label>

                <textarea name="{{ $fieldName }}"
                          rows="4"
                          class="form-control @error($fieldName) is-invalid @enderror"
                          placeholder="{{ $field['placeholder'] ?? '' }}">{{ $oldValue }}</textarea>

                @error($fieldName)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        @elseif ($field['type'] === 'select_status')
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    {{ $field['label'] }}
                </label>

                <select name="{{ $fieldName }}" class="form-select @error($fieldName) is-invalid @enderror">
                    <option value="1" @selected($oldValue === true || (string) $oldValue === '1')>Aktif</option>
                    <option value="0" @selected($oldValue === false || (string) $oldValue === '0')>Nonaktif</option>
                </select>

                @error($fieldName)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        @else
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    {{ $field['label'] }}
                    @if ($field['required'] ?? false)
                        <span class="text-danger">*</span>
                    @endif
                </label>

                <input type="{{ $field['type'] ?? 'text' }}"
                       name="{{ $fieldName }}"
                       value="{{ $oldValue }}"
                       class="form-control @error($fieldName) is-invalid @enderror"
                       placeholder="{{ $field['placeholder'] ?? '' }}">

                @error($fieldName)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        @endif
    @endforeach
</div>
