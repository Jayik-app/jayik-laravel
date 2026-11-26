<?php

namespace Jayik\Laravel\Resources;

use Illuminate\Support\LazyCollection;
use Jayik\Laravel\Http\PaginatedResponse;

class OrderCategories extends Resource
{
    public function list(int $take = 10, int $page = 1): PaginatedResponse
    {
        return $this->paginate('v2/order-categories', ['take' => $take, 'page' => $page]);
    }

    public function lazy(): LazyCollection
    {
        return $this->lazyPages('v2/order-categories');
    }

    public function get(string $id): array
    {
        return $this->client->get("v2/order-categories/{$id}");
    }

    public function create(string $name): array
    {
        return $this->client->post('v2/order-categories', ['name' => $name]);
    }

    public function update(string $id, string $name): array
    {
        return $this->client->put("v2/order-categories/{$id}", ['name' => $name]);
    }

    public function delete(string $id): void
    {
        $this->client->delete("v2/order-categories/{$id}");
    }

    /** Set the category used as the account's default. */
    public function setDefault(string $id): void
    {
        $this->client->put('v2/order-categories/default', ['default_order_category_id' => $id]);
    }
}
