# Hashdown
Hashdown reads and parses a strictly formatted .md file into a PHP numeric or associative array - or writes a PHP array or object to a structured .md file.

## Why?
Markdown's advantages as a documentation syntax are well recognized - but Markdown also offers advantages for representing arbitrary data. For example, unlike YAML and JSON, Markdown's hierarchical header structure doesn't rely on indentation or brackets - making it a more ideal solution when editing data with multi-line values. And Markdown's code block syntax allows for easy escaping of more complex content.

## How it works
In Hashdown format, each header in an a Markdown document represents a key in an associative array, where the content following and corresponding to the header represents the value of the key. For example, the following .md content would yield the PHP associative array beneath:
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

Markdown headers can also be used to produce sequential (rather than associative) arrays. A header with no inline text (e.g. a lone hash `#`, as opposed to one followed by a string, e.g. `# Some Header / Key String`) will simply increment the key. The two documents below are equivalent, and correspond with the PHP array beneath:
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

For simple lists - like those above, where all of the values are scalar - a shorthand "dash" (`-`), syntax can be used in place of hashes (`#`). The following .md document is equivalent to the two above:
```md
# Ingredients
- sugar
- water
- enriched flour
```
The dash must be followed by either a space or a line break, but can accept multiple lines of data. The following is allowed, and will produce the PHP array beneath:
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

But dash lists can't contain sub headers. The following is not allowed:
```md
-
## a second level key
some value
-
## another key
another value
```

However, if you need to represent actual Markdown within your content, you can escape it within a Markdown code block, designated by three or more tick marks (<code>\`\`\`</code>). The following is valid:
````md
-
```
## a second level key
some value
```
-
```
## another key
another value
```
````

A Markdown code block is interpreted as a "literal". Normally, blank lines and any leading or trailing spaces are ignored. For example, the following two files are equivalent, as the spaces and blank lines in the second file will be removed / ignored by the Hashdown parser:
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

However, if placed within a code block "literal", the leading spaces and blank lines will be preserved:
````md
# key
```
  some text


some more text
```
````

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
### Read from an .md file
Given the following .md file:
```md
# People
##
### Name
Jane
### Interests
- soccer
- jiu jitsu

##
### Name
John
### Interests
- plants
- animals
```
The following two snippets would be equivalent:
```php
$arr_from_md = Hashdown::x_read_file( '/path-to-file.md' );
```
```php
$arr_from_md = [
  'People' => [
    'Name' => 'Jane',
    'Interests' => [
      'soccer',
      'jiu jitsu',
    ],
    'Name' => 'John',
    'Interests' => [
      'plants',
      'animals',
    ],
  ]
];
```

### Write to .md file
The php code below will produce the `Jane.md` file shown beneath:
```php
$arr_person = [
  'Name' => 'Jane',
  'Interests' => [
    'soccer',
    'jiu jitsu',
  ]
];
Hashdown::write_to_file($arr_person, $arr_person['Name'] . '.md');
```
```md
# Name
Jane

# Interests
- soccer
- jiu jitsu
```


## Testing
- cd to the directory
- composer install
- run `vendor/bin/phpunit`
