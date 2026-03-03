<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DbUpdateController extends Controller
{
    public function updateLanguages()
    {
        try {
            if (!Schema::hasColumn('languages', 'is_active')) {
                DB::statement("ALTER TABLE languages ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER status");
                // Sync is_active with status
                DB::statement("UPDATE languages SET is_active = status");
            }

            if (!Schema::hasColumn('languages', 'is_default')) {
                DB::statement("ALTER TABLE languages ADD COLUMN is_default TINYINT(1) DEFAULT 0 AFTER is_active");
                // Set English as default initially
                DB::statement("UPDATE languages SET is_default = 1 WHERE code = 'en'");
            }

            if (!Schema::hasColumn('languages', 'fallback_locale')) {
                DB::statement("ALTER TABLE languages ADD COLUMN fallback_locale VARCHAR(10) DEFAULT 'en' AFTER is_default");
            }

            return "Database updated successfully";
        } catch (\Exception $e) {
            return "Error updating database: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile();
        }
    }
}
