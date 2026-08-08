<?php

namespace App\Services;

use App\Models\Router;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RouterService
{
    public function getList(
        int $perPage = 15
    ): LengthAwarePaginator
    {
        return Router::latest()
            ->paginate($perPage);
    }

    public function getDetail(
        int $id
    ): Router
    {
        return Router::findOrFail($id);
    }

    public function create(
        array $data
    ): Router
    {
        return Router::create($data);
    }

    public function update(
        int $id,
        array $data
    ): Router
    {
        $router = Router::findOrFail($id);

        $router->update($data);

        return $router->fresh();
    }

    public function delete(
        int $id
    ): void
    {
        Router::findOrFail($id)->delete();
    }
}