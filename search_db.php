<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$query = "FOOTER_SECTION_1_DONE";

$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    $tableName = array_values((array) $table)[0];
    try {
        $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
        foreach ($columns as $column) {
            $results = DB::table($tableName)
                ->where($column, 'LIKE', "%$query%")
                ->get();

            if ($results->count() > 0) {
                echo "Found in Table: $tableName, Column: $column\n";
                foreach ($results as $row) {
                    print_r($row);
                }
            }
        }
    } catch (\Exception $e) {
        // Skip tables with errors
    }
}
