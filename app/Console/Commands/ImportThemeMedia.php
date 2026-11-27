<?php

namespace App\Console\Commands;

use App\Services\Media\ThemeMediaImporter;
use Illuminate\Console\Command;

class ImportThemeMedia extends Command
{
    protected $signature = 'media:import-theme
                            {theme? : Theme name; defaults to the active theme}
                            {--hand-built : Also gather photos from the theme\'s hand-built pages}';

    protected $description = 'Copy the photos a theme\'s pages use into the media library';

    public function handle(ThemeMediaImporter $importer): int
    {
        $theme = $this->argument('theme') ?: (string) config('site.theme');

        if ($theme === '') {
            $this->error('No theme given and no active theme is set.');

            return self::FAILURE;
        }

        $result = $importer->import($theme, (bool) $this->option('hand-built'));

        $this->info("Imported {$result['imported']} photos from the {$theme} theme.");
        $this->line("Skipped {$result['skipped']} already in the library; {$result['missing']} source files were missing.");

        return self::SUCCESS;
    }
}
