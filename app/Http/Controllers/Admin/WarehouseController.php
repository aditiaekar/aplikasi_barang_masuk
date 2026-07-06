<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warehouse;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    private const TYPE = 'warehouses';

    public function data(Request $request): JsonResponse
    {
        $config = $this->config();
        $searchableFields = collect($config['fields'])
            ->filter(fn(array $field) => $field['searchable'] ?? false)
            ->pluck('name');

        $query = Warehouse::query()
            ->when($request->filled('keyword'), function ($query) use ($request, $searchableFields) {
                $keyword = $request->string('keyword')->toString();

                $query->where(function ($query) use ($keyword, $searchableFields) {
                    foreach ($searchableFields as $field) {
                        $query->orWhere($field, 'like', "%{$keyword}%");
                    }
                });
            });

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn(
                'code',
                fn(Warehouse $warehouse) =>
                '<span class="master-code">' . e($warehouse->code ?: '-') . '</span>'
                )
                ->editColumn(
                'is_active',
                fn(Warehouse $warehouse) =>
                $warehouse->is_active
                    ? '<span class="badge-active">Aktif</span>'
                    : '<span class="badge-inactive">Nonaktif</span>'
            )
            ->addColumn(
                'action',
                fn(Warehouse $warehouse) =>
                view('admin.master-data.partials.actions', [
                    'editRoute' => route('admin.warehouses.edit', $warehouse),
                    'destroyRoute' => route('admin.warehouses.destroy', $warehouse),
                ])->render()
            )
            ->rawColumns(['code', 'is_active', 'action'])
            ->toJson();
    }

    public function index()
    {
        $config = array_merge($this->config(), [
            'createRoute' => route('admin.warehouses.create'),
            'dataRoute' => route('admin.warehouses.data'),
        ]);

        return view('admin.master-data.index', compact('config'));
    }

    public function create()
    {
        $type = self::TYPE;
        $config = $this->config();
        $fields = $config['fields'];
        $storeRoute = route('admin.warehouses.store');
        $indexRoute = route('admin.warehouses.index');

        return view('admin.master-data.create', compact(
            'type',
            'config',
            'fields',
            'storeRoute',
            'indexRoute'
        ));
    }

    public function store(Request $request)
    {
        $config = $this->config();
        $data = $request->validate($config['rules']);

        Warehouse::create($data);

        return redirect()
            ->route('admin.warehouses.index')
            ->with('success', 'Data Gudang berhasil ditambahkan');
    }

    public function show(Warehouse $warehouse)
    {
        return redirect()->route('admin.warehouses.edit', $warehouse);
    }

    public function edit(Warehouse $warehouse)
    {
        $type = self::TYPE;
        $config = $this->config();
        $fields = $config['fields'];
        $item = $warehouse;
        $updateRoute = route('admin.warehouses.update',$warehouse);
        $indexRoute = route('admin.warehouses.index');

        return view('admin.master-data.edit', compact(
            'type',
            'config',
            'fields',
            'item',
            'updateRoute',
            'indexRoute',
        ));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $rules = $this->config()['rules'];
        $rules['code'] = [
            'nullable',
            'string',
            'max:255',
            Rule::unique('warehouses', 'code')->ignore($warehouse),
        ];

        $warehouse->update($request->validate($rules));

        return redirect()
            ->route('admin.warehouses.index')
            ->with('success', 'Data Gudang berhasil diperbarui');
    }

    public function destroy(Warehouse $warehouse)
    {
        try {
            $warehouse->delete();

            return redirect()
                ->route('admin.warehouses.index')
                ->with('success', 'Data Gudang berhasil dihapus.');
        } catch (QueryException) {
            return redirect()
                ->route('admin.warehouses.index')
                ->with('error', 'Gudang tidak dapat dihapus karena sudah digunakan.');
        }
    }

    private function config(): array
    {
        return config('master-data.' . self::TYPE);
    }
}
