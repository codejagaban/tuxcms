<?php

namespace App\Console\Commands;

use App\Services\Site\SiteBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class BuildSite extends Command
{
    protected $signature = 'site:build {--quiet-progress : Suppress the per-page listing}';

    protected $description = 'Render the published site to static HTML';

    public function handle(SiteBuilder $builder): int
    {
        // Two concurrent builds writing the same docroot would interleave.
        $lock = Cache::lock('site:build', 600);

        if (!$lock->get()) {
            $this->error('Another build is already running.');

            return self::FAILURE;
        }

        $started = microtime(true);

        try {
            $result = $builder->build(function (string $title, string $path) {
                if (!$this->option('quiet-progress')) {
                    $this->line("  <fg=gray>rendered</> {$title} <fg=gray>→</> {$path}");
                }
            });
        } catch (\Throwable $e) {
            $this->error('Build failed: ' . $e->getMessage());

            return self::FAILURE;
        } finally {
            $lock->release();
        }

        $seconds = round(microtime(true) - $started, 2);

        $this->newLine();
        $this->info(sprintf(
            'Built %d pages (%d files, %d pruned) in %ss.',
            $result['pages'],
            $result['files'],
            $result['pruned'],
            $seconds
        ));
        $this->line('  <fg=gray>Output:</> ' . $result['output']);

        return self::SUCCESS;
    }
}
