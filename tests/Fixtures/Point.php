<?php

declare(strict_types=1);

namespace Patterns\Tests\Fixtures;

use Patterns\ValueObject\ValueObject;

/**
 * Minimal concrete Value Object used to exercise the base class.
 *
 * @extends ValueObject<array{x: int, y: int}>
 */
final class Point extends ValueObject
{
    private function __construct(int $x, int $y)
    {
        parent::__construct(['x' => $x, 'y' => $y]);
    }

    public static function of(int $x, int $y): self
    {
        return new self($x, $y);
    }

    public function x(): int
    {
        /** @var int $x */
        $x = $this->props['x'];

        return $x;
    }

    public function y(): int
    {
        /** @var int $y */
        $y = $this->props['y'];

        return $y;
    }

    /**
     * @return array<string, mixed>
     */
    public function raw(): array
    {
        return $this->toProps();
    }

    /**
     * Used to prove the payload is immutable (expected to throw).
     */
    public function mutate(): void
    {
        $this->props['x'] = 999;
    }
}
