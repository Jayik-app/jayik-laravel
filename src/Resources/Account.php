<?php

namespace Jayik\Laravel\Resources;

class Account extends Resource
{
    /**
     * The merchant behind the API key.
     *
     * @return array{user: array, roles: array<int, string>, permissions: array<int, string>}
     */
    public function details(): array
    {
        return $this->client->get('auth/details');
    }

    /** @return array<int, string> */
    public function permissions(): array
    {
        return $this->details()['permissions'] ?? [];
    }
}
