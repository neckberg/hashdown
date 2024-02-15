# Hashdown
Reads and writes files resembling an .md file to and from an arbitrary PHP array or object.

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
$o_from_md = Hashdown::obj_parse_hd( '/path-to-file.md' );
$o_from_md = [
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

## testing
- cd to the directory
- composer install
- run `vendor/bin/phpunit`
