<?php

namespace Jayik\Laravel\Resources;

use Jayik\Laravel\Http\PaginatedResponse;

class Governorates extends Resource
{
    public function list(int $take = 10, int $page = 1): PaginatedResponse
    {
        return $this->paginate('v2/governorates', ['take' => $take, 'page' => $page]);
    }

    /**
     * Every governorate (id, name, number). Reference data: cache it on your side.
     *
     * @return array<int, array>
     */
    public function all(): array
    {
        return $this->lazyPages('v2/governorates', ['take' => 50])->all();
    }

    public function get(string $id): array
    {
        return $this->client->get("v2/governorates/{$id}");
    }
}
