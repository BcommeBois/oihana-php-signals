# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Common commands

- Setup
  - Install dependencies: `composer install`
  - PHP version: >= 8.4 (see `composer.json`)
- Tests (PHPUnit 12)
  - Run all tests: `composer test` (alias for `./vendor/bin/phpunit`)
  - Run a single test file: `./vendor/bin/phpunit tests/oihana/signals/SignalTest.php`
  - Run a single test method: `./vendor/bin/phpunit tests/oihana/signals/SignalTest.php --filter '^testPriorityOrder$'`
  - Run with full path and filter works across files, e.g.: `./vendor/bin/phpunit --filter 'NoticeTest::testNoticeJsonEncode'`
- Docs generation
  - Build API docs: `composer run doc` (outputs to `docs/`, uses `phpdoc.xml`)
- Build/lint
  - There is no build step and no linter/static analysis configured in this repo.

## High-level architecture

This library provides a lightweight, strongly-typed signal/slot (observer) system with optional notice payloads.

- Signals core (event system)
  - `oihana\\signals\\Signaler` (interface): contract for emitters. API includes:
    - Property `length` (read-only): number of connected receivers
    - Methods: `connect()`, `connected()`, `disconnect()`, `emit()`, `hasReceiver()`
  - `oihana\\signals\\Signal` (implementation):
    - Manages an ordered list of receivers with priorities (higher runs first)
    - Prevents duplicates (identity-based)
    - Supports both PHP callables and objects implementing `Receiver`
    - Auto-disconnect: one-time listeners removed after first emit
    - Exception propagation controlled by `Signal::$throwable` (default true)
    - Uses PHP `WeakReference` for object receivers so GC is not prevented
    - Internal storage is `SignalEntry` objects; execution order maintained via sort on `priority`
  - `oihana\\signals\\Receiver` (interface):
    - Single method `receive(mixed ...$values): void` called when a signal emits
  - `oihana\\signals\\SignalEntry` (internal):
    - Wraps a receiver, its `priority`, and `auto` (auto-disconnect) flag
    - Normalizes receiver into an invokable form; for object-based receivers stores a `WeakReference` and (optionally) a method name
    - `getCallable()` resolves to a real callable or `null` if the target object was GC’d

- Notices (typed message payloads)
  - `oihana\\signals\\Notice`:
    - Public properties: `type` (string), `target` (object|null), `context` (array)
    - Implements `JsonSerializable` and `toArray()` via `oihana\\reflect\\traits\\ReflectionTrait`
  - `oihana\\signals\\notices\\Message`: `Notice` with `text: string`
  - `oihana\\signals\\notices\\Payload`: `Notice` with `data: mixed`

## Key configuration

- Autoloading: PSR-4 `oihana\\\\` -> `src/oihana` (see `composer.json`)
- Dev autoload: PSR-4 `tests\\\\` -> `tests`
- PHPUnit: `phpunit.xml`
  - `bootstrap`=`vendor/autoload.php`, `testdox` enabled
  - Strictness enabled (fail on warnings/skipped/incomplete, etc.)
  - Source includes `src/`

## External dependencies to be aware of

- `oihana/php-core` and `oihana/php-reflect` (both `dev-main` in `composer.json`)
  - The code uses helpers like `array_any(...)` and `ReflectionTrait` from these packages. Ensure `composer install` succeeds so these symbols are available.

## Development notes specific to this codebase

- Duplicate detection is by identity (the same callable/instance). Two closures with identical code are considered different; reuse the same variable reference if you need `hasReceiver()` or de-duplication to match.
- For object receivers, the invoked method defaults to `Receiver::receive` (constant `Signal::RECEIVE`). For array callables like `[$object, 'method']`, method existence is validated on connect.
- Setting `$signal->throwable = false;` will keep the emit chain running even if a receiver throws.

## Language conventions

- All source code, comments, documentation, and generated docs (including phpDocumentor templates) must be written in English.
- Chat prompts from the user may be in French; respond in French in the chat when appropriate, but keep code and docs in English.
