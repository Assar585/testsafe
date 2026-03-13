<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\BusinessSetting;

echo "--- Product Count Diagnostic ---\n";
echo "Total Products: " . Product::count() . "\n";
echo "Published Products: " . Product::where('published', 1)->count() . "\n";
echo "Approved Products: " . Product::where('approved', 1)->count() . "\n";
echo "Preorder Products: " . Product::where('auction_product', 1)->count() . "\n"; // Assuming auction_product is preorder or similar

echo "\n--- Language Settings ---\n";
echo "Default Language: " . env('DEFAULT_LANGUAGE', 'en') . "\n";
$system_lang = BusinessSetting::where('type', 'system_default_language')->first();
echo "System Default Language (DB): " . ($system_lang ? $system_lang->value : 'not set') . "\n";

echo "\n--- First Product Info ---\n";
$p = Product::first();
if ($p) {
    echo "ID: " . $p->id . "\n";
    echo "Name: " . $p->name . "\n";
    echo "Translations: " . $p->product_translations()->count() . "\n";
} else {
    echo "No products found in DB.\n";
}
echo "--- End ---\n";
