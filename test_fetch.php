<?php

function loginAndFetch()
{
    $cookieJar = tempnam(sys_get_temp_dir(), 'cookies');

    // First, hit the login page to get CSRF token and session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://testsafe-production.up.railway.app/admin");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    $response = curl_exec($ch);

    // Parse CSRF token from the login page
    preg_match('/<input type="hidden" name="_token" value="([^"]*)">/', $response, $matches);
    $token = $matches[1] ?? '';

    if (!$token) {
        echo "Could not find CSRF token\n";
        return;
    }

    // Now POST to login
    curl_setopt($ch, CURLOPT_URL, "https://testsafe-production.up.railway.app/login");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        '_token' => $token,
        'email' => 'admin@email.com',
        'password' => '123456'
    ]));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);

    // Now fetch the ajax products endpoint
    curl_setopt($ch, CURLOPT_URL, "https://testsafe-production.up.railway.app/admin/products/filter/products?page=1");
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Accept: application/json",
        "X-Requested-With: XMLHttpRequest"
    ));
    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "HTTP Status: $httpCode\n";

    // Search for Symfony exception message
    if (preg_match('/<title([^>]*)>([^<]*)<\/title>/', $response, $matches)) {
        echo "Title: " . trim($matches[2]) . "\n";
    }

    if (preg_match('/<div class="exception-message">\s*([\s\S]*?)\s*<\/div>/i', $response, $matches)) {
        echo "Exception: " . strip_tags($matches[1]) . "\n";
    } elseif (preg_match('/"message":"([^"]*)"/', $response, $matches)) {
        echo "JSON Message: " . $matches[1] . "\n";
    } else {
        // Just print first 1000 chars
        echo "Response snippet:\n";
        echo substr($response, 0, 1000);
    }

    curl_close($ch);
    unlink($cookieJar);
}

loginAndFetch();
