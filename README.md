```bash                                      
 __      __   _             ____  _     _           _   
 \ \    / /  | |           / __ \| |   (_)         | |  
  \ \  / /_ _| |_   _  ___| |  | | |__  _  ___  ___| |_ 
   \ \/ / _` | | | | |/ _ \ |  | | '_ \| |/ _ \/ __| __|
    \  / (_| | | |_| |  __/ |__| | |_) | |  __/ (__| |_ 
     \/ \__,_|_|\__,_|\___|\____/|_.__/| |\___|\___|\__|
                                      _/ |              
                                     |__/                       
version: v.2.0.0  
description: There's patterns in everything and everywhere
```

# Patterns — Value Object

**Immutable, self-validating domain objects that compare by value.**

Part of the **Patterns** collection: small, rock-solid, dependency-free building
blocks. One pattern, one package.

- Package: `patterns/value-object`
- Class: `Patterns\ValueObject`
- Dependencies: **none** — the base class imports nothing
- PHP: `>=8.1`

---

## Install

```bash
composer require patterns/value-object
```

The base class is standalone: no runtime dependencies.
Pair it with [`patterns/result`](https://packagist.org/packages/patterns/result) if
you want your factories to return a `Result` instead of throwing — that is what the
examples do, and it is listed under `suggest`.

## Why

Primitives leak domain rules everywhere. `$email` is a `string`, so nothing
stops `"not-an-email"` from reaching your database, and two `Money(1000, 'EUR')`
are "different" just because they are different objects:

```php
// Before — primitive obsession, rules scattered, equality by identity
$email = $_POST['email'];            // any string, anywhere
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { /* ... */ }
$total = static::arraySum($cart);    // ad-hoc money maths on floats
if ($a === $b) { /* never true for "the same" money */ }
```

```php
// After — the concept validates itself and compares by value
use App\Domain\Email;   // your own Value Object

$result = Email::create($payload['email']);

if ($result->isFailure()) {
    return Result::fail($result->errorMessage());   // "Email is not in valid format"
}

$email = $result->value();        // guaranteed valid + normalized
$email->value();                  // "user@example.com"
$email->equals($otherEmail);      // true when the same address
```

An `Email` cannot exist in an invalid state: the only way in is `Email::create()`,
and it returns a `Result`. That is the whole pattern.

## Quick start

```php
use Patterns\Result;
use Patterns\ValueObject;

final class Money extends ValueObject
{
    /** @param array{amount: int, currency: string} $props */
    private function __construct(array $props)
    {
        parent::__construct($props);
    }

    public static function create(int $amount, string $currency): Result
    {
        if ($amount < 0) {
            return Result::fail('Money amount cannot be negative');
        }

        return Result::success(new self(['amount' => $amount, 'currency' => $currency]));
    }

    public function amount(): int    { return $this->props['amount']; }
    public function currency(): string { return $this->props['currency']; }
}
```

## API — `Patterns\ValueObject`

| Member | Behaviour |
|---|---|
| `protected readonly array $props` | The payload. Defined by the subclass; immutable. |
| `protected __construct(array $props)` | Called by subclasses (which keep their own constructor private/protected). |
| `equals(mixed $other): bool` | `true` when same **concrete class** and deeply equal props. Accepts `mixed` — never throws. |
| `toString(): string` | JSON of the props. Override for a domain form (see `Email`). |
| `__toString(): string` | Same as `toString()` (so `(string) $vo` and interpolation work). |
| `jsonSerialize(): array` | Implements `JsonSerializable`, so `json_encode($vo)` works. |
| `protected toProps(): array` | A value copy of the raw payload. |
| `protected static isEqual(mixed $a, mixed $b): bool` | The deep comparison used by `equals()`. |

### Equality — what counts as "equal"

| Comparison | Result |
|---|---|
| Same class, same props | `true` |
| Same class, one prop differs | `false` |
| Same props, **different class** (`Point` vs `Coordinate`) | `false` |
| Nested Value Objects equal (`Payment{payee, amount}`) | `true` |
| Nested objects of the same class (e.g. `DateTimeImmutable`) | compared structurally |
| `null`, `string`, `int`, array, foreign object | `false` (no `TypeError`) |
| `1` vs `"1"` vs `1.0` | `false` (type-strict) |
| String-keyed array, different insertion order | `true` |
| List (`['a','b']` vs `['b','a']`) | `false` (keys are positions) |

## Immutability

`$props` is `readonly`, so the payload cannot be reassigned **or mutated** —
not even by a subclass:

```php
$this->props['x'] = 999;   // Error: Cannot modify readonly property
```

There are no setters. "Changes" are expressed as operations returning a **new**
instance wrapped in a `Result` (see `Money::add()`).

## Examples

Two worked, fully tested examples live in the repository — `tests/Examples/`,
namespace `Patterns\Tests\Examples\` — so the runtime package stays
dependency-free. Copy them as a starting point.

 **Working examples on GitHub** —
[`tests/Examples/Email.php`](https://github.com/patterns-php/value-object/blob/main/tests/Examples/Email.php) ·
[`tests/Examples/Money.php`](https://github.com/patterns-php/value-object/blob/main/tests/Examples/Money.php)

**`Email`** — validation + normalization + custom string form:

```php
Email::create('  TeSt@ExAmPlE.CoM  ')->value()->value(); //  "test@example.com" if you use Result
Email::create('')->errorMessage();                        // "Email cannot be empty"
Email::create('nope')->errorMessage();                     // "Email is not in valid format"
Email::create(123)->errorMessage();                        // "Email must be a string"
(string) Email::create('a@b.com')->value();                // "a@b.com"
```

**`Money`** — integer minor units, behaviour, immutable evolution:

```php
$ten = Money::create(1000, 'EUR')->value();

$twelve = $ten->add(Money::create(234, 'EUR')->value())->value();
$twelve->amount();    // 1234
$ten->amount();       // 1000  — original untouched
$twelve->format();    // "12.34 EUR"

$ten->add(Money::create(100, 'USD')->value())->errorMessage();
// "Cannot add USD to EUR"
```

## Composition

Value Objects compose — a Value Object may hold other Value Objects:

```php
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
}
```

`Payment::of($a, $m1)->equals(Payment::of($b, $m2))` recurses into the nested
Value Objects — no manual comparison code. (`Payment` is the fixture used by the
test suite to prove nested equality; see `tests/Fixtures/Payment.php`.)

## Tests

```bash
composer install
composer test
# or
vendor/bin/phpunit
```

## License

MIT.
