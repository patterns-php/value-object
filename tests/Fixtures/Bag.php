<?php

declare(strict_types=1);

namespace Patterns\Tests\Fixtures;

use Patterns\ValueObject\ValueObject;

/**
 * Value Object whose payload is an arbitrary array - used to exercise deep,
 * order-insensitive array comparison.
 *
 * @extends ValueObject<array{items: array<int|string, mixed>}>
 */
final class Bag extends ValueObject
{
    /**
     * @param array<int|string, mixed> $items
     */
    private function __construct(array $items)
    {
        parent::__construct(['items' => $items]);
    }

    /**
     * @param array<int|string, mixed> $items
     */
    public static function of(array $items): self
    {
        return new self($items);
    }

    /**
     * @return array<int|string, mixed>
     */
    public function items(): array
    {
        /** @var array<int|string, mixed> $items */
        $items = $this->props['items'];

        return $items;
    }
}
