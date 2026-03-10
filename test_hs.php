<?php

$q = 'flour';
$results = [];

// Emulate public_path
$public_path = 'c:/Projects/MVP 1/SafeCon/_public_html/public/';
$base_path = 'c:/Projects/MVP 1/SafeCon/_public_html/';

$json_paths = [
    $public_path . 'assets/data/hs_codes_un.json',
    $base_path . 'resources/data/hs_codes_un.json',
    $public_path . 'assets/data/hs_codes.json',
    $base_path . 'resources/data/hs_codes.json'
];

foreach ($json_paths as $path) {
    echo "Checking path: $path\n";
    if (file_exists($path)) {
        echo "File found!\n";
        $content = file_get_contents($path);

        // Strip BOM if present
        $content = preg_replace('/^[\xEF\xBB\xBF\xFE\xFF\xFF\xFE]*/', '', $content);

        $data = json_decode($content, true);

        if (empty($data)) {
            echo "Data is empty for $path\n";
            continue;
        }

        $items = isset($data['results']) ? $data['results'] : $data;

        if (!is_array($items)) {
            echo "Items is not an array for $path\n";
            continue;
        }

        echo "Found " . count($items) . " items. Searching for '$q'...\n";

        foreach ($items as $item) {
            $code = $item['code'] ?? $item['id'] ?? '';
            $desc = $item['desc'] ?? $item['text'] ?? '';

            if (empty($code) || empty($desc))
                continue;

            if (
                empty($q) ||
                stripos($code, $q) !== false ||
                stripos($desc, $q) !== false
            ) {
                $results[] = [
                    'id' => $code,
                    'text' => $code . ' - ' . $desc
                ];
            }

            if (count($results) >= 5)
                break 2;
        }
        break;
    }
}

echo "Final results: " . json_encode($results, JSON_PRETTY_PRINT) . "\n";
