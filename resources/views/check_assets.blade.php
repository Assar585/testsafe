<?php
echo "<h3>Asset Check</h3>";
$paths = [
    'public/assets/js/vendors.js',
    'public/assets/js/aiz-core.js',
    'public/assets/css/vendors.css',
    'public/assets/css/aiz-core.css',
    'public/uploads/all'
];

foreach ($paths as $path) {
    $full = base_path($path);
    echo "<b>$path</b>: " . (file_exists($full) ? "<span style='color:green'>FOUND</span>" : "<span style='color:red'>MISSING</span>") . " (" . $full . ")<br>";
    if (is_dir($full)) {
        $files = array_diff(scandir($full), ['.', '..']);
        echo "-- Count: " . count($files) . "<br>";
    }
}

echo "<h3>Environment Check</h3>";
echo "APP_URL: " . env('APP_URL') . "<br>";
echo "ASSET_URL: " . env('ASSET_URL') . "<br>";
echo "Static Asset Test: " . asset('assets/js/vendors.js') . "<br>";
