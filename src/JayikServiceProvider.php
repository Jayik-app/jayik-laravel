<?php

namespace Jayik\Laravel;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Jayik\Laravel\Http\Client;
use Jayik\Laravel\Webhooks\SignatureVerifier;
use Jayik\Laravel\Webhooks\VerifyWebhookSignature;
use Jayik\Laravel\Webhooks\WebhookController;

class JayikServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jayik.php', 'jayik');

        $this->app->singleton(Jayik::class, function ($app) {
            // The facade root (not $app->make) so Http::fake() also applies on
            // Laravel 8, where the HTTP factory is not a container singleton.
            return new Jayik(
                new Client(Http::getFacadeRoot(), (array) $app['config']->get('jayik', []))
            );
        });

        $this->app->alias(Jayik::class, 'jayik');

        $this->app->bind(SignatureVerifier::class, function ($app) {
            return new SignatureVerifier(
                $app['config']->get('jayik.webhook.secret'),
                (int) $app['config']->get('jayik.webhook.tolerance', 300)
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/jayik.php' => $this->app->configPath('jayik.php'),
            ], 'jayik-config');
        }

        $router = $this->app['router'];
        $config = $this->app['config'];

        $router->aliasMiddleware('jayik.webhook', VerifyWebhookSignature::class);

        if ($config->get('jayik.webhook.route.enabled') && $config->get('jayik.webhook.route.path')) {
            $router->post($config->get('jayik.webhook.route.path'), WebhookController::class)
                ->middleware(array_merge((array) $config->get('jayik.webhook.route.middleware', []), ['jayik.webhook']))
                ->name('jayik.webhook');
        }
    }
}
