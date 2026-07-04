<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Service\MasterDataService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function __construct(
        private readonly MasterDataService $service
    ) {}

    public function index(Request $request, string $type): View
    {
        $config = $this->service->getConfig($type);
        $fields = $this->service->getFields($config);
        $items = $this->service->paginate($type, $request->string('search')->toString());

        return view('admin.master-data.index', compact('type', 'config', 'fields', 'items'));
    }

    public function create(string $type): View
    {
        $config = $this->service->getConfig($type);
        $fields = $this->service->getFields($config);

        return view('admin.master-data.create', compact('type', 'config', 'fields'));
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $config = $this->service->getConfig($type);
        $this->service->store($request, $type);

        return redirect()
            ->route('admin.master-data.index', $type)
            ->with('success', 'Data '.strtolower($config['title']).' berhasil ditambahkan.');
    }

    public function edit(string $type, int $id): View
    {
        $config = $this->service->getConfig($type);
        $fields = $this->service->getFields($config);
        $item = $this->service->findOrFail($type, $id);

        return view('admin.master-data.edit', compact('type', 'config', 'fields', 'item'));
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $config = $this->service->getConfig($type);
        $this->service->update($request, $type, $id);

        return redirect()
            ->route('admin.master-data.index', $type)
            ->with('success', 'Data '.strtolower($config['title']).' berhasil diperbarui.');
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        $config = $this->service->getConfig($type);

        try {
            $this->service->destroy($type, $id);

            return redirect()
                ->route('admin.master-data.index', $type)
                ->with('success', 'Data '.strtolower($config['title']).' berhasil dihapus.');
        } catch (QueryException) {
            return redirect()
                ->route('admin.master-data.index', $type)
                ->with('error', 'Data tidak dapat dihapus karena sudah digunakan pada data lain.');
        }
    }
}
