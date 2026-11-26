<?php

namespace Jayik\Laravel\Resources;

use Jayik\Laravel\Http\PaginatedResponse;

class ApiKeys extends Resource
{
    /** Keys without their values (values are only shown on create/regenerate). */
    public function list(int $take = 10, int $page = 1): PaginatedResponse
    {
        return $this->paginate('v2/api-keys', ['take' => $take, 'page' => $page]);
    }

    public function get(string $id): array
    {
        return $this->client->get("v2/api-keys/{$id}");
    }

    /**
     * Create a key. The `key` field of the result is shown only this once:
     * store it securely.
     */
    public function create(?string $description = null): array
    {
        return $this->client->post('v2/api-keys', ['description' => $description]);
    }

    /**
     * Issue a new value for a key. The old value stops working immediately;
     * the new one is in the result's `key` field, shown only this once.
     */
    public function regenerate(string $id): array
    {
        return $this->client->post('v2/api-keys/regenerate', ['id' => $id]);
    }

    /**
     * Switch a key on or off. Pass a description to change it too.
     */
    public function update(string $id, bool $active, ?string $description = null): array
    {
        $body = ['active' => (int) $active];

        if ($description !== null) {
            $body['description'] = $description;
        }

        return $this->client->put("v2/api-keys/{$id}", $body);
    }

    public function delete(string $id): array
    {
        return $this->client->delete("v2/api-keys/{$id}");
    }
}
