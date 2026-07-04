<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    private const TYPE = 'units';

    public function data(Request $request): JsonResponse
    {
        $config = $this->config();
        $searchableFields = collect($config['fields'])
            ->filter(fn(array $field) => $field['searchable'] ?? false)
            ->pluck('name');

        $query = Unit::query()
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
                fn(Unit $unit) =>
                '<span class="master-code">' . e($unit->code ?: '-') . '</span>'
            )
            ->editColumn(
                'is_active',
                fn(Unit $unit) =>
                $unit->is_active
                    ? '<span class="badge-active">Aktif</span>'
                    : '<span class="badge-inactive">Nonaktif</span>'
            )
            ->addColumn(
                'action',
                fn(Unit $unit) =>
                view('admin.master-data.partials.actions', [
                    'editRoute' => route('admin.units.edit', $unit),
                    'destroyRoute' => route('admin.units.destroy', $unit),
                ])->render()
            )
            ->rawColumns(['code', 'is_active', 'action'])
            ->toJson();
    }

    public function index()
    {
        $config = array_merge($this->config(), [
            'createRoute' => route('admin.units.create'),
            'dataRoute' => route('admin.units.data'),
        ]);

        return view('admin.master-data.index',compact('config'));
    }

    public function create()
    {
        $type = self::TYPE;
        $config = $this->config();
        $fields = $config['fields'];
        $storeRoute = route('admin.units.store');
        $indexRoute = route('admin.units.index');

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

        Unit::create($data);

        return redirect()
            ->route('admin.units.index')
            ->with('success','Data Unit berhasil ditambahkan');
    }

    public function show(Unit $unit)
    {
        return redirect()->route('admin.units.edit',$unit);
    }

    public function edit(Unit $unit)
    {
        $type = self::TYPE;
        $config = $this->config();
        $fields = $config['fields'];
        $item = $unit;
        $storeRoute = route('admin.units.store');
        $indexRoute = route('admin.units.index');

        return view('admin.master-data.create', compact(
            'type',
            'config',
            'fields',
            'storeRoute',
            'indexRoute',
            'item'
        ));
    }

    public function update(Request $request, Unit $unit)
    {
        $rules = $this->config()['rules'];
        $rules['code'] = [
            'nullable',
            'string',
            'max:255',
            Rule::unique('units','code')->ignore($unit),
        ];

        $unit->updated($request->validate($rules));

        return redirect()
            ->route('admin.units.index')
            ->with('success','Data unit berhasil diperbarui');
    }

    public function destroy(Unit $unit)
    {
        try {
            $unit->delete();

            return redirect()
                ->route('admin.units.index')
                ->with('success', 'Data Unit berhasil dihapus.');
        } catch (QueryException) {
            return redirect()
                ->route('admin.units.index')
                ->with('error', 'Unit tidak dapat dihapus karena sudah digunakan.');
        }
    }

    private function config(): array
    {
        return config('master-data.' . self::TYPE);
    }
}
