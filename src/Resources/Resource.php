<?php

namespace Jayik\Laravel\Resources;

use Illuminate\Support\LazyCollection;
use Jayik\Laravel\Http\Client;
use Jayik\Laravel\Http\PaginatedResponse;

abstract class Resource
{
    /** @var Client */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    protected function paginate(string $uri, array $query = []): PaginatedResponse
    {
        return new PaginatedResponse((array) $this->client->get($uri, $query));
    }

    /**
     * Walk every page lazily, one request per page.
     */
    protected function lazyPages(string $uri, array $query = []): LazyCollection
    {
        return LazyCollection::make(function () use ($uri, $query) {
            $query['take'] = $query['take'] ?? 100;
            $query['page'] = (int) ($query['page'] ?? 1);

            do {
                $page = $this->paginate($uri, $query);

                foreach ($page->items() as $item) {
                    yield $item;
                }

                $query['page']++;
            } while (! $page->isEmpty() && $page->hasMorePages());
        });
    }
}
