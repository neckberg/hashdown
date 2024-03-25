# todo
## necessary
- dash lists with numeric keys should be the default
- add notes on how to contribute
- add more README.md content (e.g. for literals), and add phpunit tests for all the examples in the README file
- submit to Packagist

## discretionary
- add parameter for not writing blank lines, e.g. condensed or minified version
- node depth errors: fix edge cases and exception message node depth and underlying code
- remove Neckberg from namespace?
- refactor parse functions
- add support for md comments. actually, I think there is support. Just need to add tests, or change how it works if desired

# done
- properly print simple list items that contain literal scalar values
- prettier line breaks for simple lists with single and multiline scalar values
- add parameter to print "pretty" simple scalar lists (i.e. as dashes), or "ugly" ones (i.e. hashes)
- put into class with composer / autoloading capability
- add phpunit tests
- add basic readme explanation: "reads and writes files resembling an .md file to and from an arbitrary PHP array (or object)"
- add `$b_omit_sequential_keys` or `$b_omit_keys_for_numeric_arrays` parameter to omit numbers for sequential hash keys (e.g. '## 0' would be written just as '##')
- reverse nested code block tick syntax. the outside container should have the most ticks: [For fenced code blocks, the prescription is more backticks.](https://meta.stackexchange.com/questions/82718/how-do-i-escape-a-backtick-within-in-line-code-in-markdown)
- allow language types when opening literals (e.g. ```html...```)
- refactor / add type declarations where appropriate: https://www.php.net/manual/en/language.types.declarations.php

## add composer
### composer init with psr-4 autoloading
```shell
This command will guide you through creating your composer.json config.

Package name (<vendor>/<name>) [neckberg/hashdown]:
Description []:
Author [Nathan Eckberg <nathan.eckberg@gmail.com>, n to skip]:
Minimum Stability []:
Package Type (e.g. library, project, metapackage, composer-plugin) []:
License []: MIT

Define your dependencies.

Would you like to define your dependencies (require) interactively [yes]? no
Would you like to define your dev dependencies (require-dev) interactively [yes]? no
Add PSR-4 autoload mapping? Maps namespace "Neckberg\Hashdown" to the entered relative path. [src/, n to skip]:

{
    "name": "neckberg/hashdown",
    "license": "MIT",
    "autoload": {
        "psr-4": {
            "Neckberg\\Hashdown\\": "src/"
        }
    },
    "authors": [
        {
            "name": "Nathan Eckberg",
            "email": "nathan.eckberg@gmail.com"
        }
    ],
    "require": {}
}

Do you confirm generation [yes]?
Generating autoload files
Generated autoload files
Would you like the vendor directory added to your .gitignore [yes]?
PSR-4 autoloading configured. Use "namespace NathanEckberg\Hashdown;" in src/
Include the Composer autoloader with: require 'vendor/autoload.php';
```

## phpunit
cd /Users/nathan.eckberg/local-sites/php/app/public/hashdown
vendor/bin/phpunit
```shell
PHPUnit 11.0.2 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.2.3
Configuration: /Users/nathan.eckberg/local-sites/php/app/public/hashdown/phpunit.xml

F                                                                   1 / 1 (100%)

Time: 00:00.010, Memory: 8.00 MB

There was 1 failure:

1) HashdownTest::test42
42 must be 42
Failed asserting that '42' is identical to 42.

/Users/nathan.eckberg/local-sites/php/app/public/hashdown/test/HashdownTest.php:11

FAILURES!
Tests: 1, Assertions: 2, Failures: 1.
```
