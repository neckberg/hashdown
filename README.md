# Hashdown
Hashdown reads and parses a strictly formatted .md file into a PHP numeric or associative array - or writes a PHP array or object to a structured .md file.

## Why?
Markdown's advantages as a documentation syntax are well recognized, but Markdown also offers advantages as a syntax for arbitrary data. For example, unlike YAML and JSON, Markdown's hierarchical header structure doesn't rely on indentation or brackets - making it and ideal solution for any data with multi-line values. And Markdown's code block syntax allows for easy escaping of more complex content.

## How it works
Using Hashdown format, each header in an .md file can represent a key in an associative array, where the content following and corresponding to the header can represent the value of the key. For example, the following .md content would yield the PHP array beneath:
```md
# Name
Jane

# Eye color
Blue
```

The above .md file would produce the following PHP associative array:
```php
[
  'Name' => 'Jane',
  'Eye color' => 'Blue',
]
```

H1s (`#`) become top level keys, while H2s (`##`) become secondary level keys, and so on:
```md
# Name
## First
Jane

## Last
Doe
```
The above becomes:
```php
[
  'Name' => [
    'First' => 'Jane',
    'Last' => 'Doe',
  ]
]
```
Skipping a header level is not allowed, as this would create an invalid array. (Hashdown format is therefore a subset of Markdown.)

Markdown headers can also be used to produce numeric (rather than associative) arrays. A header with no inline text (e.g. a lone hash `#`, as opposed to one followed by a string `# Some Header String`) will simply increment the key:
```md
# Name
Jane

# Interests
##
soccer
##
jiu jitsu
```
The above becomes:
```php
[
  'Name' => 'Jane',
  'Interests' => [
    'soccer',
    'jiu jitsu',
  ]
]
```

For simple lists, a single dash `-` can be used to designate array items, instead of blank hashes. The following two .md files would define equivalent PHP arrays:
```md
# Name
Jane

# Interests
##
soccer
##
jiu jitsu
```
```md
# Name
Jane

# Interests
- soccer
- jiu jitsu
```

Dash-based simple lists can have multiple lines of data as well, for example:
```md
- some value
with multiple lines
- another value
with multiple lines
```

But they can't have sub headers. The following is not allowed:
```md
-
## some key
some value
-
## some key
some value
```

However, if you need to represent actual Markdown within your content, you can escape it within a Markdown code block (<code>```</code>). The following is valid:
<pre lang="md"><code>
-
```
## some key
some value
```
-
```
## some key
some value
```
</code></pre>

A code block is interpreted as a "literal". Normally, blank lines and any leading or trailing spaces are ignored. For example, the following two files are equivalent, as the spaces and blank lines in the second file will be removed / ignored by the Hashdown parser:
```md
# key
value
value
```
```md
# key
 value


value
```

However, if placed within a code block "literal", the spaces and blank lines will be preserved:
<pre lang="md"><code>
# key
```
 value


value
```
</code></pre>

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
$arr_from_md = Hashdown::obj_parse_hd( '/path-to-file.md' );
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
Hashdown::write_hd_to_file($arr_person, $arr_person['Name'] . '.md');
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
