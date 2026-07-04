<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class MasterDataRepository
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, string>  $searchableFields
     */
    public function paginate(
        string $modelClass,
        array $searchableFields,
        ?string $search = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        return $modelClass::query()
            ->when($search, function ($query, string $search) use ($searchableFields) {
                $query->where(function ($query) use ($search, $searchableFields) {
                    foreach ($searchableFields as $field) {
                        $query->orWhere($field, 'like', "%{$search}%");
                    }
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /** @param class-string<Model> $modelClass */
    public function findOrFail(string $modelClass, int $id): Model
    {
        return $modelClass::query()->findOrFail($id);
    }

    /** @param class-string<Model> $modelClass */
    public function create(string $modelClass, array $data): Model
    {
        $model = new $modelClass;
        $model->forceFill($data);
        $model->save();

        return $model;
    }

    public function update(Model $model, array $data): Model
    {
        $model->forceFill($data);
        $model->save();

        return $model;
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }
}
