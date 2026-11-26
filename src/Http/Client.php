<?php

namespace Jayik\Laravel\Http;

use DateTimeInterface;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Jayik\Laravel\Exceptions\JayikException;
use Jayik\Laravel\Exceptions\RequestException;

class Client
{
    /**
     * Replace the wait between rate-limit retries (receives seconds). Mainly for tests.
     *
     * @var callable|null
     */
    public static $sleepUsing;

    /** @var HttpFactory */
    protected $http;

    /** @var array */
    protected $config;

    public function __construct(HttpFactory $http, array $config)
    {
        $this->http = $http;
        $this->config = $config;
    }

    /** @return static */
    public function withKey(string $apiKey): self
    {
        return new static($this->http, array_merge($this->config, ['api_key' => $apiKey]));
    }

    /** @return mixed */
    public function get(string $uri, array $query = [])
    {
        return $this->request('GET', $uri, $query);
    }

    /** @return mixed */
    public function post(string $uri, array $body = [])
    {
        return $this->request('POST', $uri, [], $body);
    }

    /** @return mixed */
    public function put(string $uri, array $body = [])
    {
        return $this->request('PUT', $uri, [], $body);
    }

    /** @return mixed */
    public function delete(string $uri)
    {
        return $this->request('DELETE', $uri);
    }

    /**
     * Send a request and return the decoded payload. Responses in the
     * {status, message, data} wrapper are unwrapped to `data`; bare
     * objects, arrays and page objects are returned as they are.
     *
     * @return mixed
     *
     * @throws RequestException
     */
    public function request(string $method, string $uri, array $query = [], ?array $body = null)
    {
        return static::unwrap($this->send($method, $uri, $query, $body)->json());
    }

    /**
     * Send a request and return the raw response (already checked for errors).
     *
     * @throws RequestException
     */
    public function send(string $method, string $uri, array $query = [], ?array $body = null): Response
    {
        $apiKey = $this->config['api_key'] ?? null;

        if (! is_string($apiKey) || $apiKey === '') {
            throw new JayikException('Jayik API key is not configured. Set JAYIK_API_KEY or use Jayik::withKey().');
        }

        $options = [
            'connect_timeout' => (int) ($this->config['connect_timeout'] ?? 10),
        ];

        if ($query = static::normalizeQuery($query)) {
            $options['query'] = $query;
        }

        if ($body !== null) {
            $options['json'] = static::normalizeBody($body);
        }

        $retries = max(0, (int) ($this->config['rate_limit']['retries'] ?? 0));
        $maxWait = max(0, (int) ($this->config['rate_limit']['max_wait'] ?? 30));

        for ($attempt = 0; ; $attempt++) {
            $response = $this->http
                ->baseUrl(rtrim((string) ($this->config['base_url'] ?? ''), '/'))
                ->withHeaders(['X-API-KEY' => $apiKey])
                ->acceptJson()
                ->timeout((int) ($this->config['timeout'] ?? 30))
                ->send($method, ltrim($uri, '/'), $options);

            if ($response->status() === 429 && $attempt < $retries) {
                $wait = max(1, (int) $response->header('Retry-After'));

                if ($wait <= $maxWait) {
                    $this->sleep($wait);

                    continue;
                }
            }

            if ($response->failed()) {
                throw RequestException::fromResponse($response);
            }

            return $response;
        }
    }

    /**
     * @param  mixed  $json
     * @return mixed
     */
    public static function unwrap($json)
    {
        if (is_array($json)
            && array_key_exists('data', $json)
            && array_key_exists('message', $json)
            && is_bool($json['status'] ?? null)) {
            return $json['data'];
        }

        return $json;
    }

    /**
     * Drops nulls, turns lists into CSV ("include" => ['status', 'customer'])
     * and booleans into 1/0 so `filter[...]` style arrays can be passed naturally.
     */
    public static function normalizeQuery(array $query): array
    {
        $out = [];

        foreach ($query as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_bool($value)) {
                $value = (int) $value;
            } elseif (is_array($value)) {
                $value = array_keys($value) === array_keys(array_values($value))
                    ? implode(',', $value)
                    : static::normalizeQuery($value);

                if ($value === '' || $value === []) {
                    continue;
                }
            }

            $out[$key] = $value;
        }

        return $out;
    }

    protected static function normalizeBody(array $body): array
    {
        array_walk_recursive($body, function (&$value) {
            if ($value instanceof DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }
        });

        return $body;
    }

    protected function sleep(int $seconds): void
    {
        if (static::$sleepUsing) {
            call_user_func(static::$sleepUsing, $seconds);

            return;
        }

        sleep($seconds);
    }
}
