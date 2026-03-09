<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$settings = [
    'system_name',
    'site_name',
    'frontend_base_color',
    'header_logo',
    'footer_logo',
    'admin_login_background',
    'admin_login_sidebar_theme',
    'base_color'
];

$results = DB::table('business_settings')
    ->whereIn('type', $settings)
    ->get();

foreach ($results as $row) {
    echo $row->type . ": " . $row->value . "\n";
}
