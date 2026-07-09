# Hashdown TODO

## Design principles

**Round-trip guarantees apply to PHP values, not file bytes.**

- **Guaranteed:** PHP in → write → read → same PHP out (`assertSame` on arrays/scalars).
- **Not guaranteed:** file in → write → same file bytes. Hand-edited files may lose comments, formatting choices (dash vs hash lists, omitted numeric keys), or textual form of values (e.g. `1.23e4` → `12300.0`) — as long as the PHP values are preserved.
- **File-origin equivalence:** file → parse → write → parse should yield the same PHP as the first parse. That is the meaningful round-trip when starting from a file; it does not require the intermediate file to match the original.

---

## User priorities

- [ ] **Better parse errors** — Replace generic `\Exception` messages with structured errors: file path (when available), **line number**, the **offending line**, and a **plain-language explanation** of what went wrong (not just "Invalid node depth"). Introduce something like `HashdownParseException` with those fields.

---

Improvement opportunities from project review. Not necessarily in priority order.

## High value — correctness & docs

- [ ] **Fix README groceries example** — The parser produces a proper list of objects (`Groceries => [ {Name, Ingredients}, ... ]`), but the README PHP example shows duplicate keys (`'Name' =>` twice), which isn't valid PHP and doesn't match real behavior.
- [ ] **Align `false on failure` docs with code** — `x_parse_md_string` / `x_parse_md_lines` are documented as returning `array|false`, but they always return an array or throw. Either implement structured parse errors or update docs/return types.
- [ ] **Resolve PHP version mismatch** — `composer.json` allows PHP `^7.4`, but PHPUnit 11 requires PHP 8.2+. Bump library minimum to 8.2 or downgrade PHPUnit for broader PHP support.
- [ ] **Full-document round-trip tests** — Add write → read → assert-same for remaining fixtures (`person`, `todo-list`, `auto-typing`). (`page-builder` and `comments` already have file-origin round-trips.)
- [ ] **`auto-typing` list round-trip** — `auto-typing.md` includes a `list-items` dash list with mixed types. Currently parse-only; a round-trip test would exercise list serialization + auto-typing together.

## Code quality

- [ ] **Consider splitting `Hashdown.php`** — ~600 lines covering parse, write, typing, and literals. Still manageable, but a `Parser` / `Writer` / `ScalarCodec` split may help as features grow.
- [ ] **Remove dead/stale parser code** — Unused variables in `x_parse_md_lines` (`$is_in_literal`, `$x_data_cursor`, etc.). Commented-out `>` blockquote support — implement or remove.
- [ ] **Replace echo + output buffering for string building** — `s_stringify_x` uses `ob_start()` / `echo`. Accumulating into a string would be easier to test and reason about.
- [ ] **Guard `a_line_type_summary` against empty lines** — `$s_line[0]` on an empty string after `ltrim` could warn on PHP 8+. Probably unreachable, but a guard would be defensive.

## API & ergonomics

- [ ] **Consider modern method aliases** — `x_`, `s_`, `b_`, `a_`, `i_` prefixes are consistent but unfamiliar in modern PHP. Aliases like `parse()`, `stringify()`, `readFile()`, `writeFile()` could help adoption (keep existing names).
- [ ] **Document `b_auto_type_scalars` write asymmetry** — No write-side equivalent; serialization always applies smart typing rules. Document explicitly. Add tests with `b_auto_type_scalars = false`.
- [ ] **`undefined` / `undef` sentinel** — Planned in README: omit keys entirely rather than writing `null`. Useful for sparse configs.

## Edge cases & robustness

- [ ] **Streaming parse/write for large files** — Implement incremental read/write so very large documents are not fully loaded into memory (`file()` line arrays, full PHP trees, `ob_start()` write buffers). Possible directions: generator-based parse, chunked file reads, stream writer to disk. Needed when files exceed practical `memory_limit` bounds documented in README.
- [ ] **Float edge cases** — `NAN`, `INF`, `-0.0`, very large floats, scientific notation on write. `s_format_float` handles non-finite values loosely; read/write behavior untested.
- [ ] **Strings containing backticks** — e.g. a single-line string `` say `hello` `` may need literal fencing on write. Coverage unclear.
- [ ] **Duplicate keys at same level** — PHP arrays last-wins. Hashdown doesn't warn when the same header key appears twice under one parent.
- [ ] **Line ending cross-platform behavior** — `x_parse_md_string` accepts a delimiter; `file()` uses system line endings. CRLF vs LF round-trips not explicitly tested.

## Project & packaging

- [ ] **Add CI** — GitHub Actions: `composer install && vendor/bin/phpunit` on push.
- [ ] **`composer.json` polish** — Add `scripts.test`, `keywords`, repository URL, `homepage`. Use `Neckberg\Hashdown\Tests` namespace for test class (autoload-dev already configured).
- [ ] **Reduce test duplication** — `testParseString` and `testParseFile` overlap almost completely; merge or use a data provider.
- [ ] **Clean up dev artifacts** — Commented `testNewCase`, `0_src`/`0_tgt` fixtures, `tests/tmp/` debug files. Finish (e.g. groceries round-trip) or remove.

## Already in good shape

- Fixture-driven tests with clear intent
- Round-trip coverage for scalars, whitespace, literals, and comments
- README documents auto-typing, inline backticks, serialization, comments, and limits/performance
- Lossless scalar serialization design
- Error cases for bad hash/list depth
- Dash-list parse performance (O(n) implicit numeric keys)

## Suggested starting shortlist

1. **Better parse exceptions** (user priority)
2. Full-document round-trips (`person`, `todo-list`, `auto-typing`)
3. Fix README groceries example + `false on failure` doc mismatch
4. Align PHP version requirements
5. Add CI
6. `undefined` sentinel
