# Hashdown
Hashdown reads and parses a strictly formatted .md file into a PHP numeric or associative array - or writes a PHP array or object to a structured .md file.

## Why?
Markdown's advantages as a documentation syntax are well recognized - but Markdown also offers advantages for serializing and editing arbitrary data. For example, unlike YAML and JSON, Markdown's hierarchical header structure doesn't rely on indentation or brackets - making it an often preferable solution when editing data with multi-line values. And Markdown's code block syntax allows for easy escaping of more complex content.

## How it works
In Hashdown format, each header in a Markdown document represents a key in an associative array, where the content following and corresponding to the header represents the value of the key. For example, the following .md content would yield the PHP associative array beneath:
```md
# Name of Food
Twinkie

# Serving size
2 cakes

# Calories per serving
280
```
```php
[
  'Name of Food' => 'Twinkie',
  'Serving size' => 2 cakes,
  'Calories per serving' => 280,
]
```

H1s (`#`) become top level keys, while H2s (`##`) become secondary level keys, and so on:
```md
# Serving size
## Amount
2

## Unit
Cakes
```
The above becomes:
```php
[
  'Serving size' => [
    'Amount' => '2',
    'Unit' => 'Cakes',
  ]
]
```
Skipping a header level (e.g. jumping from `#` to `###`) is not allowed, as this would create an invalid array.


### Lists and Sequential arrays
Markdown headers can also be used to produce sequential (rather than associative) arrays. A header with no inline text (e.g. a lone hash `#`) will simply increment the key. The two documents below are equivalent, and correspond with the PHP array beneath:
```md
# Ingredients
##
sugar
##
water
##
enriched flour
```
```md
# Ingredients
## 0
sugar
## 1
water
## 2
enriched flour
```
```php
[
  'Ingredients' => [
    'sugar',
    'water',
    'enriched flour',
  ]
]
```

For list items with scalar values (like those shown above), a shorthand "dash" (`-`), syntax can be used instead of hashes (`#`). The following .md document is equivalent to the two above:
```md
# Ingredients
- sugar
- water
- enriched flour
```

"Dash" style list values can span multiple lines. The following list is valid and equivalent to the PHP array shown beneath:
```md
- first line,
second line

-
another list item
with multiple lines
```
```php
[
  'first line,\nsecond line',
  'another list item\nwith multiple lines'
]
```

But non-scalar values must fall under a "hash" style header. The first example below is valid, but the second is not, as the desired data structure can become ambiguous:
```md
#
## Name
Twinkie
## Ingredients
- sugar
- water

#
## Name
Diet Coke
```
```md
-
## Name
Twinkie
## Ingredients
- sugar
- water

-
## Name
Diet Coke
```

### Literals and Code blocks
#### Escaping embedded Markdown syntax
If you need to represent Markdown as scalar content within your .md document, you can escape it using Markdown's code block syntax.

