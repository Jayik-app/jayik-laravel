<?php

namespace Jayik\Laravel\Resources;

use Illuminate\Support\LazyCollection;
use Jayik\Laravel\Http\PaginatedResponse;

/**
 * Pickup points. In a marketplace, create one warehouse per vendor and pass
 * its id as `warehouse_id` when creating that vendor's orders.
 */
class Warehouses extends Resource
{
    public function list(int $take = 15, int $page = 1): PaginatedResponse
    {
        return $this->paginate('v2/warehouses', ['take' => $take, 'page' => $page]);
    }

    public function lazy(): LazyCollection
    {
        return $this->lazyPages('v2/warehouses');
    }

    public function get(string $id): array
    {
        return $this->client->get("v2/warehouses/{$id}");
    }

    /**
     * Required: name, address. Optional: lat, long, governorate_id (UUID or
     * number), phone, secondary_phone, availability_times_from/_to (HH:MM),
     * site_id.
     */
    public function create(array $warehouse): array
    {
        return $this->client->post('v2/warehouses', $warehouse);
    }

    /**
     * Same fields as create, all optional. Always send governorate_id:
     * omitting it clears the warehouse's governorate.
     */
    public function update(string $id, array $warehouse): array
    {
        return $this->client->put("v2/warehouses/{$id}", $warehouse);
    }

    public function delete(string $id): void
    {
        $this->client->delete("v2/warehouses/{$id}");
    }

    /** Set the warehouse used when an order is created without one. */
    public function setDefault(string $id): void
    {
        $this->client->put('v2/warehouses/default', ['default_warehouse_id' => $id]);
    }

    /**
     * One entry per day: id, warehouse_id, day, opens_at, closes_at, is_closed.
     *
     * @return array<int, array>
     */
    public function businessHours(string $id): array
    {
        return $this->client->get("v2/warehouses/{$id}/business-hours");
    }

    /**
     * Keyed by lowercase day name; days left out are unchanged.
     *
     *     ['sunday' => ['is_closed' => false, 'opens_at' => '09:00', 'closes_at' => '17:00'],
     *      'friday' => ['is_closed' => true]]
     *
     * @param  array<string, array>  $hours
     */
    public function updateBusinessHours(string $id, array $hours): void
    {
        $this->client->put("v2/warehouses/{$id}/business-hours", ['business_hours' => $hours]);
    }
}
