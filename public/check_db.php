<?php
// Secure temporary diagnostic script
$secret = 'check123';
if (($_GET['s'] ?? '') !== $secret) die('Access denied.');

require '../vendor/autoload.php';
$app = require_once '../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "Product count: " . Product::count() . "<br>";
echo "Auction Products: " . Product::where('auction_product', 1)->count() . "<br>";
echo "Wholesale Products: " . Product::where('wholesale_product', 1)->count() . "<br>";
echo "Digital Products: " . Product::where('digital', 1)->count() . "<br>";
echo "Last 5 Products:<br>";
foreach(Product::latest()->take(5)->get() as $p) {
    echo "- [$p->id] $p->name (Added by: $p->added_by)<br>";
}
echo "--- Done ---";
unlink(__FILE__); // Self-destruct after execution
