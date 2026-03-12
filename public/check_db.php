<?php
$secret = 'check123';
if (($_GET['s'] ?? '') !== $secret) die('Access denied.');

require '../vendor/autoload.php';
$app = require_once '../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

echo "Total Products: " . Product::count() . "<br>";
echo "Auction Products (any user): " . Product::where('auction_product', 1)->count() . "<br>";
echo "Wholesale Products (any user): " . Product::where('wholesale_product', 1)->count() . "<br>";
echo "Digital Products (any user): " . Product::where('digital', 1)->count() . "<br>";
echo "Normal Products: " . Product::where('auction_product', 0)->where('wholesale_product', 0)->where('digital', 0)->count() . "<br>";

echo "<br>Last 10 Products with IDs and Flags:<br>";
foreach(Product::latest()->take(10)->get() as $p) {
    echo "- [$p->id] $p->name | Auction: $p->auction_product | Wholesale: $p->wholesale_product | Digital: $p->digital | Added by: $p->added_by<br>";
}
echo "--- Done ---";
