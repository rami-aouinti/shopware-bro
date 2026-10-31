<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__.'/custom/plugins/TechPartsErpConnector/src',
        __DIR__.'/custom/plugins/TechPartsErpConnector/tests',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    // The project intentionally enforces declare(strict_types=1) in every PHP file.
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'ordered_imports' => true,
        'single_line_empty_body' => true,
    ])
    ->setFinder($finder);
