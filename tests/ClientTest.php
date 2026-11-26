<?php

namespace Jayik\Laravel\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Jayik\Laravel\Exceptions\JayikException;
use Jayik\Laravel\Exceptions\NotFoundException;
use Jayik\Laravel\Exceptions\RateLimitException;
use Jayik\Laravel\Exceptions\ValidationException;
use Jayik\Laravel\Facades\Jayik;
use Jayik\Laravel\Http\Client;

class ClientTest extends TestCase
{
    public function test_it_sends_the_api_key_and_unwraps_the_data_wrapper()
    {
        Http::fake([
            '*' => Http::response(['status' => true, 'message' => 'Order Created Successfully', 'data' => ['id' => 7, 'tr' => '100']]),
        ]);

        $order = Jayik::orders()->create(['customer_phone' => '07800000000', 'amount' => 5000]);

        $this->assertSame(['id' => 7, 'tr' => '100'], $order);

        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST'
                && $request->url() === self::BASE.'/v2/orders'
                && $request->hasHeader('X-API-KEY', 'jk_test_key')
                && $request->hasHeader('Accept', 'application/json')
                && $request['customer_phone'] === '07800000000'
                && $request['amount'] === 5000;
        });
    }

    public function test_bare_responses_are_returned_untouched()
    {
        Http::fake([
            '*/auth/details' => Http::response(['user' => ['id' => 'u1'], 'roles' => [], 'permissions' => ['merchant-get-order']]),
            '*/tr-exists/*' => Http::response(['exists' => true]),
        ]);

        $this->assertSame(['merchant-get-order'], Jayik::account()->permissions());
        $this->assertTrue(Jayik::orders()->trExists('ABC 1'));

        Http::assertSent(function (Request $request) {
            return $request->url() === self::BASE.'/auth/details';
        });
        Http::assertSent(function (Request $request) {
            return $request->url() === self::BASE.'/v2/orders/tr-exists/ABC%201';
        });
    }

    public function test_list_builds_filters_includes_and_returns_a_page()
    {
        Http::fake([
            '*' => Http::response(['current_page' => 1, 'data' => [['id' => 1], ['id' => 2]], 'per_page' => 2, 'last_page' => 3, 'total' => 5]),
        ]);

        $page = Jayik::orders()->list([
            'take' => 2,
            'simple' => false,
            'sort' => '-created_at',
            'include' => ['status', 'customer'],
            'filter' => ['status_id' => ['a', 'b'], 'customer.phone' => '0780', 'tr' => null],
        ]);

        $this->assertCount(2, $page);
        $this->assertSame(5, $page->total());
        $this->assertTrue($page->hasMorePages());
        $this->assertSame([1, 2], $page->collect()->pluck('id')->all());

        Http::assertSent(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $query == [
                'take' => '2',
                'simple' => '0',
                'sort' => '-created_at',
                'include' => 'status,customer',
                'filter' => ['status_id' => 'a,b', 'customer.phone' => '0780'],
            ];
        });
    }

    public function test_lazy_walks_every_page()
    {
        Http::fake([
            '*' => Http::sequence()
                ->push(['current_page' => 1, 'data' => [['id' => 1], ['id' => 2]], 'per_page' => 2, 'last_page' => 2])
                ->push(['current_page' => 2, 'data' => [['id' => 3]], 'per_page' => 2, 'last_page' => 2]),
        ]);

        $ids = Jayik::orders()->lazy(['take' => 2])->pluck('id')->all();

        $this->assertSame([1, 2, 3], $ids);
        Http::assertSentCount(2);
    }

    public function test_validation_errors_are_exposed()
    {
        Http::fake([
            '*' => Http::response(['status' => false, 'message' => ['customer_phone' => ['The customer phone field is required.']], 'data' => []], 400),
        ]);

        try {
            Jayik::orders()->create([]);
            $this->fail('Expected a ValidationException.');
        } catch (ValidationException $e) {
            $this->assertSame(400, $e->status());
            $this->assertSame('The customer phone field is required.', $e->getMessage());
            $this->assertSame(['customer_phone' => ['The customer phone field is required.']], $e->errors());
        }
    }

    public function test_status_codes_map_to_exceptions()
    {
        Http::fake(['*' => Http::response(['status' => false, 'message' => 'Not found', 'data' => []], 404)]);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not found');

        Jayik::orders()->get(99);
    }

    public function test_rate_limited_requests_are_retried_after_the_given_delay()
    {
        $slept = [];
        Client::$sleepUsing = function ($seconds) use (&$slept) {
            $slept[] = $seconds;
        };

        Http::fake([
            '*' => Http::sequence()
                ->push(['status' => false, 'message' => 'Too many', 'data' => []], 429, ['Retry-After' => '3'])
                ->push(['status' => true, 'message' => 'ok', 'data' => ['tr_code' => '100006537']]),
        ]);

        $this->assertSame('100006537', Jayik::orders()->generateTr());
        $this->assertSame([3], $slept);
    }

    public function test_rate_limit_exception_when_retries_are_exhausted()
    {
        config(['jayik.rate_limit.retries' => 0]);
        $this->app->forgetInstance(\Jayik\Laravel\Jayik::class);
        Jayik::clearResolvedInstances();

        Http::fake(['*' => Http::response(['status' => false, 'message' => 'Too many', 'data' => []], 429, ['Retry-After' => '12'])]);

        try {
            Jayik::statuses()->get('x');
            $this->fail('Expected a RateLimitException.');
        } catch (RateLimitException $e) {
            $this->assertSame(12, $e->retryAfter());
        }
    }

    public function test_with_key_uses_another_merchants_key()
    {
        Http::fake(['*' => Http::response(['status' => true, 'message' => 'ok', 'data' => null])]);

        Jayik::withKey('jk_vendor')->webhook()->remove();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('X-API-KEY', 'jk_vendor')
                && $request->method() === 'PUT'
                && array_key_exists('webhook', $request->data())
                && $request->data()['webhook'] === null;
        });
    }

    public function test_a_missing_key_fails_before_any_request()
    {
        Http::fake();

        $this->expectException(JayikException::class);

        Jayik::withKey('')->orders()->list();
    }
}
