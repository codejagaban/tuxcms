<?php

namespace App\Console\Commands;

use App\Services\Media\CrystalMediaImporter;
use Illuminate\Console\Command;

class ImportCrystalMedia extends Command
{
    protected $signature = 'crystal:import-media';

    protected $description = 'Import images used by the Crystal site into the reusable media library';

    public function handle(CrystalMediaImporter $importer): int
    {
        $result = $importer->import();

        $this->info("Imported {$result['imported']} Crystal images.");
        $this->line("Skipped {$result['skipped']} existing images; {$result['missing']} source files were missing.");

        return self::SUCCESS;
    }
}
