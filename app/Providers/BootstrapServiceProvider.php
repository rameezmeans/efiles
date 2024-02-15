<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BootstrapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../vendor/rameezmeans/ecutech-code/src/css' => public_path('vendor/ecutech-code/css'),
        ], 'public');

        $this->publishes([
            __DIR__ . '/../../vendor/rameezmeans/ecutech-code/src/js' => public_path('vendor/ecutech-code/js'),
        ], 'public');

        $this->publishes([
            __DIR__ . '/../../vendor/rameezmeans/ecutech-code/src/images' => public_path('vendor/ecutech-code/images'),
        ], 'public');
    }
}
