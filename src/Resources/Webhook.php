<?php

namespace Jayik\Laravel\Resources;

/** Manage where Jayik sends order events. */
class Webhook extends Resource
{
    /**
     * Current settings: `webhook` (URL), `secret` (its `value` is the signing
     * secret, put it in JAYIK_WEBHOOK_SECRET) and `signature` (scheme details).
     */
    public function get(): array
    {
        return $this->client->get('v2/webhook');
    }

    /** The signing secret (whsec_...). */
    public function secret(): ?string
    {
        return $this->get()['secret']['value'] ?? null;
    }

    /**
     * Set the URL that receives events. Must be https://.
     * Call get() afterwards if you need to confirm the saved value.
     */
    public function set(string $url): array
    {
        return (array) $this->client->put('v2/webhook', ['webhook' => $url]);
    }

    /** Stop receiving events. */
    public function remove(): array
    {
        return (array) $this->client->put('v2/webhook', ['webhook' => null]);
    }

    /**
     * Ask Jayik to send a test event to your URL: real data when $tr is
     * given, sample data otherwise. $operation defaults to "created"
     * (see Support\WebhookOperation). Returns what was dispatched.
     */
    public function test(?string $tr = null, ?string $operation = null): array
    {
        return $this->client->post('v2/webhook/test', array_filter([
            'tr' => $tr,
            'operation' => $operation,
        ], function ($value) {
            return $value !== null;
        }));
    }

    /** Re-send a `created` event for the order to your URL. */
    public function redeliver(string $tr): array
    {
        return $this->client->post('v2/webhook/deliveries/'.rawurlencode($tr));
    }
}
