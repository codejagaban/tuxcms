<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TuxCmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/tuxcms.php',
            'tuxcms'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/tuxcms.php' => config_path('tuxcms.php'),
        ], 'tuxcms-config');
    }
}
