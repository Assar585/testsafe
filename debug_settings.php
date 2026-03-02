<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "HOMEPAGE_SELECT: " . get_setting('homepage_select') . "\n";
echo "HEADER_ELEMENT: " . get_setting('header_element') . "\n";
echo "DEFAULT_LANGUAGE: " . get_setting('DEFAULT_LANGUAGE') . "\n";
echo "SYSTEM_DEFAULT_CURRENCY: " . get_setting('system_default_currency') . "\n";
echo "HOME_SLIDER_IMAGES: " . get_setting('home_slider_images') . "\n";
