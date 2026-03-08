<?php

$files = [
    'resources/views/backend/product/products/create.blade.php',
    'resources/views/backend/product/products/edit.blade.php',
    'resources/views/seller/product/products/create.blade.php',
    'resources/views/seller/product/products/edit.blade.php',
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }

    $content = file_get_contents($file);

    // 1. Update Label to TN VED (HS Code)
    $content = preg_replace('/{{translate\(\'HS Code\'\)}}/', "{{translate('TN VED (HS Code)')}}", $content);
    $content = preg_replace('/{{translate\(\'HSN Code\'\)}}/', "{{translate('TN VED (HS Code)')}}", $content);

    // 2. Remove the heavy @foreach loop for HS Codes
    // We target the @foreach($hsCodes as $hsItem) block
    $content = preg_replace('/@foreach\(\$hsCodes as \$hsItem\).*?@endforeach/s', '', $content);

    // 3. Update placeholder and data-size
    $content = preg_replace('/{{ translate\(\'Select HS Code\.\.\.\'\) }}/', "{{ translate('Search TN VED by code or product name...') }}", $content);
    $content = preg_replace('/{{ translate\(\'Select HSN Code\.\.\.\'\) }}/', "{{ translate('Search TN VED by code or product name...') }}", $content);
    $content = preg_replace('/data-size="8"/', 'data-size="5"', $content);

    // 4. Simplify small text
    $content = preg_replace('/{{ translate\(\'Used for international shipping and customs\. Type to search by code or product name\.\'\) }}/', "{{ translate('Used for international shipping and customs.') }}", $content);

    // 5. Remove the heavy JSON initialization script
    $content = preg_replace('/\/\/ Load HS Code Autocomplete from JSON.*?AIZ\.plugins\.bootstrapSelect\(\'refresh\'\);\s+?\}\s+?\}\);\s+?\}\);/s', '', $content);

    file_put_contents($file, $content);
    echo "Processed: $file\n";
}
