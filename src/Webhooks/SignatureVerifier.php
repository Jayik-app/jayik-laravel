<?php

namespace Jayik\Laravel\Webhooks;

use Jayik\Laravel\Exceptions\InvalidSignatureException;

/**
 * Jayik signs each webhook with HMAC-SHA256 over "{timestamp}.{raw body}"
 * and sends it as "X-Jayik-Signature: sha256={hex}" next to
 * "X-Jayik-Timestamp: {unix seconds}".
 */
class SignatureVerifier
{
    public const SIGNATURE_HEADER = 'X-Jayik-Signature';

    public const TIMESTAMP_HEADER = 'X-Jayik-Timestamp';

    /** @var string|null */
    protected $secret;

    /** @var int */
    protected $tolerance;

    /**
     * @param  int  $tolerance  Maximum age in seconds; 0 disables the age check.
     */
    public function __construct(?string $secret, int $tolerance = 300)
    {
        $this->secret = $secret;
        $this->tolerance = $tolerance;
    }

    public function sign(string $payload, int $timestamp): string
    {
        return 'sha256='.hash_hmac('sha256', $timestamp.'.'.$payload, (string) $this->secret);
    }

    /**
     * @param  string  $payload  The raw request body, before any JSON decoding.
     *
     * @throws InvalidSignatureException
     */
    public function verify(string $payload, ?string $signature, ?string $timestamp): void
    {
        if ($this->secret === null || $this->secret === '') {
            throw new InvalidSignatureException('Jayik webhook secret is not configured (JAYIK_WEBHOOK_SECRET).');
        }

        if ($signature === null || $signature === '' || $timestamp === null || $timestamp === '') {
            throw new InvalidSignatureException('Missing Jayik signature headers.');
        }

        if (! ctype_digit($timestamp)) {
            throw new InvalidSignatureException('Invalid Jayik webhook timestamp.');
        }

        if ($this->tolerance > 0 && abs(time() - (int) $timestamp) > $this->tolerance) {
            throw new InvalidSignatureException('Jayik webhook timestamp is outside the allowed tolerance.');
        }

        if (! hash_equals($this->sign($payload, (int) $timestamp), $signature)) {
            throw new InvalidSignatureException('Jayik webhook signature mismatch.');
        }
    }

    public function isValid(string $payload, ?string $signature, ?string $timestamp): bool
    {
        try {
            $this->verify($payload, $signature, $timestamp);
        } catch (InvalidSignatureException $e) {
            return false;
        }

        return true;
    }
}
