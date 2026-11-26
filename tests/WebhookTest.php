<?php

namespace Jayik\Laravel\Tests;

use Illuminate\Support\Facades\Event;
use Jayik\Laravel\Webhooks\SignatureVerifier;
use Jayik\Laravel\Webhooks\WebhookReceived;

class WebhookTest extends TestCase
{
    protected function deliver(string $body, array $headers)
    {
        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];

        foreach ($headers as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return $this->call('POST', '/jayik/webhook', [], [], [], $server, $body);
    }

    public function test_signature_matches_the_documented_scheme()
    {
        $verifier = new SignatureVerifier('whsec_test');
        $body = '{"opration":"updated","data":{"tr":"100"}}';

        $this->assertSame(
            'sha256='.hash_hmac('sha256', '1700000000.'.$body, 'whsec_test'),
            $verifier->sign($body, 1700000000)
        );
    }

    public function test_a_signed_webhook_dispatches_the_event()
    {
        Event::fake([WebhookReceived::class]);

        $body = '{"opration":"updated","data":{"tr":"100","status":{"name":"Delivered"},"driver":{"id":null,"name":null,"phone":null}}}';
        $timestamp = time();

        $this->deliver($body, [
            'X-Jayik-Timestamp' => (string) $timestamp,
            'X-Jayik-Signature' => (new SignatureVerifier('whsec_test'))->sign($body, $timestamp),
        ])->assertStatus(200)->assertJson(['received' => true]);

        Event::assertDispatched(WebhookReceived::class, function (WebhookReceived $event) {
            return $event->webhook->isUpdated()
                && $event->webhook->tr() === '100'
                && $event->webhook->status() === 'Delivered';
        });
    }

    public function test_a_tampered_body_is_rejected()
    {
        Event::fake([WebhookReceived::class]);

        $timestamp = time();

        $this->deliver('{"opration":"updated","data":{"tr":"999"}}', [
            'X-Jayik-Timestamp' => (string) $timestamp,
            'X-Jayik-Signature' => (new SignatureVerifier('whsec_test'))->sign('{"opration":"updated","data":{"tr":"100"}}', $timestamp),
        ])->assertStatus(401);

        Event::assertNotDispatched(WebhookReceived::class);
    }

    public function test_missing_headers_and_old_timestamps_are_rejected()
    {
        $body = '{"opration":"created","data":{}}';
        $old = time() - 301;

        $this->deliver($body, [])->assertStatus(401);

        $this->deliver($body, [
            'X-Jayik-Timestamp' => (string) $old,
            'X-Jayik-Signature' => (new SignatureVerifier('whsec_test'))->sign($body, $old),
        ])->assertStatus(401);
    }

    public function test_nothing_verifies_without_a_configured_secret()
    {
        $verifier = new SignatureVerifier(null);
        $body = '{}';

        $this->assertFalse($verifier->isValid($body, $verifier->sign($body, time()), (string) time()));
    }
}