A "literal" or "code block" section is designated by three or more tick marks (<code>\`\`\`</code>). The `data` key below has a child node called `title`, while the `content` node is just a string of Markdown text:
````md
# data
## title
A Tale of Two Cities
# content
```
# Chapter 1
It was the best of times...
```
````

#### Expressing whitespace
Normally, Hashdown ignores blank lines and leading or trailing spaces. For example, the following two documents are equivalent, as the spaces and blank lines in the second document will be removed / ignored by the Hashdown parser:
```md
# key
some text
some more text
```
```md
# key
  some text


some more text
```

However, if placed within a "literal" block, the leading spaces and blank lines will be preserved:
````md
# key
```
  some text


some more text
```
````

#### Escaping / nesting literals
Literals can be nested within literals. The outer-most layer must have the most tick marks. If a literal is initiated with 5 tick marks, anything goes until the next line with 5 tick marks:
``````md
`````
# This is a literal initiated with 5 tick marks

````
# this is a nested literal, designated by 4 tick marks

```
# this is a doubly nested literal, designated by 3 tick marks

```
````
`````
# This is outside the literal, since the line above has 5 tick marks
``````

### Comments
Hashdown supports standard Markdown/HTML comments (`<!-- ... -->`) for annotating files by hand. Comments are ignored when parsing **outside** fenced literals. Inside a fenced literal, `<!-- -->` is preserved as part of the value.

Comments may appear as:
- **Full-line** annotations between keys or scalar lines
- **Multi-line** blocks spanning several lines
- **Inline** notes on the same line as a header, list item, or scalar text

```md
<!-- file note -->

# Name
Jane <!-- display name -->

# Notes
First line <!-- inline -->
Second line

<!--
multi-line
comment
-->

# content
```
# header <!-- preserved in literal -->
```
```

When writing from PHP, comments are not re-emitted. File-origin round-trips preserve PHP values, not the original comment text.

### Auto-typing, escaping, and round-trips
By default, Hashdown auto-types plain scalar text when parsing. The following table summarizes common cases:

| Plain text | Parsed as |
|---|---|
| `true` / `false` | boolean |
| `null` | `null` |
| `123` | integer |
| `3.14` / `3.0` | float |
| `1.23e4` / `-1.23e4` | float (scientific notation) |
| `007` | string (leading zeros are preserved) |
| anything else | string |

Scientific notation is recognized on read when the text matches a float literal — for example, `1.23e4` becomes the PHP float `12300.0`. To keep scientific notation as a **string** (e.g. the four-character string `1e6`), wrap it in inline backticks or use a fenced literal / `string` type hint.

When **writing** floats, Hashdown uses decimal notation (e.g. `12300.0` rather than `1.23e4`). The numeric value is preserved on round-trip, but the exact text may change unless the value is stored as a string.

#### Inline backticks
A scalar wrapped in a **single** pair of backticks is always read as a string, with the backticks removed. Use this when the text looks like a boolean, number, or `null`, but should remain a string:

```md
# string-true
`true`

# string-int
`123`

# string-scientific
`1e6`
```

The above evaluate to the PHP strings `'true'`, `'123'`, and `'1e6'` — not a boolean, integer, or float.

Inline backticks are for **single-line** values. Multi-line strings that need whitespace or Markdown syntax preserved should use fenced literals (three or more backticks), as described above.

#### Lossless round-trip serialization
When writing with `write_to_file` or `s_stringify_x`, Hashdown formats scalars so that reading the file back yields the same PHP values:

| PHP value | Written as |
|---|---|
| `null` | `null` |
| `true` / `false` | `true` / `false` |
| integer | decimal text (e.g. `123`) |
| float | decimal text; whole-number floats include a fractional part (e.g. `3.0`, not `3`) |
| string | plain text when unambiguous |
| ambiguous string | inline backticks (e.g. `` `true` ``, `` `123` ``, `` `1e6` ``) |
| string with leading zeros | plain text (e.g. `007`) |
| string with whitespace, `#`, `-`, or multiple lines | fenced literal |

A string is considered **ambiguous** when the same text would auto-type to a different PHP value on read. The serializer detects this automatically — you do not need to add backticks yourself when writing from PHP.

Examples of unambiguous strings that are written without backticks: `007`, `"123"`, and ordinary text that does not match boolean, null, or numeric literals.

Examples of ambiguous strings that receive backticks on write: `'true'`, `'123'`, `'1e6'`, `'null'`.

Whole-number floats are written with an explicit decimal (e.g. `4.0`) so they are not mistaken for integers on read. Numeric type hints in fenced blocks (see below) are generally **not** needed for round-trips — they remain useful when hand-editing files and you want authoritative coercion regardless of the payload text.

### Explicit scalar type hints
Scalar values can also be marked with an explicit type hint by placing the hint on the opening fence of a fenced block. Supported hints are `int`, `float`, `bool`, `null`, and `string`.

Examples:
```md
# fenced-int
```int
123abc
```
```
This evaluates to the PHP integer `123`.

```md
# fenced-float
```float
3
```
```
This evaluates to the PHP float `3.0`.

```md
# fenced-bool
```bool
0
```
```
This evaluates to the PHP boolean `false`.

```md
# fenced-null
```null
anything
```
```
This evaluates to the PHP value `null`.

With explicit hints, Hashdown uses the hint as authoritative and coerces the value accordingly. In practice this means:
- `int` and `float` use PHP-style numeric coercion
- `bool` uses PHP-style boolean coercion, including the semantics of whitespace and empty strings
- `null` always becomes `null`, regardless of the payload content
- `string` preserves the value as a string

A future enhancement is planned for an `undefined`/`undef` sentinel that would omit the key entirely rather than producing `null`.

## Limits and performance

Hashdown does not impose its own file-size limit. Practical limits come from PHP: the entire file and the resulting PHP array are held in memory at once. There is no streaming parse or write today.

### What to expect

| Factor | Behavior |
|---|---|
| **Memory** | Peak usage is often several times the file size — the file is loaded as an array of lines, a full PHP array is built, and writes buffer the complete markdown string in memory. |
| **Typical use** | Page-builder-scale files (nested blocks, lists, comments) parse and write in a few milliseconds. |
| **Large dash lists** | Lists using `-` shorthand parse in linear time. Hundreds of thousands of items are practical on default PHP memory settings. |
| **Many flat keys** | Very wide structures (hundreds of thousands of top-level keys) are more memory-intensive than lists or nested trees. |
| **Large single values** | Multi-megabyte string values are supported, but the string exists in memory on both read and write. |

### When things fail

If PHP runs out of memory, you will see a fatal error such as `Allowed memory size of ... bytes exhausted`. Hashdown does not catch this — raise `memory_limit` in `php.ini` or your runtime if you need to process larger files.

With PHP's default `memory_limit` of `128M`, rough comfort zones are:

| `memory_limit` | Rough comfort zone |
|---|---|
| 128M (common default) | Low- to mid-megabyte files; hundreds of thousands of list items or ~250k flat keys |
| 256M | Tens of megabytes |
| 512M+ | Larger blobs and 500k+ flat keys |

Exact limits depend on structure: many small keys cost more per entry than a few nested nodes or a single large literal.

### Errors you may see

| Cause | Symptom |
|---|---|
| `memory_limit` exceeded | PHP fatal error (most common at scale) |
| `max_execution_time` exceeded | `Maximum execution time exceeded` (if configured) |
| Write failure | `Failed to write to file...` |
| Invalid structure | `Invalid node depth at line N...` |

For very large files in the future, streaming parse/write (processing incrementally without loading everything into memory) would be the architectural next step. That is not implemented today.

## Code examples
### Reading from an .md file
Use Hashdown's static `x_read_file` method to read from / deserialize an .md file:
```php
use Neckberg\Hashdown\Hashdown;

$x_groceries = Hashdown::x_read_file( '.../Groceries.md' );
```
Given the following `Groceries.md` document, the above code would set `$x_groceries` to the PHP array shown beneath:
```md
# Groceries
##
### Name
Twinkie
### Ingredients
- sugar
- water
- enriched flour

##
### Name
Diet Coke
### Ingredients
- carbonated water
- caramel color
- aspartame
```
```php
[
  'Groceries' => [
    'Name' => 'Twinkie',
    'Ingredients' => [
      'sugar',
      'water',
      'enriched flour',
    ],
    'Name' => 'Diet Coke',
    'Ingredients' => [
      'carbonated water',
      'caramel color',
      'aspartame',
    ],
  ],
];
```

### Writing to an .md file
Use Hashdown's static `write_to_file` method to write to an .md file.

The php code below will produce a `Groceries.md` file with the content shown beneath:
```php
use Neckberg\Hashdown\Hashdown;

$x_groceries = [
  'Groceries' => [
    'Name' => 'Twinkie',
    'Ingredients' => [
      'sugar',
      'water',
      'enriched flour',
    ],
    'Name' => 'Diet Coke',
    'Ingredients' => [
      'carbonated water',
      'caramel color',
      'aspartame',
    ],
  ],
];
Hashdown::write_to_file($x_groceries, '.../Groceries.md');
```
```md
# Groceries
## 0
### Name
Twinkie

### Ingredients
- sugar
- water
- enriched flour

## 1
### Name
Diet Coke

### Ingredients
- carbonated water
- caramel color
- aspartame
```

#### Formatting options
By default, `write_to_file` will use the shorthand "dash" lists and explicitly numbered sequential array items (as shown above). But this behavior can be changed via the 3rd and 4th parameters:
- `b_no_shorthand_lists`, bool: If true, don't use shorthand "dash" syntax for any lists. Only use "hash" syntax.
- `b_omit_numeric_array_keys`, bool: If true, omit explicit key values for sequential numeric arrays.

Assuming the same `$x_groceries` variable defined above, the following calls will produce the output beneath:

##### Allow shorthand "dash" lists, but omit sequential keys where possible
```php
Hashdown::write_to_file($x_groceries, '.../Groceries.md', false, true);
```
```md
# Groceries
##
### Name
Twinkie

### Ingredients
- sugar
- water
- enriched flour

##
### Name
Diet Coke

### Ingredients
- carbonated water
- caramel color
- aspartame
```
##### Only allow "hash" style lists, but show explicit sequential key numbers
```php
Hashdown::write_to_file($x_groceries, '.../Groceries.md', true, false);
```
```md
# Groceries
## 0
### Name
Twinkie

### Ingredients
#### 0
sugar

#### 1
water

#### 2
enriched flour

## 1
### Name
Diet Coke

### Ingredients
#### 0
carbonated water

#### 1
caramel color

#### 2
aspartame
```

##### Only allow "hash" style lists, and omit sequential keys where possible
```php
Hashdown::write_to_file($x_groceries, '.../Groceries.md', false, false);
```
```md
# Groceries
##
### Name
Twinkie

### Ingredients
####
sugar

####
water

####
enriched flour

##
### Name
Diet Coke

### Ingredients
####
carbonated water

####
caramel color

####
aspartame
```

### Reading and writing to / from strings and arrays
In addition to writing and reading directly to and from .md files, you can also manipulate md strings directly, using the following functions:
#### x_parse_md_string
Accepts a string of Markdown content, and returns a corresponding PHP associative array, or false on failure.
##### Parameters
- string `$s_hd_content` String representing a Markdown document
- string `$s_line_delimeter` The string marking the boundary between lines in the file. Default is PHP_EOL.

#### x_parse_md_lines
Accepts an array of Markdown lines, and returns a corresponding PHP associative array, or false on failure.
##### Parameters
- array `$a_hd_lines` Array of lines of a Markdown document

#### s_stringify_x
Accepts a PHP associative array or object, and returns a corresponding Markdown string.
##### Parameters
- mixed `$x_data` The associative array or object to be converted.
- bool `$b_no_shorthand_lists` If true, don't use shorthand "dash" syntax for any lists
- bool `$b_omit_numeric_array_keys` If true, omit explicit key values for sequential numeric arrays

## Testing
- cd to the directory
- composer install
- run `vendor/bin/phpunit`
