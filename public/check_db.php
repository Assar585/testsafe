<?php
$secret = 'check123';
if (($_GET['s'] ?? '') !== $secret) die('Access denied.');

require '../vendor/autoload.php';
$app = require_once '../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "DB_HOST: " . env('DB_HOST') . "<br>";
echo "DB_DATABASE: " . env('DB_DATABASE') . "<br>";
echo "Product count: " . Product::count() . "<br>";
echo "Last 5 Products:<br>";
foreach(Product::latest()->take(5)->get() as $p) {
    echo "- [$p->id] $p->name (Added by: $p->added_by, Created at: $p->created_at)<br>";
}
echo "--- Done ---";
// unlink(__FILE__); // Keep it for a moment
