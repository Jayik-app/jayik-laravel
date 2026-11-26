<?php

namespace Jayik\Laravel\Webhooks;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController
{
    public function __invoke(Request $request, Dispatcher $events): JsonResponse
    {
        $events->dispatch(new WebhookReceived(WebhookEvent::fromRequest($request)));

        return new JsonResponse(['received' => true]);
    }
}
