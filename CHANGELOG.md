# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0]

### Breaking

- **Type-aware scalar write** — `null`, `true`/`false`, ints, and floats are serialized as typed text (e.g. `null`, `true`, `3.0`) instead of empty or opaque string forms. Re-reading preserves PHP types.
- **Ambiguous strings are backtick-wrapped on write** — values like `'true'`, `'123'`, or `'1e6'` are written as `` `true` `` / `` `123` `` / `` `1e6` `` so auto-typing does not change them on read.
- **Whole-number floats keep a decimal** — e.g. `3.0` is written as `3.0`, not `3`, so they round-trip as floats.
- **Floats write in decimal notation** — scientific notation may be recognized on read (e.g. `1.23e4` → `12300.0`), but write uses decimal text (`12300.0`).
- **Stricter dash-list rules** — only a single `-` is valid list syntax. Multi-dash markers (`--`, `---`) and nested objects/arrays under a dash item throw parse errors. Use empty `#` headers for nested list items.
- **Parse APIs throw on invalid structure** — documented behavior is `\Exception` with line, path (when known), and a plain-language reason. Docs no longer claim `false` on failure.
- **Leading `\` is no longer auto-fenced on write** — leftover from an abandoned `\`-comment idea; only `#` and `-` still force a fenced literal when they start a scalar line.
- **PHP 8.2+ required** — minimum PHP version raised from 7.4 to match the supported test toolchain (PHPUnit 11).

### Added

- **HTML comments** — `<!-- ... -->` (full-line, multi-line, and inline) are ignored outside fenced literals; preserved inside them. Writer does not re-emit comments.
- **Explicit fence type hints** — opening fences may use `int`, `float`, `bool`, `null`, or `string` (e.g. `` ```int ``).
- **Inline backticks** — a single pair of backticks forces a string scalar on read.
- **Auto-typing on read** — plain scalars map to bool / null / int / float when unambiguous; leading-zero numerics like `007` stay strings.
- **Clearer parse/I/O errors** — messages cite the offending line and cover cases such as invalid header depth, bad list placement, unsupported `--`, and unterminated fences or comments.
- **Limits and performance notes** in the README (memory model, comfort zones, error symptoms).

### Fixed

- **Dash-list parse performance** — assigning implicit numeric keys is O(n) instead of O(n²) for large `-` lists.
- **Groceries README examples** — corrected to a list of objects (matching how empty `#` headers parse).

### Changed

- Round-trip guarantee is defined as **PHP value equality**, not identical file bytes (comments and some textual forms may change on write).
