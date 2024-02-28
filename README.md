# Hashdown
Reads and parses a strictly formatted .md file into a PHP associative array - or writes a PHP associative array or object to a structured .md file.

Each header in an .md file defines a new key in an associative array, where the content beneath the header is the value of the key. For example, the following .md content would yield the proceeding PHP array:
```md
# Name
Jane

# Eye color
Blue
```
```php
[
  'Name' => 'Jane',
  'Eye color' => 'Blue',
]
```

H1s ('#') become top level keys, while H2s ('##') become secondary level keys, and so on (note that skipping a level is not allowed):
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

A header with no proceeding inline text (e.g. '#', as opposed to '# Header') will simply increment the key:
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

For simple lists, a single dash '-' can be used to designate array items, instead of blank hashes. The following two .md files would define equivalent PHP arrays:
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

## Further examples
### Read from .md file
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
The following declarations would be equivalent:
```php
$arr_from_md = Hashdown::obj_parse_hd( '/path-to-file.md' );
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
write_hd_to_file($arr_person, $arr_person . '.md');
```
```md
# Name
Jane

# Interests
- soccer
- jiu jitsu
```


## testing
- cd to the directory
- composer install
- run `vendor/bin/phpunit`
