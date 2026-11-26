<?php

namespace Jayik\Laravel\Resources;

use Illuminate\Support\LazyCollection;
use Jayik\Laravel\Http\PaginatedResponse;

class Orders extends Resource
{
    /**
     * List orders.
     *
     * Query keys: take (default 15, max 500), page, from + to (dates, filter on
     * last update, send together), simple (bool, skips the total count),
     * sort (e.g. "-created_at"), include (array or CSV of: status, customer,
     * governorate, district, warehouse, orderCategory, orderItems,
     * orderItems.attribute, merchant, history.merchant, driverRate,
     * amountChanges, financial) and filter (array with: tr, uuid, status_id,
     * not_status_id, governorate_id, warehouse_id, order_category_id,
     * delivery_type, customer.phone, customer.name, customer.address, amount).
     *
     *     Jayik::orders()->list([
     *         'filter' => ['warehouse_id' => $id, 'status_id' => [$a, $b]],
     *         'include' => ['status', 'customer'],
     *         'sort' => '-created_at',
     *     ]);
     */
    public function list(array $query = []): PaginatedResponse
    {
        return $this->paginate('v2/orders', $query);
    }

    /**
     * Iterate every matching order across all pages (one request per page).
     */
    public function lazy(array $query = []): LazyCollection
    {
        return $this->lazyPages('v2/orders', $query);
    }

    /**
     * Fetch one order by its numeric id.
     *
     * @param  array<int, string>|string  $include
     */
    public function get(int $id, $include = []): array
    {
        return $this->client->get("v2/orders/{$id}", ['include' => $include]);
    }

    /**
     * Fetch one order by its tracking number, or null when none matches.
     *
     * @param  array<int, string>|string  $include
     */
    public function findByTr(string $tr, $include = []): ?array
    {
        return $this->list([
            'filter' => ['tr' => $tr],
            'include' => $include,
            'take' => 1,
            'simple' => true,
        ])->items()[0] ?? null;
    }

    /**
     * Create one order (status Pending). Returns the order record.
     *
     * Required: customer_phone, customer_address, amount (IQD, 0 for prepaid),
     * order_category_id, governorate_id (UUID or governorate number).
     * Optional: tr, warehouse_id, customer_name, district_id, delivery_type
     * (0 normal, 1 fawri), notes, business_name, destination_coordinate
     * ("lat,lng"), total_products, scheduled_delivery_date_from/_to,
     * invoice_url, order_items[] (attribute_id, name, weight, unit, count).
     */
    public function create(array $order): array
    {
        return $this->client->post('v2/orders', $order);
    }

    /**
     * Create 1–300 orders, all-or-none. The API returns no order data;
     * look the orders up afterwards by their `tr`.
     *
     * Each order requires: tr, customer_name, customer_phone,
     * customer_address, amount, order_category_id, governorate_id (UUID only).
     *
     * @param  array<int, array>  $orders
     */
    public function createBatch(array $orders): void
    {
        $this->client->post('v2/orders/batch', ['orders' => array_values($orders)]);
    }

    /** Ask the API for an unused tracking number. */
    public function generateTr(): string
    {
        return (string) $this->client->get('v2/orders/generate-tr')['tr_code'];
    }

    public function trExists(string $tr): bool
    {
        return (bool) ($this->client->get('v2/orders/tr-exists/'.rawurlencode($tr))['exists'] ?? false);
    }

    /** Cancel an order. Only while Pending, AwaitingPickup or PickupDelayed. */
    public function cancel(string $tr): array
    {
        return $this->client->put('v2/orders/cancel', ['tr' => $tr]);
    }

    /** Move a Pending order to AwaitingPickup. */
    public function readyToPickup(string $tr): array
    {
        return $this->client->put('v2/orders/ready-to-pickup', ['tr' => $tr]);
    }

    /** Permanently delete an order by numeric id. Only while Pending. */
    public function delete(int $id): array
    {
        return $this->client->delete("v2/orders/{$id}");
    }
}
