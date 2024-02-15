# Hashdown
Reads and writes files resembling an .md file to and from an arbitrary PHP array or object.

Each header in an .md file (designated by a '#') is treated as a key in an associative array - and the content beneath each header is treated as the value of the key.

H1s ('#') become top level keys, while H2s ('##') become secondary level keys, and so on. Skipping a level is not allowed.

## Examples
### Read from .md file
Given the following .md file:
```md
# People
##
### Name
Nathan
### Interests
- soccer
- jiu jitsu

##
### Name
Sara
### Interests
- plants
- animals
```
The following declarations would be equivalent:
```php
$arr_from_md = Hashdown::obj_parse_hd( '/path-to-file.md' );
$arr_from_md = [
  'People' => [
    'Name' => 'Nathan',
    'Interests' => [
      'soccer',
      'jiu jitsu',
    ],
    'Name' => 'Sara',
    'Interests' => [
      'plants',
      'animals',
    ],
  ]
];
```

### Write to .md file
The php code below will produce the `Nathan.md` file shown beneath:
```php
$arr_person = [
  'Name' => 'Nathan',
  'Interests' => [
    'soccer',
    'jiu jitsu',
  ]
];
write_hd_to_file($arr_person, $arr_person . '.md');
```
```md
# Name
Nathan

# Interests
- soccer
- jiu jitsu
```


## testing
- cd to the directory
- composer install
- run `vendor/bin/phpunit`
