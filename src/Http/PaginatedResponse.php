<?php

namespace Jayik\Laravel\Http;

use ArrayIterator;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * One page of a list endpoint (Laravel-style page object).
 */
class PaginatedResponse implements Arrayable, Countable, IteratorAggregate, JsonSerializable
{
    /** @var array */
    protected $page;

    public function __construct(array $page)
    {
        $this->page = $page;
    }

    /** @return array<int, array> */
    public function items(): array
    {
        return array_values($this->page['data'] ?? []);
    }

    public function collect(): Collection
    {
        return new Collection($this->items());
    }

    public function currentPage(): int
    {
        return (int) ($this->page['current_page'] ?? 1);
    }

    public function perPage(): int
    {
        return (int) ($this->page['per_page'] ?? count($this->items()));
    }

    /** Null when the list was requested with `simple` (no total count). */
    public function total(): ?int
    {
        return isset($this->page['total']) ? (int) $this->page['total'] : null;
    }

    public function lastPage(): ?int
    {
        return isset($this->page['last_page']) ? (int) $this->page['last_page'] : null;
    }

    public function hasMorePages(): bool
    {
        $last = $this->lastPage();

        if ($last !== null) {
            return $this->currentPage() < $last;
        }

        if (array_key_exists('next_page_url', $this->page)) {
            return $this->page['next_page_url'] !== null;
        }

        return $this->perPage() > 0 && count($this->items()) >= $this->perPage();
    }

    public function isEmpty(): bool
    {
        return $this->items() === [];
    }

    public function count(): int
    {
        return count($this->items());
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items());
    }

    /** The raw page object as returned by the API. */
    public function toArray(): array
    {
        return $this->page;
    }

    public function jsonSerialize(): array
    {
        return $this->page;
    }
}
