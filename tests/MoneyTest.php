<?php

declare(strict_types=1);

namespace Patterns\Tests;

use PHPUnit\Framework\TestCase;
use Patterns\Examples\Money;

final class MoneyTest extends TestCase
{
    public function testCreatesValidMoney(): void
    {
        $result = Money::create(1234, 'EUR');

        $this->assertTrue($result->isSuccess());
        $this->assertSame(1234, $result->value()->amount());
        $this->assertSame('EUR', $result->value()->currency());
    }

    public function testRejectsNegativeAmounts(): void
    {
        $this->assertTrue(Money::create(-1, 'EUR')->isFailure());
    }

    public function testRejectsNonIntegerAmounts(): void
    {
        $this->assertTrue(Money::create(12.34, 'EUR')->isFailure());
        $this->assertTrue(Money::create('1234', 'EUR')->isFailure());
        $this->assertTrue(Money::create(null, 'EUR')->isFailure());
    }

    public function testRejectsMalformedCurrencyCodes(): void
    {
        $this->assertTrue(Money::create(100, 'eur')->isFailure());
        $this->assertTrue(Money::create(100, 'EURO')->isFailure());
        $this->assertTrue(Money::create(100, '12')->isFailure());
        $this->assertTrue(Money::create(100, 42)->isFailure());
    }

    public function testZero(): void
    {
        $this->assertTrue(Money::create(0, 'EUR')->value()->isZero());
        $this->assertFalse(Money::create(1, 'EUR')->value()->isZero());
    }

    public function testAddsSameCurrency(): void
    {
        $sum = Money::create(1000, 'EUR')->value()->add(Money::create(234, 'EUR')->value());

        $this->assertTrue($sum->isSuccess());
        $this->assertSame(1234, $sum->value()->amount());
    }

    public function testAddingDifferentCurrenciesFails(): void
    {
        $sum = Money::create(1000, 'EUR')->value()->add(Money::create(100, 'USD')->value());

        $this->assertTrue($sum->isFailure());
        $this->assertSame('Cannot add USD to EUR', $sum->errorMessage());
    }

    /**
     * Operations return new instances - the original is untouched.
     */
    public function testAdditionDoesNotMutate(): void
    {
        $ten = Money::create(1000, 'EUR')->value();
        $ten->add(Money::create(500, 'EUR')->value());

        $this->assertSame(1000, $ten->amount());
    }

    public function testFormatsMinorUnits(): void
    {
        $this->assertSame('12.34 EUR', Money::create(1234, 'EUR')->value()->format());
        $this->assertSame('0.05 EUR', Money::create(5, 'EUR')->value()->format());
        $this->assertSame('12.34 EUR', (string) Money::create(1234, 'EUR')->value());
    }

    public function testComparesByValue(): void
    {
        $a = Money::create(1000, 'EUR')->value();
        $b = Money::create(1000, 'EUR')->value();
        $c = Money::create(1000, 'USD')->value();
        $d = Money::create(1001, 'EUR')->value();

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
        $this->assertFalse($a->equals($d));
    }
}
