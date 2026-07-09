<?php

use PHPUnit\Framework\TestCase;
use Neckberg\Hashdown\Hashdown;

class HashdownTest extends TestCase {

  // public function testNewCase () {
  //   $x_from_md = Hashdown::x_read_file( __DIR__ . '/data/0_src.md');
  //   Hashdown::write_to_file($x_from_md, __DIR__ . '/data/0_tgt.md');
  //   // Hashdown::write_to_file($x_from_md, __DIR__ . '/data/0_tgt.md', false, true);
  //   // Hashdown::write_to_file($x_from_md, __DIR__ . '/data/0_tgt.md', true, false);
  //   // Hashdown::write_to_file($x_from_md, __DIR__ . '/data/0_tgt.md', true, true);
  //   file_put_contents( __DIR__ . '/data/0_tgt.json', json_encode($x_from_md, JSON_PRETTY_PRINT) );
  //   $this->assertSame('hello', 'hello', 'not the same');
  // }
  public function testParseString () {
    $this->assertParsedMdMatchesString('blank', '');
    $this->assertParsedMdMatchesString('single-scalar-value', 'lorem ipsum');
    $this->assertParsedMdMatchesString('single-scalar-value-literal', '# lorem ipsum' . PHP_EOL . PHP_EOL . '## lorem ipsum');
    $this->assertParsedMdMatchesString('literals-within-literals', rtrim(file_get_contents(__DIR__ . '/data/literals-within-literals.txt')));

    $this->assertParsedMdMatchesCorrespondingJson('todo-list', true);
    $this->assertParsedMdMatchesCorrespondingJson('list-of-paragraphs', true);

    $this->assertParsedMdMatchesCorrespondingJson('person', true);
    $this->assertParsedMdMatchesCorrespondingJson('person-first-last-name', true);
    $this->assertParsedMdMatchesCorrespondingJson('blank-key-values', true);
    $this->assertParsedMdMatchesCorrespondingJson('page-builder', true);
  }
  public function testParseFile () {
    $this->assertParsedMdMatchesString('blank', '');
    $this->assertParsedMdMatchesString('single-scalar-value', 'lorem ipsum');
    $this->assertParsedMdMatchesString('single-scalar-value-literal', '# lorem ipsum' . PHP_EOL . PHP_EOL . '## lorem ipsum');
    $this->assertParsedMdMatchesString('literals-within-literals', rtrim(file_get_contents(__DIR__ . '/data/literals-within-literals.txt')));

    $this->assertParsedMdMatchesCorrespondingJson('todo-list');
    $this->assertParsedMdMatchesCorrespondingJson('list-of-paragraphs');

    $this->assertParsedMdMatchesCorrespondingJson('person');
    $this->assertParsedMdMatchesCorrespondingJson('person-first-last-name');
    $this->assertParsedMdMatchesCorrespondingJson('blank-key-values');
    $this->assertParsedMdMatchesCorrespondingJson('page-builder');
    $this->assertParsedMdMatchesCorrespondingJson('auto-typing');
  }

  private function x_get_parsed_data_from_md_file(string $s_file_path, bool $b_get_file_contents_as_string = false) {
    if ($b_get_file_contents_as_string) {
      return Hashdown::x_parse_md_string( file_get_contents($s_file_path) );
    }
    else {
      return Hashdown::x_read_file( $s_file_path );
    }
  }

  private function assertParsedMdMatchesCorrespondingJson(string $s_filename, bool $b_get_file_contents_as_string = false) {
    $x_from_md = $this->x_get_parsed_data_from_md_file( __DIR__ . '/data/' . $s_filename . '.md', $b_get_file_contents_as_string);

    $a_from_json = json_decode( file_get_contents( __DIR__ . '/data/' . $s_filename . '.json'), true );
    // file_put_contents( __DIR__ . '/tmp/log_' . $s_filename . '_json.txt', print_r($x_from_json, 1) );
    // file_put_contents( __DIR__ . '/tmp/log_' . $s_filename . '_md.txt', print_r($x_from_md, 1) );

    $this->assertSame(
      $a_from_json,
      $x_from_md,
      'Array from ' . $s_filename . '.md should match that from ' . $s_filename . '.json',
    );
  }

