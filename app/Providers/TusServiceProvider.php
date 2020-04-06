<?php

namespace App\Providers;

use TusPhp\Tus\Server as TusServer;
use Illuminate\Support\ServiceProvider;

class TusServiceProvider extends ServiceProvider
{
    // ...

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('tus-server', function ($app) {
            $server = new TusServer('redis');
            
            $server->setApiPath('/api/file') // tus server endpoint.
                ->setUploadDir(storage_path('app/temp')); // uploads dir.

            return $server;
        });
    }
}