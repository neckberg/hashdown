<?php

use PHPUnit\Framework\TestCase;
use Neckberg\Hashdown\Hashdown;

class HashdownTest extends TestCase {

  public function testParseFile () {
    $this->assertParsedMdMatchesString('blank', '');
    $this->assertParsedMdMatchesString('single-scalar-value', 'lorem ipsum');
    $this->assertParsedMdMatchesString('single-scalar-value-literal', '# lorem ipsum' . PHP_EOL . PHP_EOL . '## lorem ipsum' . PHP_EOL);
    $this->assertParsedMdMatchesString('literals-within-literals', rtrim(file_get_contents(__DIR__ . '/data/literals-within-literals.txt')));

    $this->assertParsedMdMatchesCorrespondingJson('todo-list');
    $this->assertParsedMdMatchesCorrespondingJson('list-of-paragraphs');

    $this->assertParsedMdMatchesCorrespondingJson('person');
    $this->assertParsedMdMatchesCorrespondingJson('person-first-last-name');
    $this->assertParsedMdMatchesCorrespondingJson('page-builder');
  }

  private function assertParsedMdMatchesCorrespondingJson(string $s_filename) {
    $x_from_md = Hashdown::x_read_file( __DIR__ . '/data/' . $s_filename . '.md' );
    $a_from_json = json_decode( file_get_contents( __DIR__ . '/data/' . $s_filename . '.json'), true );
    // file_put_contents( __DIR__ . '/data/log_' . $s_filename . '_json.txt', print_r($x_from_json, 1) );
    // file_put_contents( __DIR__ . '/data/log_' . $s_filename . '_md.txt', print_r($x_from_md, 1) );

    $this->assertSame(
      $a_from_json,
      $x_from_md,
      'Array from ' . $s_filename . '.md should match that from ' . $s_filename . '.json',
    );
  }

  private function assertParsedMdMatchesString(string $s_filename, string $s_expected_value) {
    $x_from_md = Hashdown::x_read_file( __DIR__ . '/data/' . $s_filename . '.md' );
    $this->assertSame(
      $s_expected_value,
      $x_from_md,
      'Value from ' . $s_filename . '.md should be "' . $s_expected_value . '"',
    );
  }

  public function testGenerateString () {
    $this->assertGeneratedMdFromJsonMatchesString('single-scalar-value', s_source_file_extension: 'txt');
    $this->assertGeneratedMdFromJsonMatchesString('list', file_get_contents( __DIR__ . '/data/list-shorthand.txt'));
    $this->assertGeneratedMdFromJsonMatchesString('list', b_shorthand_lists: false);
    $this->assertGeneratedMdFromJsonMatchesString('todo-list', file_get_contents( __DIR__ . '/data/todo-list-shorthand.txt'));
    $this->assertGeneratedMdFromJsonMatchesString('todo-list', b_shorthand_lists: false);
    $this->assertGeneratedMdFromJsonMatchesString('list-of-paragraphs', file_get_contents( __DIR__ . '/data/list-of-paragraphs-shorthand.txt'));
    $this->assertGeneratedMdFromJsonMatchesString('list-of-paragraphs', b_shorthand_lists: false);
    $this->assertGeneratedMdFromJsonMatchesString('page-builder');
  }

  private function assertGeneratedMdFromJsonMatchesString(string $s_filename, string $s_expected_value = null, bool $b_shorthand_lists = true, string $s_source_file_extension = 'json') {
    $x_source_data = rtrim( file_get_contents( __DIR__ . '/data/' . $s_filename . '.' . ($s_source_file_extension ?? 'json')));
    if ($s_source_file_extension === 'json') {
      $x_source_data = json_decode( $x_source_data, true );
    }
    $s_md_format = Hashdown::s_stringify_x($x_source_data, $b_shorthand_lists );

    $this->assertSame(
      trim($s_expected_value ?? file_get_contents( __DIR__ . '/data/' . $s_filename . '.txt')),
      trim($s_md_format),
      'Hashdown string generated from associative array should match provided string: ' . $s_expected_value,
    );
  }
}
