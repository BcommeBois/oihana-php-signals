# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Code coverage tooling: a Clover-to-Markdown report generator (`tools/clover-to-markdown.php`), Composer scripts and CI integration.
- A `Classes` row in the Markdown coverage summary, derived from the per-class metrics to match PHPUnit's `--coverage-text` figure.

### Changed
- Raised the test coverage of `Signal` and `SignalEntry` to 100%.

## [1.0.2] - 2026-01-18

### Changed
- Simplified `Notice` to rely directly on `ReflectionTrait::toArray()`, removing the custom `toArray()` override (and the `PrepareOption` import) introduced in 1.0.1.
- Regenerated the API documentation.

## [1.0.1] - 2026-01-18

### Fixed
- Adapted `Notice::toArray()` and `Notice::jsonSerialize()` to the new `ReflectionTrait::toArray()` signature from oihana-php-reflect, aliasing the trait method and enabling `PrepareOption::REDUCE`.
- Updated the related unit tests and phpDoc.

## [1.0.0] - 2025-11-12

Initial stable release.

### Added
- Core signal/slot (observer) system with priority-based execution and one-time (auto-disconnect) listeners.
- Exception propagation control via `Signal::$throwable` (defaults to true).
- Object receivers use PHP `WeakReference` so garbage collection is not prevented.
- Supports both PHP callables and `Receiver` objects; duplicate detection by identity.
- Public API surfaces:
  - Interfaces and classes under `oihana\\signals\\`: `Signaler`, `Signal`, `Receiver`, `SignalEntry` (internal helper).
  - Notice payloads: `oihana\\signals\\Notice`, `oihana\\signals\\notices\\Message`, `oihana\\signals\\notices\\Payload`.
- Documentation skeleton and phpDocumentor template for API docs.
- PHPUnit test suite and Composer scripts.

[Unreleased]: https://github.com/BcommeBois/oihana-php-signals/compare/1.0.2...HEAD
[1.0.2]: https://github.com/BcommeBois/oihana-php-signals/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/BcommeBois/oihana-php-signals/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/BcommeBois/oihana-php-signals/releases/tag/1.0.0
