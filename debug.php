<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

try {
    echo "<h1>Testing Product Creation</h1>";
    $controller = app(\App\Http\Controllers\ProductController::class);
    echo $controller->create()->render();
    echo "<h2>Success</h2>";
} catch (\Throwable $e) {
    echo "<h1>Error Caught!</h1>";
    echo "<b>Message:</b> " . $e->getMessage() . "<br>";
    echo "<b>File:</b> " . $e->getFile() . " on line " . $e->getLine() . "<br><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
