<?php

declare(strict_types=1);

namespace Patterns\Examples;

use Patterns\Result;
use Patterns\ValueObject\ValueObject;

/**
 * Money - a Value Object with behavior.
 *
 * Amounts are integer minor units (cents) plus a 3-letter ISO currency code.
 * Operations never mutate: they return a new Money wrapped in a Result.
 *
 * @extends ValueObject<array{amount: int, currency: string}>
 */
final class Money extends ValueObject
{
    private function __construct(int $amount, string $currency)
    {
        parent::__construct(['amount' => $amount, 'currency' => $currency]);
    }

    /**
     * @return Result<self>
     */
    public static function create(mixed $amount, mixed $currency = 'EUR'): Result
    {
        if (!is_int($amount)) {
            return Result::fail('Money amount must be an integer of minor units');
        }

        if ($amount < 0) {
            return Result::fail('Money amount cannot be negative');
        }

        if (!is_string($currency) || preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            return Result::fail('Money currency must be a 3-letter uppercase ISO code');
        }

        return Result::success(new self($amount, $currency));
    }

    public function amount(): int
    {
        /** @var int $amount */
        $amount = $this->props['amount'];

        return $amount;
    }

    public function currency(): string
    {
        /** @var string $currency */
        $currency = $this->props['currency'];

        return $currency;
    }

    public function isZero(): bool
    {
        return $this->amount() === 0;
    }

    /**
     * Returns a NEW Money (immutable evolution) - never mutates this instance.
     * Adding different currencies is a domain error, so it fails.
     *
     * @return Result<self>
     */
    public function add(self $other): Result
    {
        if ($this->currency() !== $other->currency()) {
            return Result::fail(
                "Cannot add {$other->currency()} to {$this->currency()}"
            );
        }

        return Result::success(new self($this->amount() + $other->amount(), $this->currency()));
    }

    /**
     * Human readable minor-unit rendering, e.g. 1234 EUR => "12.34 EUR".
     */
    public function format(): string
    {
        return sprintf('%d.%02d %s', intdiv($this->amount(), 100), $this->amount() % 100, $this->currency());
    }

    public function toString(): string
    {
        return $this->format();
    }
}
