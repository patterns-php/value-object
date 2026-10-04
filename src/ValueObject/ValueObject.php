<?php

declare(strict_types=1);

namespace Patterns\ValueObject;

use JsonSerializable;

/**
 * Base class for Value Objects - immutable objects defined by their property values.
 *
 * A Value Object has no identity: two instances are equal when they are the
 * same concrete class and all their properties are equal (compared deeply).
 *
 * Concrete value objects are expected to:
 *   - keep the constructor private/protected,
 *   - expose a static factory that validates and returns a `Patterns\Result`,
 *   - read their payload from `$this->props`.
 *
 * @see README.md for full documentation and examples.
 *
 * @template TProps of array<string, mixed>
 */
abstract class ValueObject implements JsonSerializable
{
    /**
     * The properties that define this Value Object.
     *
     * `readonly` guarantees the payload cannot be reassigned nor mutated - not
     * even by subclasses - so every instance is immutable by construction.
     *
     * @var array<string, mixed>
     */
    protected readonly array $props;

    /**
     * @param array<string, mixed> $props
     */
    protected function __construct(array $props)
    {
        $this->props = $props;
    }

    /**
     * Checks if this Value Object equals another one.
     *
     * Equality requires the same concrete class and deeply equal properties.
     * Accepts `mixed` so it is safe to call with raw/untrusted data - anything
     * that is not a Value Object of the same class simply returns false.
     */
    public function equals(mixed $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        if ($other::class !== static::class) {
            return false;
        }

        return static::isEqual($this->props, $other->props);
    }

    /**
     * Deep, type-strict structural comparison of two values.
     *
     * Handles nested arrays, nested Value Objects and other objects of the
     * same class. Arrays are compared by key (so a list is position sensitive,
     * while insertion order of string keys is irrelevant); scalar types are
     * strict, so `1 !== "1" !== 1.0`.
     */
    protected static function isEqual(mixed $a, mixed $b): bool
    {
        if ($a === $b) {
            return true;
        }

        if (is_array($a) && is_array($b)) {
            if (count($a) !== count($b)) {
                return false;
            }

            foreach ($a as $key => $value) {
                if (!array_key_exists($key, $b) || !static::isEqual($value, $b[$key])) {
                    return false;
                }
            }

            return true;
        }

        if ($a instanceof self && $b instanceof self) {
            return $a->equals($b);
        }

        if (is_object($a) && is_object($b) && $a::class === $b::class) {
            return static::isEqual((array) $a, (array) $b);
        }

        return false;
    }

    /**
     * Stable string representation of the properties (JSON).
     */
    public function toString(): string
    {
        return json_encode(
            $this->jsonSerialize(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) ?: '';
    }

    /**
     * PHP string-cast equivalent of {@see toString()}.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * The raw property payload (a value copy - mutating it has no effect).
     *
     * @return array<string, mixed>
     */
    protected function toProps(): array
    {
        return $this->props;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->props;
    }
}
