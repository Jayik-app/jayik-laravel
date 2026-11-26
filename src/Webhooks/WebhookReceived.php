<?php

namespace Jayik\Laravel\Webhooks;

/**
 * Laravel event dispatched for every verified webhook hitting the package route.
 * Jayik expects a 2xx within 5 seconds, so make slow listeners queued.
 */
class WebhookReceived
{
    /** @var WebhookEvent */
    public $webhook;

    public function __construct(WebhookEvent $webhook)
    {
        $this->webhook = $webhook;
    }
}
