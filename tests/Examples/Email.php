<?php

declare(strict_types=1);

namespace Patterns\Tests\Examples;

use Patterns\Result;
use Patterns\ValueObject;

/**
 * Email - canonical Value Object example.
 *
 * Immutable, self-validating, normalized (lowercase, trimmed) and compared by
 * value.
 *
 * @extends ValueObject<array{value: string}>
 */
final class Email extends ValueObject
{
    private const PATTERN = '/^[\w\-.]+@([\w\-]+\.)+[\w\-]{2,4}$/';

    private function __construct(string $value)
    {
        parent::__construct(['value' => $value]);
    }

    /**
     * @return Result<self>
     */
    public static function create(mixed $email): Result
    {
        if (!is_string($email)) {
            return Result::fail('Email must be a string');
        }

        $normalized = strtolower(trim($email));

        if ($normalized === '') {
            return Result::fail('Email cannot be empty');
        }

        if (preg_match(self::PATTERN, $normalized) !== 1) {
            return Result::fail('Email is not in valid format');
        }

        return Result::success(new self($normalized));
    }

    public function value(): string
    {
        /** @var string $value */
        $value = $this->props['value'];

        return $value;
    }

    /**
     * A Value Object chooses its own string form; here it is just the address.
     */
    public function toString(): string
    {
        return $this->value();
    }
}
