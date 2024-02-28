<?php

use PHPUnit\Framework\TestCase;
use Neckberg\Hashdown\Hashdown;

class HashdownTest extends TestCase {

  public function testParseFile () {
    $this->assertParsedMdMatchesString('blank', '');
    $this->assertParsedMdMatchesString('single-scalar-value', 'lorem ipsum');
    $this->assertParsedMdMatchesString('single-scalar-value-literal', '# lorem ipsum' . PHP_EOL . PHP_EOL . '## lorem ipsum' . PHP_EOL);

    $this->assertParsedMdMatchesCorrespondingJson('todo-list');

    $this->assertParsedMdMatchesCorrespondingJson('person');
    $this->assertParsedMdMatchesCorrespondingJson('person-first-last-name');
    $this->assertParsedMdMatchesCorrespondingJson('page-builder');
  }

  private function assertParsedMdMatchesCorrespondingJson(string $s_filename) {
    $o_from_md = Hashdown::obj_parse_hd( __DIR__ . '/data/' . $s_filename . '.md' );
    $o_from_json = json_decode( file_get_contents( __DIR__ . '/data/' . $s_filename . '.json'), true );
    // file_put_contents( __DIR__ . '/data/log.txt', print_r($o_from_json, 1) );
    // file_put_contents( __DIR__ . '/data/log2.txt', print_r($o_from_md, 1) );

    $this->assertSame(
      $o_from_json,
      $o_from_md,
      'Array from ' . $s_filename . '.md should match that from ' . $s_filename . '.json',
    );
  }

  private function assertParsedMdMatchesString(string $s_filename, string $s_expected_value) {
    $o_from_md = Hashdown::obj_parse_hd( __DIR__ . '/data/' . $s_filename . '.md' );
    $this->assertSame(
      $s_expected_value,
      $o_from_md,
      'Value from ' . $s_filename . '.md should be "' . $s_expected_value . '"',
    );
  }

  public function testGenerateString () {
    $o_from_json = json_decode( file_get_contents( __DIR__ . '/data/page-builder.json'), true );
    $s_from_array = Hashdown::s_hd_from_obj($o_from_json, true );
    $s_from_file = file_get_contents( __DIR__ . '/data/page-builder.md');
    // file_put_contents( __DIR__ . '/data/log.txt', $s_from_array );
    // file_put_contents( __DIR__ . '/data/log2.txt', $s_from_file );

    $this->assertSame(
      trim($s_from_file),
      trim($s_from_array),
      'Hashdown string generated from associative array should match that from existing .md file',
    );

    // $o_object = [
    //   'name' => [
    //     'first' => 'first name',
    //     'last' => 'last name'
    //   ],
    //   'address' => ['123', 'maple street'],
    // ];
    // $o_object = obj_parse_hd( __DIR__ . '/example-page.md' );
    // write_hd_to_file($o_object, __DIR__ . '/example-page-printed.md', true );
    // echo s_hd_from_obj($o_object, false);
  }
}