  private function assertParsedMdMatchesString(string $s_filename, string $s_expected_value, bool $b_get_file_contents_as_string = false) {
    $x_from_md = $this->x_get_parsed_data_from_md_file( __DIR__ . '/data/' . $s_filename . '.md', $b_get_file_contents_as_string);
    $this->assertSame(
      $s_expected_value,
      $x_from_md,
      'Value from ' . $s_filename . '.md should be "' . $s_expected_value . '"',
    );
  }

  public function testWriteFile () {
    $this->assertGeneratedMdFileMatchesExpected('single-scalar-value.txt');
    $this->assertGeneratedMdFileMatchesExpected('single-scalar-value-literal.txt');
    $this->assertGeneratedMdFileMatchesExpected('scalar-nested-literal.txt');
    $this->assertGeneratedMdFileMatchesExpected('list-empty.json');
    $this->assertGeneratedMdFileMatchesExpected('list.json');
    $this->assertGeneratedMdFileMatchesExpected('list.json', true, false, 'hash-lists');
    $this->assertGeneratedMdFileMatchesExpected('list.json', true, true, 'hash-lists_omit-numeric-keys');
    $this->assertGeneratedMdFileMatchesExpected('todo-list-empty.json');
    $this->assertGeneratedMdFileMatchesExpected('todo-list.json');
    $this->assertGeneratedMdFileMatchesExpected('todo-list.json', true, false, 'hash-lists');
    $this->assertGeneratedMdFileMatchesExpected('todo-list.json', true, true, 'hash-lists_omit-numeric-keys');
    $this->assertGeneratedMdFileMatchesExpected('todo-list.json', false, true, 'dash-lists_omit-numeric-keys');
    $this->assertGeneratedMdFileMatchesExpected('list-of-paragraphs.json');
    $this->assertGeneratedMdFileMatchesExpected('list-of-paragraphs.json', true, false, 'hash-lists');
    $this->assertGeneratedMdFileMatchesExpected('list-of-paragraphs.json', true, true, 'hash-lists_omit-numeric-keys');
    $this->assertGeneratedMdFileMatchesExpected('page-builder.json');
    $this->assertGeneratedMdFileMatchesExpected('page-builder.json', true, false, 'hash-lists');
    $this->assertGeneratedMdFileMatchesExpected('page-builder.json', true, true, 'hash-lists_omit-numeric-keys');
    $this->assertGeneratedMdFileMatchesExpected('page-builder.json', false, true, 'dash-lists_omit-numeric-keys');
  }

  public function testParseExplicitScalarTypes() {
    $x_original = [
      'float' => 3.14,
      'float_dot_zero' => 4.0,
      'float_coerced' => (float) 4,
      'bool_true' => true,
      'string_true' => 'true',
      'string_false' => 'false',
      'null_null' => null,
      'string_null' => 'null',
      'int_int' => 123,
      'string_int' => '123',
      'string_leading_zero' => '007',
      'string_scientific' => '1e6',
      'string_quoted' => '"123"',
    ];

    $this->assertWriteReadRoundTrip($x_original, 'write-read-roundtrip-scalar');
  }

  public function testWriteReadRoundTripWhitespace() {
    $x_original = [
      'leading_and_blank_lines' => "  some text\n\n\nsome more text",
      'trailing_space' => 'content...   ',
      'indented_line' => "line one\n    indented",
    ];

    $this->assertWriteReadRoundTrip($x_original, 'write-read-roundtrip-whitespace');
  }

  public function testWriteReadRoundTripMarkdownLiteral() {
    $x_original = [
      'content' => rtrim(file_get_contents(__DIR__ . '/data/single-scalar-value-literal.txt')),
    ];

    $this->assertWriteReadRoundTrip($x_original, 'write-read-roundtrip-markdown-literal');
  }

