<?php

declare(strict_types=1);

/**
 * Lightweight static validation for SW-102.
 *
 * Run from the plugin directory:
 *   php tests/validate-storefront-reference.php
 */

$pluginRoot = dirname(__DIR__);
$templatePath = $pluginRoot . '/src/Resources/views/storefront/page/product-detail/description.html.twig';
$snippetPaths = [
    'en-GB' => $pluginRoot . '/src/Resources/snippet/product-manufacturer-reference.en-GB.json',
    'de-DE' => $pluginRoot . '/src/Resources/snippet/product-manufacturer-reference.de-DE.json',
];

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}" . PHP_EOL);
        exit(1);
    }

    echo "PASS: {$message}" . PHP_EOL;
};

$assert(is_file($templatePath), 'product detail Twig override exists');

$template = file_get_contents($templatePath);
$assert(is_string($template), 'product detail Twig override is readable');
$assert(
    str_contains($template, "{% sw_extends '@Storefront/storefront/page/product-detail/description.html.twig' %}"),
    'template extends the core product description template'
);
$assert(
    str_contains($template, '{% block page_product_detail_description_content_text %}')
        && str_contains($template, '{{ parent() }}'),
    'the original product description block is preserved'
);
$assert(
    str_contains($template, 'rami_manufacturer_reference'),
    'template reads the existing manufacturer-reference custom field'
);
$assert(
    str_contains($template, 'manufacturerReference is not empty'),
    'empty references are not rendered'
);
$assert(
    str_contains($template, "'productManufacturerReference.label'|trans"),
    'the displayed label uses a translatable snippet'
);
$assert(
    !str_contains($template, 'autoescape false') && !str_contains($template, '|raw'),
    'template does not disable Twig auto-escaping'
);

foreach ($snippetPaths as $locale => $path) {
    $assert(is_file($path), "{$locale} snippet file exists");

    $json = file_get_contents($path);
    $assert(is_string($json), "{$locale} snippet file is readable");

    try {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        fwrite(STDERR, "FAIL: {$locale} snippet JSON is invalid: {$exception->getMessage()}" . PHP_EOL);
        exit(1);
    }

    $assert(
        isset($data['productManufacturerReference']['label'])
            && is_string($data['productManufacturerReference']['label'])
            && $data['productManufacturerReference']['label'] !== '',
        "{$locale} snippet defines a non-empty manufacturer-reference label"
    );
}

echo 'All SW-102 static validations passed.' . PHP_EOL;
