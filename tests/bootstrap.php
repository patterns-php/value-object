<?php

declare(strict_types=1);

/**
 * Test bootstrap.
 *
 * Registers a minimal PSR-4 autoloader for the `Patterns\` namespace across
 * this package's `src/`, this package's `tests/` and the sibling
 * `patterns/result` package's `src/`, so the suite runs without
 * `composer install`. When vendor/autoload.php is present it takes precedence
 * and this autoloader simply never fires.
 */

spl_autoload_register(static function (string $class): void {
    /** @var array<string, list<string>> $prefixes */
    $prefixes = [
        'Patterns\\Tests\\' => [__DIR__ . '/'],
        'Patterns\\' => [
            __DIR__ . '/../src/',
            __DIR__ . '/../../result/src/', // sibling package (patterns/result)
        ],
    ];

    foreach ($prefixes as $prefix => $roots) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

        foreach ($roots as $root) {
            if (is_file($root . $relative)) {
                require $root . $relative;

                return;
            }
        }

        return;
    }
});
