<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    private function configs(): array
    {
        return [
            'categories' => [
                'title' => 'Kategori',
                'subtitle' => 'Kelola data kategori barang.',
                'table' => 'categories',
                'model' => Category::class,
                'icon' => 'bx-category',
                'fields' => [
                    [
                        'name' => 'code',
                        'label' => 'Kode Kategori',
                        'type' => 'text',
                        'required' => false,
                        'unique' => true,
                        'searchable' => true,
                        'placeholder' => 'Contoh: KAT-001',
                    ],
                    [
                        'name' => 'name',
                        'label' => 'Nama Kategori',
                        'type' => 'text',
                        'required' => true,
                        'searchable' => true,
                        'placeholder' => 'Masukkan nama kategori',
                    ],
                    [
                        'name' => 'description',
                        'label' => 'Deskripsi',
                        'type' => 'textarea',
                        'required' => false,
                        'searchable' => true,
                        'placeholder' => 'Masukkan deskripsi kategori',
                    ],
                    [
                        'name' => 'is_active',
                        'label' => 'Status',
                        'type' => 'select_status',
                        'required' => false,
                    ],
                ],
            ],

            'units' => [
                'title' => 'Satuan',
                'subtitle' => 'Kelola data satuan barang.',
                'table' => 'units',
                'model' => Unit::class,
                'icon' => 'bx-ruler',
                'fields' => [
                    [
                        'name' => 'code',
                        'label' => 'Kode Satuan',
                        'type' => 'text',
                        'required' => false,
                        'unique' => true,
                        'searchable' => true,
                        'placeholder' => 'Contoh: PCS',
                    ],
                    [
                        'name' => 'name',
                        'label' => 'Nama Satuan',
                        'type' => 'text',
                        'required' => true,
                        'searchable' => true,
                        'placeholder' => 'Contoh: Pieces',
                    ],
                    [
                        'name' => 'symbol',
                        'label' => 'Simbol',
                        'type' => 'text',
                        'required' => false,
                        'searchable' => true,
                        'placeholder' => 'Contoh: pcs',
                    ],
                    [
                        'name' => 'description',
                        'label' => 'Deskripsi',
                        'type' => 'textarea',
                        'required' => false,
                        'searchable' => true,
                        'placeholder' => 'Masukkan deskripsi satuan',
                    ],
                    [
                        'name' => 'is_active',
                        'label' => 'Status',
                        'type' => 'select_status',
                        'required' => false,
                    ],
                ],
            ],

            'suppliers' => [
                'title' => 'Supplier',
                'subtitle' => 'Kelola data supplier barang masuk.',
                'table' => 'suppliers',
                'model' => Supplier::class,
                'icon' => 'bx-store',
                'fields' => [
                    [
                        'name' => 'code',
                        'label' => 'Kode Supplier',
                        'type' => 'text',
                        'required' => false,
                        'unique' => true,
                        'searchable' => true,
                        'placeholder' => 'Contoh: SUP-001',
                    ],
                    [
                        'name' => 'name',
                        'label' => 'Nama Supplier',
                        'type' => 'text',
                        'required' => true,
                        'searchable' => true,
                        'placeholder' => 'Masukkan nama supplier',
                    ],
                    [
                        'name' => 'phone',
                        'label' => 'No. Telepon',
                        'type' => 'text',
                        'required' => false,
                        'searchable' => true,
                        'placeholder' => 'Masukkan nomor telepon',
                    ],
                    [
                        'name' => 'email',
                        'label' => 'Email',
                        'type' => 'email',
                        'required' => false,
                        'searchable' => true,
                        'placeholder' => 'Masukkan email supplier',
                    ],
                    [
                        'name' => 'address',
                        'label' => 'Alamat',
                        'type' => 'textarea',
                        'required' => false,
                        'searchable' => true,
                        'placeholder' => 'Masukkan alamat supplier',
                    ],
                    [
                        'name' => 'is_active',
                        'label' => 'Status',
                        'type' => 'select_status',
                        'required' => false,
                    ],
                ],
            ],

            'warehouses' => [
                'title' => 'Gudang',
                'subtitle' => 'Kelola data gudang penyimpanan barang.',
                'table' => 'warehouses',
                'model' => Warehouse::class,
                'icon' => 'bx-building-house',
                'fields' => [
                    [
                        'name' => 'code',
                        'label' => 'Kode Gudang',
                        'type' => 'text',
                        'required' => false,
                        'unique' => true,
                        'searchable' => true,
                        'placeholder' => 'Contoh: GDG-001',
                    ],
                    [
                        'name' => 'name',
                        'label' => 'Nama Gudang',
                        'type' => 'text',
                        'required' => true,
                        'searchable' => true,
                        'placeholder' => 'Masukkan nama gudang',
                    ],
                    [
                        'name' => 'address',
                        'label' => 'Alamat',
                        'type' => 'textarea',
                        'required' => false,
                        'searchable' => true,
                        'placeholder' => 'Masukkan alamat gudang',
                    ],
                    [
                        'name' => 'description',
                        'label' => 'Deskripsi',
                        'type' => 'textarea',
                        'required' => false,
                        'searchable' => true,
                        'placeholder' => 'Masukkan deskripsi gudang',
                    ],
                    [
                        'name' => 'is_active',
                        'label' => 'Status',
                        'type' => 'select_status',
                        'required' => false,
                    ],
                ],
            ],
        ];
    }

    private function getConfig(string $type): array
    {
        $configs = $this->configs();

        abort_if(!array_key_exists($type, $configs), 404);

        return $configs[$type];
    }

    private function availableFields(array $config): array
    {
        return collect($config['fields'])
            ->filter(fn ($field) => Schema::hasColumn($config['table'], $field['name']))
            ->values()
            ->all();
    }

    private function validationRules(array $config, array $fields, ?int $ignoreId = null): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $fieldRules = [];

            $fieldRules[] = $field['required'] ?? false ? 'required' : 'nullable';

            if (($field['type'] ?? 'text') === 'email') {
                $fieldRules[] = 'email';
            }

            if (($field['type'] ?? 'text') === 'select_status') {
                $fieldRules[] = 'boolean';
            } else {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:255';
            }

            if (($field['unique'] ?? false) === true) {
                $uniqueRule = Rule::unique($config['table'], $field['name']);

                if ($ignoreId) {
                    $uniqueRule->ignore($ignoreId);
                }

                $fieldRules[] = $uniqueRule;
            }

            $rules[$field['name']] = $fieldRules;
        }

        return $rules;
    }

    public function index(Request $request, string $type)
    {
        $config = $this->getConfig($type);
        $fields = $this->availableFields($config);
        $model = $config['model'];

        $searchableFields = collect($fields)
            ->filter(fn ($field) => $field['searchable'] ?? false)
            ->pluck('name')
            ->values();

        $items = $model::query()
            ->when($request->search, function ($query, $search) use ($searchableFields) {
                $query->where(function ($query) use ($search, $searchableFields) {
                    foreach ($searchableFields as $field) {
                        $query->orWhere($field, 'like', "%{$search}%");
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.master-data.index', compact('type', 'config', 'fields', 'items'));
    }

    public function create(string $type)
    {
        $config = $this->getConfig($type);
        $fields = $this->availableFields($config);

        return view('admin.master-data.create', compact('type', 'config', 'fields'));
    }

    public function store(Request $request, string $type)
    {
        $config = $this->getConfig($type);
        $fields = $this->availableFields($config);
        $model = $config['model'];

        $validated = $request->validate(
            $this->validationRules($config, $fields)
        );

        if (Schema::hasColumn($config['table'], 'is_active') && !array_key_exists('is_active', $validated)) {
            $validated['is_active'] = true;
        }

        $record = new $model();
        $record->forceFill($validated);
        $record->save();

        return redirect()
            ->route('admin.master-data.index', $type)
            ->with('success', 'Data ' . strtolower($config['title']) . ' berhasil ditambahkan.');
    }

    public function edit(string $type, int $id)
    {
        $config = $this->getConfig($type);
        $fields = $this->availableFields($config);
        $model = $config['model'];

        $item = $model::findOrFail($id);

        return view('admin.master-data.edit', compact('type', 'config', 'fields', 'item'));
    }

    public function update(Request $request, string $type, int $id)
    {
        $config = $this->getConfig($type);
        $fields = $this->availableFields($config);
        $model = $config['model'];

        $item = $model::findOrFail($id);

        $validated = $request->validate(
            $this->validationRules($config, $fields, $item->id)
        );

        if (Schema::hasColumn($config['table'], 'is_active') && !array_key_exists('is_active', $validated)) {
            $validated['is_active'] = true;
        }

        $item->forceFill($validated);
        $item->save();

        return redirect()
            ->route('admin.master-data.index', $type)
            ->with('success', 'Data ' . strtolower($config['title']) . ' berhasil diperbarui.');
    }

    public function destroy(string $type, int $id)
    {
        $config = $this->getConfig($type);
        $model = $config['model'];

        $item = $model::findOrFail($id);

        try {
            $item->delete();

            return redirect()
                ->route('admin.master-data.index', $type)
                ->with('success', 'Data ' . strtolower($config['title']) . ' berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()
                ->route('admin.master-data.index', $type)
                ->with('error', 'Data tidak dapat dihapus karena sudah digunakan pada data lain.');
        }
    }
}