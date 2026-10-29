<?php

declare(strict_types=1);

/**
 * Lightweight static validation for SW-103.
 * Run: php tests/validate-stock-warning.php
 */

$root = dirname(__DIR__);
$failures = [];

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        $failures[] = $message;
        fwrite(STDERR, "FAIL: {$message}" . PHP_EOL);
        return;
    }

    echo "PASS: {$message}" . PHP_EOL;
};

$composerPath = $root . '/composer.json';
$composer = is_file($composerPath)
    ? json_decode((string) file_get_contents($composerPath), true)
    : null;

$assert(is_array($composer), 'composer.json exists and contains valid JSON');
$assert(
    is_array($composer) && ($composer['name'] ?? null) === 'rami/product-stock-warning',
    'Composer package name is correct'
);
$assert(
    is_array($composer)
        && ($composer['extra']['shopware-plugin-class'] ?? null) === 'ProductStockWarning\\ProductStockWarning',
    'Shopware plugin class is registered'
);

$pluginClass = $root . '/src/ProductStockWarning.php';
$assert(is_file($pluginClass), 'Plugin entry-point exists');
if (is_file($pluginClass)) {
    $classContent = (string) file_get_contents($pluginClass);
    $assert(str_contains($classContent, 'class ProductStockWarning extends Plugin'), 'Plugin entry-point extends Shopware Plugin');
}

$configPath = $root . '/src/Resources/config/config.xml';
$assert(is_file($configPath), 'Plugin configuration XML exists');
if (is_file($configPath)) {
    $old = libxml_use_internal_errors(true);
    $xml = new DOMDocument();
    $loaded = $xml->load($configPath, LIBXML_NONET);
    $assert($loaded, 'Plugin configuration XML is well-formed');

    if ($loaded) {
        $xpath = new DOMXPath($xml);
        $assert(
            $xpath->query('/config/card/input-field[@type="int"]/name[text()="stockThreshold"]')->length === 1,
            'Integer stockThreshold configuration is declared'
        );
        $assert(
            $xpath->query('/config/card/input-field[name="stockThreshold"]/defaultValue[text()="5"]')->length === 1,
            'Default stock threshold is five'
        );
    }
    libxml_clear_errors();
    libxml_use_internal_errors($old);
}

$templatePath = $root . '/src/Resources/views/storefront/component/buy-widget/buy-widget.html.twig';
$assert(is_file($templatePath), 'Storefront buy-widget override exists');
if (is_file($templatePath)) {
    $twig = (string) file_get_contents($templatePath);
    $assert(
        str_contains($twig, "@Storefront/storefront/component/buy-widget/buy-widget.html.twig"),
        'Template extends the Shopware buy-widget template'
    );
    $assert(str_contains($twig, '{% block buy_widget_buy_form %}'), 'Correct buy form block is overridden');
    $assert(str_contains($twig, '{{ parent() }}'), 'Native buy form is preserved');
    $assert(str_contains($twig, "config('ProductStockWarning.config.stockThreshold')"), 'Threshold is read from plugin configuration');
    $assert(str_contains($twig, 'availableStock > 0 and availableStock <= stockThreshold'), 'Warning is limited to positive stock at or below threshold');
    $assert(str_contains($twig, "|trans({'%stock%': availableStock})"), 'Warning text is translated with the stock count');
    $assert(!str_contains($twig, '|raw') && !str_contains($twig, 'autoescape false'), 'Template does not disable output escaping');
}

foreach (['de-DE', 'en-GB'] as $locale) {
    $snippetPath = $root . '/src/Resources/snippet/product-stock-warning.' . $locale . '.json';
    $assert(is_file($snippetPath), "Translation file exists for {$locale}");
    if (is_file($snippetPath)) {
        $snippet = json_decode((string) file_get_contents($snippetPath), true);
        $assert(
            is_array($snippet) && !empty($snippet['productStockWarning']['message']),
            "Translation message is valid for {$locale}"
        );
    }
}

if ($failures !== []) {
    fwrite(STDERR, sprintf("%d validation(s) failed.%s", count($failures), PHP_EOL));
    exit(1);
}

echo "All SW-103 static validations passed." . PHP_EOL;
