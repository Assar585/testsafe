<?php

use Illuminate\Support\Facades\Artisan;

// Manually bootstrap the Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h1>Nuclear Cache Clear</h1>";

try {
    echo "Clearing View Cache... ";
    Artisan::call('view:clear');
    echo "Done.<br>";

    echo "Clearing Config Cache... ";
    Artisan::call('config:clear');
    echo "Done.<br>";

    echo "Clearing Route Cache... ";
    Artisan::call('route:clear');
    echo "Done.<br>";

    echo "Clearing Application Cache... ";
    Artisan::call('cache:clear');
    echo "Done.<br>";

    echo "Re-caching Config... ";
    Artisan::call('config:cache');
    echo "Done.<br>";

    echo "Re-caching Routes... ";
    Artisan::call('route:cache');
    echo "Done.<br>";

    echo "<h2>All caches cleared and rebuilt successfully!</h2>";
} catch (\Exception $e) {
    echo "<h2>Error: " . $e->getMessage() . "</h2>";
}
