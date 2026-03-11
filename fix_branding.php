<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BusinessSetting;

$branding = [
    'system_name' => 'SAFE CONTRACT',
    'site_name' => 'SAFE CONTRACT',
    'header_logo' => null, // We'll let them re-upload or fix paths
    'footer_logo' => null,
    'base_color' => '#377dff'
];

foreach ($branding as $type => $value) {
    if ($value !== null) {
        $setting = BusinessSetting::where('type', $type)->first();
        if ($setting) {
            if (empty($setting->value) || $setting->value == 'null' || $setting->value == 'SAFE-CONTRACT') {
                $setting->value = $value;
                $setting->save();
                echo "Updated $type to $value\n";
            }
        } else {
            BusinessSetting::create(['type' => $type, 'value' => $value]);
            echo "Created $type as $value\n";
        }
    }
}
echo "Branding fix complete.\n";
