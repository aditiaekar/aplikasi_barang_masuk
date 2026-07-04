<?php

namespace App\Service;

use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Repositories\MasterDataRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class MasterDataService
{
    public function __construct(
        private readonly MasterDataRepository $repository
    ) {}

    public function getConfig(string $type): array
    {
        $configs = $this->configs();

        abort_if(! array_key_exists($type, $configs), 404);

        return $configs[$type];
    }

    public function getFields(array $config): array
    {
        return collect($config['fields'])
            ->filter(fn (array $field) => Schema::hasColumn($config['table'], $field['name']))
            ->values()
            ->all();
    }

    public function paginate(string $type, ?string $search = null): LengthAwarePaginator
    {
        $config = $this->getConfig($type);
        $fields = $this->getFields($config);
        $searchableFields = collect($fields)
            ->filter(fn (array $field) => $field['searchable'] ?? false)
            ->pluck('name')
            ->values()
            ->all();

        return $this->repository->paginate(
            $config['model'],
            $searchableFields,
            $search
        );
    }

    public function findOrFail(string $type, int $id): Model
    {
        $config = $this->getConfig($type);

        return $this->repository->findOrFail($config['model'], $id);
    }

    public function store(Request $request, string $type): Model
    {
        $config = $this->getConfig($type);
        $fields = $this->getFields($config);
        $validated = $request->validate($this->validationRules($config, $fields));

        return $this->repository->create(
            $config['model'],
            $this->withDefaultStatus($config, $validated)
        );
    }

    public function update(Request $request, string $type, int $id): Model
    {
        $config = $this->getConfig($type);
        $fields = $this->getFields($config);
        $model = $this->repository->findOrFail($config['model'], $id);
        $validated = $request->validate(
            $this->validationRules($config, $fields, (int) $model->getKey())
        );

        return $this->repository->update(
            $model,
            $this->withDefaultStatus($config, $validated)
        );
    }

    public function destroy(string $type, int $id): bool
    {
        $config = $this->getConfig($type);
        $model = $this->repository->findOrFail($config['model'], $id);

        return $this->repository->delete($model);
    }

    private function withDefaultStatus(array $config, array $validated): array
    {
        if (Schema::hasColumn($config['table'], 'is_active') &&
            ! array_key_exists('is_active', $validated)) {
            $validated['is_active'] = true;
        }

        return $validated;
    }

    private function validationRules(array $config, array $fields, ?int $ignoreId = null): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $fieldRules = [($field['required'] ?? false) ? 'required' : 'nullable'];

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

                if ($ignoreId !== null) {
                    $uniqueRule->ignore($ignoreId);
                }

                $fieldRules[] = $uniqueRule;
            }

            $rules[$field['name']] = $fieldRules;
        }

        return $rules;
    }

    private function configs(): array
    {
        return [
            'categories' => config('master-data.categories'),
            'units' => [
                'title' => 'Satuan',
                'subtitle' => 'Kelola data satuan barang.',
                'table' => 'units',
                'model' => Unit::class,
                'icon' => 'bx-ruler',
                'fields' => [
                    ['name' => 'code', 'label' => 'Kode Satuan', 'type' => 'text', 'required' => false, 'unique' => true, 'searchable' => true, 'placeholder' => 'Contoh: PCS'],
                    ['name' => 'name', 'label' => 'Nama Satuan', 'type' => 'text', 'required' => true, 'searchable' => true, 'placeholder' => 'Contoh: Pieces'],
                    ['name' => 'symbol', 'label' => 'Simbol', 'type' => 'text', 'required' => false, 'searchable' => true, 'placeholder' => 'Contoh: pcs'],
                    ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'required' => false, 'searchable' => true, 'placeholder' => 'Masukkan deskripsi satuan'],
                    ['name' => 'is_active', 'label' => 'Status', 'type' => 'select_status', 'required' => false],
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
                    ['name' => 'address', 'label' => 'Alamat', 'type' => 'textarea', 'required' => false, 'searchable' => true, 'placeholder' => 'Masukkan alamat gudang'],
                    ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'required' => false, 'searchable' => true, 'placeholder' => 'Masukkan deskripsi gudang'],
                    ['name' => 'is_active', 'label' => 'Status', 'type' => 'select_status', 'required' => false],
                ],
            ],
        ];
    }
}
