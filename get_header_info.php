<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BusinessSetting;
use App\Models\ElementType;

$header_element_id = get_setting('header_element');
$header_name = get_element_type_by_id($header_element_id);

echo "Header Element ID: " . $header_element_id . "\n";
echo "Header Name: " . ($header_name ?? 'header1 (default)') . "\n";
