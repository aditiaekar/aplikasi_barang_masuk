<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    private const TYPE = 'categories';

    public function data(Request $request): JsonResponse
    {
        $config = $this->config();
        $searchableFields = collect($config['fields'])
            ->filter(fn (array $field) => $field['searchable'] ?? false)
            ->pluck('name');

        $query = Category::query()
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
                fn(Category $category) =>
                '<span class="master-code">' . e($category->code ?: '-') . '</span>'
            )
            ->editColumn(
                'is_active',
                fn(Category $category) =>
                $category->is_active
                    ? '<span class="badge-active">Aktif</span>'
                    : '<span class="badge-inactive">Nonaktif</span>'
            )
            ->addColumn(
                'action',
                fn(Category $category) =>
                view('admin.master-data.partials.actions', [
                    'editRoute' => route('admin.categories.edit', $category),
                    'destroyRoute' => route('admin.categories.destroy', $category),
                ])->render()
            )
            ->rawColumns(['code', 'is_active', 'action'])
            ->toJson();
    }

    public function index(): View
    {
        $config = array_merge($this->config(), [
            'createRoute' => route('admin.categories.create'),
            'dataRoute' => route('admin.categories.data'),
        ]);

        return view('admin.master-data.index', compact('config'));
    }

    public function create(): View
    {
        $type = self::TYPE;
        $config = $this->config();
        $fields = $config['fields'];
        $storeRoute = route('admin.categories.store');
        $indexRoute = route('admin.categories.index');

        return view('admin.master-data.create', compact(
            'type',
            'config',
            'fields',
            'storeRoute',
            'indexRoute'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $config = $this->config();
        $data = $request->validate($config['rules']);

        Category::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Data kategori berhasil ditambahkan.');
    }

    public function show(Category $category): RedirectResponse
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    public function edit(Category $category): View
    {
        $type = self::TYPE;
        $config = $this->config();
        $fields = $config['fields'];
        $item = $category;
        $updateRoute = route('admin.categories.update', $category);
        $indexRoute = route('admin.categories.index');

        return view('admin.master-data.edit', compact(
            'type',
            'config',
            'fields',
            'item',
            'updateRoute',
            'indexRoute'
        ));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $rules = $this->config()['rules'];
        $rules['code'] = [
            'nullable',
            'string',
            'max:255',
            Rule::unique('categories', 'code')->ignore($category),
        ];

        $category->update($request->validate($rules));

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Data kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            $category->delete();

            return redirect()
                ->route('admin.categories.index')
                ->with('success', 'Data kategori berhasil dihapus.');
        } catch (QueryException) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena sudah digunakan.');
        }
    }

    private function config(): array
    {
        return config('master-data.'.self::TYPE);
    }
}
