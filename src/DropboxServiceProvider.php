<?php

namespace Tigusigalpa\Dropbox;

use Illuminate\Support\ServiceProvider;
use RuntimeException;

class DropboxServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/dropbox.php',
            'dropbox'
        );

        $this->app->singleton(DropboxClient::class, function ($app) {
            $accessToken = config('dropbox.access_token');

            if (empty($accessToken)) {
                throw new RuntimeException('Dropbox access token is not configured. Please set DROPBOX_ACCESS_TOKEN in your .env file.');
            }

            return new DropboxClient($accessToken);
        });

        $this->app->alias(DropboxClient::class, 'dropbox');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/dropbox.php' => config_path('dropbox.php'),
            ], 'dropbox-config');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [DropboxClient::class, 'dropbox'];
    }
}
