<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ImportDatabase extends Command
{
    protected $signature = 'app:import-db';
    protected $description = 'Import database.sql.gz reliably in CLI to prevent timeouts';

    public function handle()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $gzFile = base_path('database.sql.gz');
        if (!file_exists($gzFile)) {
            $this->error('ERROR: database.sql.gz not found');
            return 1;
        }

        $this->info("Extracting database.sql.gz...");
        $sqlFile = base_path('database_extracted.sql');
        $gz = gzopen($gzFile, 'rb');
        $out = fopen($sqlFile, 'wb');
        while (!gzeof($gz)) {
            fwrite($out, gzread($gz, 4096));
        }
        fclose($out);
        gzclose($gz);

        $this->info("Extracted! Loading into DB...");

        DB::unprepared('SET FOREIGN_KEY_CHECKS=0;');

        $handle = fopen($sqlFile, 'r');
        $buffer = '';
        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }
            $buffer .= $line;
            if (str_ends_with($trimmed, ';')) {
                DB::unprepared($buffer);
                $buffer = '';
            }
        }
        fclose($handle);

        DB::unprepared('SET FOREIGN_KEY_CHECKS=1;');

        $this->info("Import complete! Clearing caches...");

        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        @unlink($sqlFile);
        $this->info("Done successfully!");
        return 0;
    }
}
