<?php declare(strict_types=1);

/**
 * Lightweight validation for the declarative Shopware custom-field manifest.
 *
 * Run from the plugin directory:
 *   php tests/validate-custom-fields.php
 */

$manifest = __DIR__ . '/../src/Resources/config/custom-fields.xml';

if (!is_file($manifest)) {
    fwrite(STDERR, "FAIL: custom-fields.xml was not found at {$manifest}" . PHP_EOL);
    exit(1);
}

$previous = libxml_use_internal_errors(true);
$document = new DOMDocument();

try {
    $loaded = $document->load($manifest, LIBXML_NONET);
    if (!$loaded) {
        $messages = array_map(
            static fn (LibXMLError $error): string => trim($error->message),
            libxml_get_errors()
        );

        fwrite(STDERR, "FAIL: invalid XML: " . implode('; ', $messages) . PHP_EOL);
        exit(1);
    }

    $xpath = new DOMXPath($document);

    $assert = static function (bool $condition, string $message): void {
        if (!$condition) {
            fwrite(STDERR, "FAIL: {$message}" . PHP_EOL);
            exit(1);
        }

        echo "PASS: {$message}" . PHP_EOL;
    };

    $assert(
        $xpath->query('/custom-fields/custom-field-set[name="rami_manufacturer_reference_set"]')->length === 1,
        'custom-field set name is declared'
    );

    $assert(
        $xpath->query('/custom-fields/custom-field-set/related-entities/product')->length === 1,
        'custom-field set is associated with products'
    );

    $assert(
        $xpath->query('/custom-fields/custom-field-set/fields/text[@name="rami_manufacturer_reference"]')->length === 1,
        'manufacturer reference is declared as a text field'
    );

    $assert(
        $xpath->query('/custom-fields/custom-field-set/fields/text[@name="rami_manufacturer_reference"]/required[text()="false"]')->length === 1,
        'manufacturer reference is optional'
    );
} finally {
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
}
