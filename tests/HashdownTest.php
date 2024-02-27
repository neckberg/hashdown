<?php

use PHPUnit\Framework\TestCase;
use Neckberg\Hashdown\Hashdown;

require_once 'bootstrap.php';

class HashdownTest extends TestCase {

  // public function test42 () {
  //   $this->assertSame(42, 42, '42 must be 42');
  //   $this->assertSame(42, '42', '42 must be 42');
  // }

  public function testParseFile () {
    $o_from_md = Hashdown::obj_parse_hd( __DIR__ . '/data/example-page.md' );
    $o_from_json = json_decode( file_get_contents( __DIR__ . '/data/example-page.json'), true );
    // file_put_contents( __DIR__ . '/data/log.txt', print_r($o_from_json, 1) );
    // file_put_contents( __DIR__ . '/data/log2.txt', print_r($o_from_md, 1) );

    $this->assertSame(
      $o_from_json,
      $o_from_md,
      'Array from MD file should match that from JSON file',
    );
  }

  public function testGenerateString () {
    $o_from_json = json_decode( file_get_contents( __DIR__ . '/data/example-page.json'), true );
    $s_from_array = Hashdown::s_hd_from_obj($o_from_json, true );
    $s_from_file = file_get_contents( __DIR__ . '/data/example-page.md');
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
