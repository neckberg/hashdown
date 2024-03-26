# Hashdown
Hashdown reads and parses a strictly formatted .md file into a PHP numeric or associative array - or writes a PHP array or object to a structured .md file.

## Why?
Markdown's advantages as a documentation syntax are well recognized - but Markdown also offers advantages for serializing and editing arbitrary data. For example, unlike YAML and JSON, Markdown's hierarchical header structure doesn't rely on indentation or brackets - making it a more ideal solution when editing data with multi-line values. And Markdown's code block syntax allows for easy escaping of more complex content.

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
  ]
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
  ]
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

## Testing
- cd to the directory
- composer install
- run `vendor/bin/phpunit`
