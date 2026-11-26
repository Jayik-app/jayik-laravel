<?php

namespace Jayik\Laravel\Exceptions;

/** 429 — rate limit (120 requests/minute per account) reached. */
class RateLimitException extends RequestException
{
    /** Seconds to wait before retrying, from the Retry-After header. */
    public function retryAfter(): ?int
    {
        $value = $this->response->header('Retry-After');

        return is_numeric($value) ? (int) $value : null;
    }
}
