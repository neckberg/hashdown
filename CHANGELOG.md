# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0]

### Breaking

- **PHP 8.2+ required** — minimum PHP version raised from 7.4.

- **Auto-typing on read (default on)** — plain scalars that look like booleans, `null`, integers, or floats are parsed as those PHP types instead of strings. Examples: `true` → `true`, `123` → `123`, `1.23e4` → `12300.0`, `null` → `null`. Leading-zero numerics like `007` stay strings. Pass `$b_auto_type_scalars = false` to keep the old all-strings read behavior for plain scalars.

- **HTML comments are stripped on parse** — `<!-- ... -->` outside fenced literals is ignored (full-line, multi-line, and inline). In 1.x that text was kept as part of the scalar. Inside fences, comments remain data. Unterminated `<!--` without a closing `-->` now throws. The writer does not re-emit comments.

- **Inline backticks force a string** — a single-line value wrapped in one pair of backticks (e.g. `` `true` ``) is read as the inner string (`'true'`), with the backticks removed. In 1.x the backticks were kept in the value. (With `$b_auto_type_scalars = false`, backtick wrappers are left as literal text, matching 1.x.)

- **Fence type hints coerce values** — an opening fence tagged `int`, `float`, `bool`, `null`, or `string` (e.g. `` ```int ``) now coerces the block body. In 1.x those tags were ignored and the body was a plain string. Other fence tags (e.g. `` ```php ``) still behave as ordinary literals.

- **Type-aware scalar write** — writing PHP values no longer string-casts the way 1.x did:
  - `true` / `false` → `true` / `false` (1.x wrote `1` / empty)
  - `null` → `null` (1.x wrote an empty value)
  - whole-number floats → `3.0` (1.x wrote `3`)
  - floats use decimal text on write (e.g. `12300.0`), even if the file was read from scientific notation
  - ambiguous strings (`'true'`, `'123'`, `'1e6'`, …) are written with inline backticks so re-reading preserves them as strings

- **Leading `\` is no longer auto-fenced on write** — only `#` and `-` still force a fenced literal when they start a scalar line. (Related to an abandoned `\`-comment idea; parse never treated `\` as special.)

### Added

- **`$b_auto_type_scalars`** on `x_read_file`, `x_parse_md_string`, and `x_parse_md_lines` (default `true`).
- **Clearer parse/I/O errors** — messages include line number, file path when known, the offending line, and a plain-language reason (invalid depth, bad list placement, unsupported `--`, unterminated fence/comment, etc.).
- **Limits and performance notes** in the README.
- **`CHANGELOG.md`**.

### Fixed

- **Dash-list parse performance** — assigning implicit numeric keys is O(n) instead of O(n²).
- **Groceries README examples** — corrected to a list of objects (matching how empty `#` headers parse).

### Changed

- **Round-trip guarantee** is defined as PHP value equality, not identical file bytes (comments and some textual forms may change on write).
- **Invalid `--` / nested structure under `-`** still fail as in 1.x, but with clearer messages and explicit docs that only a single `-` is valid list syntax.
