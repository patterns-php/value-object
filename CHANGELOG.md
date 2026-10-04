# Changelog

All notable changes to `patterns/value-object` are documented here.

Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) ·
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-10-04

### Changed

- **Breaking:** the base class moved from `Patterns\ValueObject\ValueObject` to
  `Patterns\ValueObject` (file `src/ValueObject.php`). This package ships the
  pattern, never a family of value objects, so the extra namespace level had
  nothing to hold. Update imports:

  ```php
  - use Patterns\ValueObject\ValueObject;
  + use Patterns\ValueObject;
  ```

### Added

- README now links the working examples on GitHub.

## [1.0.1] - 2026-10-04

### Changed

- `patterns/result` is no longer a runtime dependency (now `require-dev` plus a
  `suggest` entry). The base class never used it.

### Removed

- `Patterns\Examples\Email` and `Patterns\Examples\Money` no longer ship with the
  package; the illustrations moved to `tests/Examples/` (`Patterns\Tests\Examples\`).

## [1.0.0] - 2026-10-04

### Added

- Initial release: immutable base class with `readonly` payload, deep equality by
  value (`equals()`), `toString()` / `__toString()` and `JsonSerializable`.

[2.0.0]: https://github.com/patterns-php/value-object/compare/v1.0.1...v2.0.0
[1.0.1]: https://github.com/patterns-php/value-object/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/patterns-php/value-object/releases/tag/v1.0.0
