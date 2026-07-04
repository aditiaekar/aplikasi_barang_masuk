<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    private const TYPE = 'suppliers';

    public function data(Request $request): JsonResponse
    {
        $config = $this->config();
        $searchableFields = collect($config['fields'])
            ->filter(fn(array $field) => $field['searchable'] ?? false)
            ->pluck('name');

        $query = Supplier::query()
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
                fn(Supplier $supplier) =>
                '<span class="master-code">' . e($supplier->code ?: '-') . '</span>'
            )
            ->editColumn(
                'is_active',
                fn(Supplier $supplier) =>
                $supplier->is_active
                    ? '<span class="badge-active">Aktif</span>'
                    : '<span class="badge-inactive">Nonaktif</span>'
            )
            ->addColumn(
                'action',
                fn(Supplier $supplier) =>
                view('admin.master-data.partials.actions', [
                    'editRoute' => route('admin.suppliers.edit', $supplier),
                    'destroyRoute' => route('admin.suppliers.destroy', $supplier),
                ])->render()
            )
            ->rawColumns(['code', 'is_active', 'action'])
            ->toJson();
    }

    public function index()
    {
        $config = array_merge($this->config(), [
            'createRoute' => route('admin.suppliers.create'),
            'dataRoute' => route('admin.suppliers.data'),
        ]);

        return view('admin.master-data.index', compact('config'));
    }

    public function create()
    {
        $type = self::TYPE;
        $config = $this->config();
        $fields = $config['fields'];
        $storeRoute = route('admin.suppliers.store');
        $indexRoute = route('admin.suppliers.index');

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
        $data = $request->validated($config['rules']);

        Supplier::create($data);

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Data Supplier berhasil ditambahkan');
    }

    public function show(Supplier $supplier)
    {
        return redirect()->route('admin.suppliers.edit', $supplier);
    }

    public function edit(Supplier $supplier)
    {
        $type = self::TYPE;
        $config = $this->config();
        $fields = $config['fields'];
        $item = $supplier;
        $storeRoute = route('admin.suppliers.store');
        $indexRoute = route('admin.suppliers.index');

        return view('admin.master-data.create', compact(
            'type',
            'config',
            'fields',
            'storeRoute',
            'indexRoute',
            'item'
        ));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $rules = $this->config()['rules'];
        $rules['code'] = [
            'nullable',
            'string',
            'max:255',
            Rule::unique('suppliers', 'code')->ignore($supplier),
        ];

        $supplier->updated($request->validate($rules));

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Data Supplier berhasil diperbarui');
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();

            return redirect()
                ->route('admin.suppliers.index')
                ->with('success', 'Data Supplier berhasil dihapus.');
        } catch (QueryException) {
            return redirect()
                ->route('admin.suppliers.index')
                ->with('error', 'Supplier tidak dapat dihapus karena sudah digunakan.');
        }
    }

    private function config(): array
    {
        return config('master-data.' . self::TYPE);
    }
}
