<?php

namespace Jayik\Laravel\Resources;

use Jayik\Laravel\Http\PaginatedResponse;

/** Product types; their ids feed order_items[].attribute_id. */
class Attributes extends Resource
{
    public function list(int $take = 15, int $page = 1): PaginatedResponse
    {
        return $this->paginate('v2/attributes', ['take' => $take, 'page' => $page]);
    }

    /** @return array<int, array> */
    public function all(): array
    {
        return $this->lazyPages('v2/attributes')->all();
    }
}
