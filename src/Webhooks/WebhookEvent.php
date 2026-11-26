<?php

namespace Jayik\Laravel\Webhooks;

use Illuminate\Http\Request;
use Jayik\Laravel\Support\WebhookOperation;

/**
 * A parsed webhook body: {"opration": "...", "data": {order record + driver}}.
 * (The API spells the key "opration".)
 */
class WebhookEvent
{
    /** @var string One of the Support\WebhookOperation constants. */
    public $operation;

    /** @var array The order record, plus a `driver` object. */
    public $order;

    /** @var array The full decoded body. */
    public $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->operation = (string) ($payload['opration'] ?? $payload['operation'] ?? '');
        $this->order = (array) ($payload['data'] ?? []);
    }

    public static function fromRequest(Request $request): self
    {
        return new static((array) json_decode((string) $request->getContent(), true));
    }

    public function is(string $operation): bool
    {
        return $this->operation === $operation;
    }

    public function isCreated(): bool
    {
        return $this->is(WebhookOperation::CREATED);
    }

    public function isUpdated(): bool
    {
        return $this->is(WebhookOperation::UPDATED);
    }

    public function isAssigned(): bool
    {
        return $this->is(WebhookOperation::ASSIGNED);
    }

    public function isDeleted(): bool
    {
        return $this->is(WebhookOperation::DELETED) || $this->is(WebhookOperation::FORCE_DELETED);
    }

    /** The order's tracking number. */
    public function tr(): ?string
    {
        return isset($this->order['tr']) ? (string) $this->order['tr'] : null;
    }

    /** The order's status code, e.g. "Delivered" (see Support\OrderStatus). */
    public function status(): ?string
    {
        $status = $this->order['status'] ?? null;

        if (is_array($status)) {
            return $status['name'] ?? null;
        }

        return is_string($status) ? $status : null;
    }

    /** Driver id/name/phone; values are null unless enabled for the account. */
    public function driver(): array
    {
        return (array) ($this->order['driver'] ?? []);
    }
}
