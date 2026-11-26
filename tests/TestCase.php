<?php

namespace Jayik\Laravel\Tests;

use Jayik\Laravel\Http\Client;
use Jayik\Laravel\JayikServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public const BASE = 'https://api.jayik.app/api/merchant';

    protected function getPackageProviders($app)
    {
        return [JayikServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('jayik.api_key', 'jk_test_key');
        $app['config']->set('jayik.webhook.secret', 'whsec_test');
    }

    protected function tearDown(): void
    {
        Client::$sleepUsing = null;

        parent::tearDown();
    }
}
