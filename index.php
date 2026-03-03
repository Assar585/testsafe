<?php

ini_set('serialize_precision', -1);

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

if (isset($_GET['debug-env']) && $_GET['debug-env'] === 'railway') {
    echo "<div style='font-family: monospace; background: #fff; color: #333; padding: 20px; text-align: left;'>";
    echo "<h2>Environment Variables Debug:</h2>";
    echo "<b>DB_HOST:</b> " . getenv('DB_HOST') . " (from \$_ENV: " . ($_ENV['DB_HOST'] ?? 'null') . ", from \$_SERVER: " . ($_SERVER['DB_HOST'] ?? 'null') . ")<br>";
    echo "<b>DB_USERNAME:</b> " . getenv('DB_USERNAME') . " (from \$_ENV: " . ($_ENV['DB_USERNAME'] ?? 'null') . ", from \$_SERVER: " . ($_SERVER['DB_USERNAME'] ?? 'null') . ")<br>";
    echo "<b>DB_DATABASE:</b> " . getenv('DB_DATABASE') . " (from \$_ENV: " . ($_ENV['DB_DATABASE'] ?? 'null') . ", from \$_SERVER: " . ($_SERVER['DB_DATABASE'] ?? 'null') . ")<br>";
    echo "<b>File /var/www/.env exists:</b> " . (file_exists(__DIR__ . '/.env') ? 'Yes' : 'No') . "<br>";
    if (file_exists(__DIR__ . '/.env')) {
        echo "<b>Content of .env:</b><br><pre>" . htmlspecialchars(file_get_contents(__DIR__ . '/.env')) . "</pre>";
    }
    echo "</div>";
    exit;
}

require __DIR__ . '/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__ . '/bootstrap/app.php';



/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
