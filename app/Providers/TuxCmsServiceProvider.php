<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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

        // Markers for the editor's preview; see App\Support\Preview. They
        // print nothing outside a preview render. `@tuxsection` because
        // Blade already owns `@section`.
        Blade::directive('field', fn (string $expression) => "<?php echo \\App\\Support\\Preview::field({$expression}); ?>");
        Blade::directive('image', fn (string $expression) => "<?php echo \\App\\Support\\Preview::image({$expression}); ?>");
        Blade::directive('tuxsection', fn (string $expression) => "<?php echo \\App\\Support\\Preview::section({$expression}); ?>");
    }
}
