# PHP code quality

The initial quality-tool scope is intentionally limited to the custom plugin
`custom/plugins/TechPartsErpConnector`. Shopware core, `vendor/`, generated
assets, and unrelated project code are not analysed by these rules.

## Tools

- **PHP_CodeSniffer** checks the PSR-12 style rules in `phpcs.xml.dist`.
- **PHP-CS-Fixer** applies the agreed formatting rules from
  `.php-cs-fixer.dist.php`. Run its check mode in CI before making it blocking.
- **PHPStan** performs static type analysis using `phpstan.neon.dist`, initially
  at level 5.
- **Psalm** provides a second static-analysis perspective using `psalm.xml`.
  It overlaps with PHPStan, so review its findings before raising strictness.
- **PHP Mess Detector** reports maintainability and complexity smells defined in
  `phpmd.xml`.
- **PHPUnit** checks the plugin's unit tests.

## Composer scripts

Run these from the repository root (inside the Shopware PHP container):

- `composer cs:check` — check style.
- `composer cs:fix` — apply safe style fixes.
- `composer stan` — run PHPStan.
- `composer psalm` — run Psalm.
- `composer mess-detector` — run PHPMD.
- `composer test:plugin` — run plugin unit tests.
- `composer quality` — run all checks in sequence.

## First run and adoption

The first run establishes a baseline; existing findings are not automatically
proof of a functional defect. Fix genuine issues and document narrowly scoped
exceptions rather than excluding the whole plugin. Do not auto-fix all files
until the diff has been reviewed.

After dependencies are installed and the first local runs are reviewed, add the
same commands to CI. Keep the tools' configuration in version control so the
whole team gets consistent results.
