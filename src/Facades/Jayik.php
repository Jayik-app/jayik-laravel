<?php

namespace Jayik\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Jayik\Laravel\Jayik withKey(string $apiKey)
 * @method static \Jayik\Laravel\Http\Client client()
 * @method static \Jayik\Laravel\Resources\Account account()
 * @method static \Jayik\Laravel\Resources\Orders orders()
 * @method static \Jayik\Laravel\Resources\Warehouses warehouses()
 * @method static \Jayik\Laravel\Resources\OrderCategories orderCategories()
 * @method static \Jayik\Laravel\Resources\Governorates governorates()
 * @method static \Jayik\Laravel\Resources\Statuses statuses()
 * @method static \Jayik\Laravel\Resources\Attributes attributes()
 * @method static \Jayik\Laravel\Resources\Webhook webhook()
 * @method static \Jayik\Laravel\Resources\ApiKeys apiKeys()
 *
 * @see \Jayik\Laravel\Jayik
 */
class Jayik extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Jayik\Laravel\Jayik::class;
    }
}
