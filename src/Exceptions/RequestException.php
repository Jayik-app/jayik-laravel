<?php

namespace Jayik\Laravel\Exceptions;

use Illuminate\Http\Client\Response;

/**
 * Any non-2xx answer from the API. Error bodies look like
 * {"status": false, "message": "..." | {"field": ["..."]}, "data": []}.
 */
class RequestException extends JayikException
{
    /** @var Response */
    public $response;

    /** @var array<string, array<int, string>> */
    protected $errors = [];

    protected const STATUS_MAP = [
        400 => ValidationException::class,
        401 => AuthenticationException::class,
        403 => PermissionDeniedException::class,
        404 => NotFoundException::class,
        409 => ConflictException::class,
        422 => UnprocessableException::class,
        429 => RateLimitException::class,
    ];

    final public function __construct(string $message, Response $response)
    {
        parent::__construct($message, $response->status());

        $this->response = $response;
    }

    public static function fromResponse(Response $response): self
    {
        $message = $response->json('message');
        $errors = [];

        if (is_array($message)) {
            foreach ($message as $field => $messages) {
                $errors[$field] = array_values((array) $messages);
            }

            $first = reset($errors);
            $message = $first ? $first[0] : 'The given data was invalid.';
        }

        if (! is_string($message) || $message === '') {
            $message = 'Jayik API request failed with status '.$response->status().'.';
        }

        $class = self::STATUS_MAP[$response->status()] ?? self::class;

        $exception = new $class($message, $response);
        $exception->errors = $errors;

        return $exception;
    }

    public function status(): int
    {
        return $this->response->status();
    }

    /**
     * Field => messages, filled for validation failures.
     *
     * @return array<string, array<int, string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
