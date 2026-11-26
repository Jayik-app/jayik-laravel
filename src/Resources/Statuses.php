<?php

namespace Jayik\Laravel\Resources;

use Jayik\Laravel\Http\PaginatedResponse;

class Statuses extends Resource
{
    public function list(int $take = 10, int $page = 1): PaginatedResponse
    {
        return $this->paginate('v2/statuses', ['take' => $take, 'page' => $page]);
    }

    /**
     * Every order status. `name` is the stable code (see Support\OrderStatus);
     * ids are UUIDs. Reference data: cache it on your side.
     *
     * @return array<int, array>
     */
    public function all(): array
    {
        return $this->lazyPages('v2/statuses', ['take' => 50])->all();
    }

    public function get(string $id): array
    {
        return $this->client->get("v2/statuses/{$id}");
    }
}