  public function testWriteReadRoundTripNestedLiteral() {
    $x_original = [
      'content' => rtrim(file_get_contents(__DIR__ . '/data/scalar-nested-literal.txt')),
    ];

    $this->assertWriteReadRoundTrip($x_original, 'write-read-roundtrip-nested-literal');
  }

  private function assertWriteReadRoundTrip(array $x_original, string $s_fixture_basename): void {
    $s_expected_fixture_path = __DIR__ . '/data/' . $s_fixture_basename . '.json.md';
    $s_generated_file_path = __DIR__ . '/tmp/' . $s_fixture_basename . '.generated.md';
    if (file_exists($s_generated_file_path)) {
      unlink($s_generated_file_path);
    }

    Hashdown::write_to_file($x_original, $s_generated_file_path);
    $this->assertFileExists($s_generated_file_path, 'Generated Markdown file should be created.');

    $this->assertSame(
      file_get_contents($s_expected_fixture_path),
      file_get_contents($s_generated_file_path),
      'Generated Markdown should match the expected round-trip fixture.'
    );

    $x_from_md = Hashdown::x_read_file($s_generated_file_path);
    $this->assertSame(
      $x_original,
      $x_from_md,
      'Parsing the generated Markdown should round-trip back to the original values.'
    );

    unlink($s_generated_file_path);
  }

  private function assertGeneratedMdFileMatchesExpected(string $s_src_filename, bool $b_no_shorthand_lists = false, bool $b_omit_numeric_array_keys = false, string $validation_file_suffix = '' ) {
    if ($validation_file_suffix) {
      $validation_file_suffix = '-' . $validation_file_suffix;
    }
    $x_source_data = rtrim(file_get_contents( __DIR__ . '/data/' . $s_src_filename));
    list($s_src_file_short_name, $s_src_file_ext) = $this->a_split_filename($s_src_filename);
    if ($s_src_file_ext === 'json') {
      $x_source_data = json_decode( $x_source_data, true );
    }
    $s_generated_file_name = $s_src_filename . $validation_file_suffix . '.md';
    $s_generated_file_path = __DIR__ . '/tmp/' . $s_generated_file_name;

    // write the .md file
    Hashdown::write_to_file($x_source_data, $s_generated_file_path, $b_no_shorthand_lists, $b_omit_numeric_array_keys);

    // Assert the file was created
    $this->assertFileExists($s_generated_file_path, 'File should be created: ' . $s_generated_file_path);

    // Assert the file content is as expected
    $this->assertStringEqualsFile(
      $s_generated_file_path,
      file_get_contents(__DIR__ . '/data/' . $s_generated_file_name),
      'File content from tmp/' . $s_generated_file_name . ' should match data/' . $s_generated_file_name
    );

    // delete the generated file
    unlink($s_generated_file_path);
  }

  private function a_split_filename(string $s_filename) {
    $x_dot_position = strrpos($s_filename, '.');
    if ($x_dot_position === false) {
        return [$s_filename, ''];
    } else {
        $s_name = substr($s_filename, 0, $x_dot_position);
        $s_extension = substr($s_filename, $x_dot_position + 1);
        return [$s_name, $s_extension];
    }
  }

  public function testReadNonexistentFile () {
    $this->assertThrowExceptionOnRead('<nonexistent filename>', 'Failed to open non-existent file');
  }
  public function testBadListDepth () {
    $this->assertThrowExceptionOnRead('bad-list-depth', 'Invalid node depth at line 4: ###');  // /Users/nathan.eckberg/local-sites/php/app/public/hashdown/tests/data/bad-list-depth.md
  }
  public function testBadHashDepth () {
    $this->assertThrowExceptionOnRead('bad-hash-depth', 'Invalid node depth at line 2: ### 0');  // /Users/nathan.eckberg/local-sites/php/app/public/hashdown/tests/data/bad-hash-depth.md
  }
  public function assertThrowExceptionOnRead (string $s_filename, string $s_message = '') {
    $this->expectException(\Exception::class);
    if ($s_message) {
      $this->expectExceptionMessage($s_message);
    }
    $x_from_md = Hashdown::x_read_file( __DIR__ . '/data/' . $s_filename . '.md' );
  }
}
