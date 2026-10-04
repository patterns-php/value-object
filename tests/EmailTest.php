<?php

declare(strict_types=1);

namespace Patterns\Tests;

use PHPUnit\Framework\TestCase;
use Patterns\Examples\Email;

final class EmailTest extends TestCase
{
    public function testCreatesAValidEmail(): void
    {
        $result = Email::create('test@example.com');

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(Email::class, $result->value());
        $this->assertSame('test@example.com', $result->value()->value());
    }

    /**
     * @return list<array{0: string}>
     */
    public static function invalidEmails(): array
    {
        return [
            [''],
            ['   '],
            ['not-an-email'],
            ['@missing-local-part.com'],
            ['missing-domain@'],
            ['missing-domain-part@domain'],
            ['spaces in@email.com'],
        ];
    }

    /**
     * @dataProvider invalidEmails
     */
    public function testRejectsInvalidEmails(string $invalid): void
    {
        $result = Email::create($invalid);

        $this->assertTrue($result->isFailure());
        $this->assertNotNull($result->errorMessage());
    }

    public function testRejectsNonStringsWithoutTypeError(): void
    {
        $result = Email::create(123);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Email must be a string', $result->errorMessage());
    }

    public function testReportsTheEmptyMessageExplicitly(): void
    {
        $this->assertSame('Email cannot be empty', Email::create('')->errorMessage());
        $this->assertSame('Email is not in valid format', Email::create('nope')->errorMessage());
    }

    public function testNormalizesToLowercaseAndTrims(): void
    {
        $result = Email::create('  TeSt@ExAmPlE.CoM  ');

        $this->assertTrue($result->isSuccess());
        $this->assertSame('test@example.com', $result->value()->value());
    }

    public function testComparesByValue(): void
    {
        $a = Email::create('test@example.com')->value();
        $b = Email::create('TEST@example.com')->value();
        $c = Email::create('different@example.com')->value();

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function testEqualsHandlesNullSafely(): void
    {
        $email = Email::create('test@example.com')->value();

        $this->assertFalse($email->equals(null));
    }

    public function testStringRepresentationIsTheAddress(): void
    {
        $email = Email::create('test@example.com')->value();

        $this->assertSame('test@example.com', $email->toString());
        $this->assertSame('test@example.com', (string) $email);
    }
}
