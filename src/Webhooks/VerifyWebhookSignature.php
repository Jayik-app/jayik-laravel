<?php

namespace Jayik\Laravel\Webhooks;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jayik\Laravel\Exceptions\InvalidSignatureException;

/**
 * Route middleware (alias: jayik.webhook) that rejects requests not signed by Jayik.
 */
class VerifyWebhookSignature
{
    /** @var SignatureVerifier */
    protected $verifier;

    public function __construct(SignatureVerifier $verifier)
    {
        $this->verifier = $verifier;
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            $this->verifier->verify(
                (string) $request->getContent(),
                $request->header(SignatureVerifier::SIGNATURE_HEADER),
                $request->header(SignatureVerifier::TIMESTAMP_HEADER)
            );
        } catch (InvalidSignatureException $e) {
            return new JsonResponse(['message' => 'Invalid signature.'], 401);
        }

        return $next($request);
    }
}
