<?php

declare(strict_types=1);

namespace Patterns\Tests\Fixtures;

use Patterns\Tests\Examples\Email;
use Patterns\Tests\Examples\Money;
use Patterns\ValueObject;

/**
 * Value Object composed of other Value Objects - used to exercise nested
 * equality (composition).
 *
 * @extends ValueObject<array{payee: Email, amount: Money}>
 */
final class Payment extends ValueObject
{
    private function __construct(Email $payee, Money $amount)
    {
        parent::__construct(['payee' => $payee, 'amount' => $amount]);
    }

    public static function of(Email $payee, Money $amount): self
    {
        return new self($payee, $amount);
    }

    public function payee(): Email
    {
        /** @var Email $payee */
        $payee = $this->props['payee'];

        return $payee;
    }

    public function amount(): Money
    {
        /** @var Money $amount */
        $amount = $this->props['amount'];

        return $amount;
    }
}
