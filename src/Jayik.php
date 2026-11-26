<?php

namespace Jayik\Laravel;

use Jayik\Laravel\Http\Client;
use Jayik\Laravel\Resources\Account;
use Jayik\Laravel\Resources\ApiKeys;
use Jayik\Laravel\Resources\Attributes;
use Jayik\Laravel\Resources\Governorates;
use Jayik\Laravel\Resources\OrderCategories;
use Jayik\Laravel\Resources\Orders;
use Jayik\Laravel\Resources\Statuses;
use Jayik\Laravel\Resources\Warehouses;
use Jayik\Laravel\Resources\Webhook;

class Jayik
{
    /** @var Client */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * A new instance that authenticates with another merchant's API key.
     * Useful for marketplaces where each vendor has their own Jayik account.
     *
     * @return static
     */
    public function withKey(string $apiKey): self
    {
        return new static($this->client->withKey($apiKey));
    }

    public function client(): Client
    {
        return $this->client;
    }

    public function account(): Account
    {
        return new Account($this->client);
    }

    public function orders(): Orders
    {
        return new Orders($this->client);
    }

    public function warehouses(): Warehouses
    {
        return new Warehouses($this->client);
    }

    public function orderCategories(): OrderCategories
    {
        return new OrderCategories($this->client);
    }

    public function governorates(): Governorates
    {
        return new Governorates($this->client);
    }

    public function statuses(): Statuses
    {
        return new Statuses($this->client);
    }

    /** Product types used in order_items[].attribute_id. */
    public function attributes(): Attributes
    {
        return new Attributes($this->client);
    }

    public function webhook(): Webhook
    {
        return new Webhook($this->client);
    }

    public function apiKeys(): ApiKeys
    {
        return new ApiKeys($this->client);
    }
}
