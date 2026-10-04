<?php

declare(strict_types=1);

namespace Patterns\Tests\Fixtures;

use Patterns\ValueObject\ValueObject;

/**
 * Same shape as {@see Point} - used to prove equality is class-strict.
 *
 * @extends ValueObject<array{x: int, y: int}>
 */
final class Coordinate extends ValueObject
{
    private function __construct(int $x, int $y)
    {
        parent::__construct(['x' => $x, 'y' => $y]);
    }

    public static function of(int $x, int $y): self
    {
        return new self($x, $y);
    }
}
