<?php

declare(strict_types=1);

namespace Patterns\Tests;

use Error;
use PHPUnit\Framework\TestCase;
use Patterns\Examples\Email;
use Patterns\Examples\Money;
use Patterns\Tests\Fixtures\Bag;
use Patterns\Tests\Fixtures\Coordinate;
use Patterns\Tests\Fixtures\Payment;
use Patterns\Tests\Fixtures\Point;

final class ValueObjectTest extends TestCase
{
    public function testEqualWhenSameClassAndSameProps(): void
    {
        $this->assertTrue(Point::of(1, 2)->equals(Point::of(1, 2)));
    }

    public function testNotEqualWhenAPropertyDiffers(): void
    {
        $this->assertFalse(Point::of(1, 2)->equals(Point::of(1, 3)));
        $this->assertFalse(Point::of(1, 2)->equals(Point::of(9, 2)));
    }

    public function testEqualityIsReflexive(): void
    {
        $point = Point::of(4, 5);

        $this->assertTrue($point->equals($point));
    }

    public function testEqualityIsClassStrict(): void
    {
        // Same properties, different class => not equal.
        $this->assertFalse(Point::of(1, 2)->equals(Coordinate::of(1, 2)));
    }

    public function testNotEqualToNull(): void
    {
        $this->assertFalse(Point::of(1, 2)->equals(null));
    }

    /**
     * `equals()` accepts mixed and never throws a TypeError, so it is safe on
     * raw/untrusted data.
     */
    public function testNotEqualToForeignValuesWithoutTypeError(): void
    {
        $point = Point::of(1, 2);

        $this->assertFalse($point->equals('not a value object'));
        $this->assertFalse($point->equals(42));
        $this->assertFalse($point->equals([]));
        $this->assertFalse($point->equals(['x' => 1, 'y' => 2]));
        $this->assertFalse($point->equals(new \stdClass()));
    }

    public function testDeepEqualityOfNestedValueObjects(): void
    {
        $a = Payment::of(
            Email::create('test@example.com')->value(),
            Money::create(1000, 'EUR')->value()
        );
        $b = Payment::of(
            Email::create('TEST@example.com')->value(),
            Money::create(1000, 'EUR')->value()
        );
        $c = Payment::of(
            Email::create('other@example.com')->value(),
            Money::create(1000, 'EUR')->value()
        );

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    /**
     * Arrays are compared by key, so a list is position sensitive
     * (same as the TypeScript original).
     */
    public function testDeepEqualityOfListsIsPositionSensitive(): void
    {
        $this->assertTrue(Bag::of(['a', 'b'])->equals(Bag::of(['a', 'b'])));
        $this->assertFalse(Bag::of(['a', 'b'])->equals(Bag::of(['b', 'a'])));
        $this->assertFalse(Bag::of(['a', 'b'])->equals(Bag::of(['a'])));
        $this->assertFalse(Bag::of(['a', 'b'])->equals(Bag::of(['a', 'c'])));
    }

    /**
     * String-keyed arrays are matched by key name, so insertion order is
     * irrelevant.
     */
    public function testDeepEqualityOfNestedAssociativeArraysIgnoresKeyOrder(): void
    {
        $this->assertTrue(
            Bag::of(['meta' => ['weight' => 1, 'unit' => 'kg']])
                ->equals(Bag::of(['meta' => ['unit' => 'kg', 'weight' => 1]]))
        );
        $this->assertFalse(
            Bag::of(['meta' => ['weight' => 1]])
                ->equals(Bag::of(['meta' => ['weight' => 2]]))
        );
    }

    /**
     * Comparison is type-strict: 1 !== "1" !== 1.0.
     */
    public function testDeepEqualityIsTypeStrict(): void
    {
        $this->assertFalse(Bag::of([1])->equals(Bag::of(['1'])));
        $this->assertFalse(Bag::of([1])->equals(Bag::of([1.0])));
    }

    public function testToStringReturnsJson(): void
    {
        $point = Point::of(1, 2);

        $this->assertSame('{"x":1,"y":2}', $point->toString());
        $this->assertSame('{"x":1,"y":2}', (string) $point);
    }

    public function testJsonSerializeExposesProps(): void
    {
        $this->assertSame('{"x":7,"y":8}', json_encode(Point::of(7, 8)));
        $this->assertSame(['x' => 7, 'y' => 8], Point::of(7, 8)->jsonSerialize());
    }

    /**
     * The payload is `readonly`: mutating it - even from inside a subclass -
     * is a hard error. This is what makes the object truly immutable.
     */
    public function testPropsCannotBeMutated(): void
    {
        $this->expectException(Error::class);

        Point::of(1, 2)->mutate();
    }

    public function testToPropsReturnsAValueCopy(): void
    {
        $point = Point::of(1, 2);

        $raw = $point->raw();
        $raw['x'] = 999;

        $this->assertSame(1, $point->raw()['x']);
        $this->assertSame(1, $point->x());
    }
}
