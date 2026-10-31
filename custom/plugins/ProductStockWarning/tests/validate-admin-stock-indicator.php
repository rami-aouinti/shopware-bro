<?php declare(strict_types=1);

$root = dirname(__DIR__);
$requiredFiles = [
    'src/Resources/app/administration/src/main.js',
    'src/Resources/app/administration/src/extension/sw-product-list/index.js',
    'src/Resources/app/administration/src/extension/sw-product-list/sw-product-list.html.twig',
    'src/Resources/app/administration/src/extension/sw-product-list/sw-product-list.scss',
    'src/Resources/app/administration/src/snippet/de-DE.json',
    'src/Resources/app/administration/src/snippet/en-GB.json',
];

foreach ($requiredFiles as $relativePath) {
    if (!is_file($root . '/' . $relativePath)) {
        fwrite(STDERR, "Missing file: " . $relativePath . PHP_EOL);
        exit(1);
    }
}

$extension = file_get_contents($root . '/src/Resources/app/administration/src/extension/sw-product-list/index.js');
$template = file_get_contents($root . '/src/Resources/app/administration/src/extension/sw-product-list/sw-product-list.html.twig');

foreach ([
    "Shopware.Component.override('sw-product-list'",
    "stockWarningStatus",
    "getValues('ProductStockWarning.config')",
    'currentStock <= 0',
    'currentStock <= this.stockWarningThreshold',
] as $needle) {
    if (!str_contains($extension, $needle)) {
        fwrite(STDERR, "Missing expected behavior: " . $needle . PHP_EOL);
        exit(1);
    }
}

foreach ([
    'sw_product_list_grid_columns',
    'column-stockWarningStatus',
] as $needle) {
    if (!str_contains($template, $needle)) {
        fwrite(STDERR, "Missing template extension point: " . $needle . PHP_EOL);
        exit(1);
    }
}

if (str_contains($template, 'sw_extends')) {
    fwrite(STDERR, "Administration override template must not use sw_extends." . PHP_EOL);
    exit(1);
}

foreach (['de-DE', 'en-GB'] as $locale) {
    $snippetPath = $root . '/src/Resources/app/administration/src/snippet/' . $locale . '.json';
    $snippet = json_decode(file_get_contents($snippetPath), true);
    if (!isset($snippet['productStockWarning']['admin']['columnLabel'])) {
        fwrite(STDERR, "Invalid translation snippet: " . $locale . PHP_EOL);
        exit(1);
    }
}

echo "SW-104 administration stock indicator validation passed." . PHP_EOL;
