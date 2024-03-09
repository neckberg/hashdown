<?php

use PHPUnit\Framework\TestCase;
use Neckberg\Hashdown\Hashdown;

class HashdownTest extends TestCase {

  public function testParseFile () {
    $this->assertParsedMdMatchesString('blank', '');
    $this->assertParsedMdMatchesString('single-scalar-value', 'lorem ipsum');
    $this->assertParsedMdMatchesString('single-scalar-value-literal', '# lorem ipsum' . PHP_EOL . PHP_EOL . '## lorem ipsum');
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
    // file_put_contents( __DIR__ . '/tmp/log_' . $s_filename . '_json.txt', print_r($x_from_json, 1) );
    // file_put_contents( __DIR__ . '/tmp/log_' . $s_filename . '_md.txt', print_r($x_from_md, 1) );

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

  public function testWriteFile () {
    $this->assertGeneratedMdFileMatchesExpected('single-scalar-value.txt');
    $this->assertGeneratedMdFileMatchesExpected('single-scalar-value-literal.txt');
    $this->assertGeneratedMdFileMatchesExpected('scalar-nested-literal.txt');
    $this->assertGeneratedMdFileMatchesExpected('list.json');
    $this->assertGeneratedMdFileMatchesExpected('list.json', false, 'hash-lists');
    $this->assertGeneratedMdFileMatchesExpected('todo-list.json');
    $this->assertGeneratedMdFileMatchesExpected('todo-list.json', false, 'hash-lists');
    $this->assertGeneratedMdFileMatchesExpected('list-of-paragraphs.json');
    $this->assertGeneratedMdFileMatchesExpected('list-of-paragraphs.json', false, 'hash-lists');
    $this->assertGeneratedMdFileMatchesExpected('page-builder.json');
    $this->assertGeneratedMdFileMatchesExpected('page-builder.json', false, 'hash-lists');
  }

  private function assertGeneratedMdFileMatchesExpected(string $s_src_filename, bool $b_shorthand_lists = true, string $validation_file_suffix = '' ) {
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
    Hashdown::write_to_file($x_source_data, $s_generated_file_path, $b_shorthand_lists);

    // Assert the file was created
    $this->assertFileExists($s_generated_file_path, 'File should be created: ' . $s_generated_file_path);

    // Assert the file content is as expected
    $this->assertStringEqualsFile(
      $s_generated_file_path,
      file_get_contents(__DIR__ . '/data/' . $s_generated_file_name),
      'File content should match the expected content'
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
}
