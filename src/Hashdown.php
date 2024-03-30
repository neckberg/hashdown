<?php
namespace Neckberg\Hashdown;

/**
 * Hashdown class for parsing Markdown into associative arrays and vice versa.
 *
 * This class provides functionality to convert Markdown content into associative arrays
 * and to generate Markdown from associative arrays or objects. It supports nested structures
 * with headers defining the keys and content as values.
 *
 * @package Neckberg\Hashdown
 * @link https://github.com/neckberg/hashdown
 * @license MIT License https://github.com/neckberg/hashdown/blob/main/LICENSE
 * @author Nathan Eckberg
 */

class Hashdown {

  /**
   * Writes a PHP associative array or object to a Markdown file.
   *
   * @param mixed $x_data The associative array, object, or scalar to be written.
   * @param string $s_file_name The file name where the Markdown will be saved.
   * @param bool $b_no_shorthand_lists If true, don't use shorthand "dash" syntax for any lists
   * @param bool $b_omit_numeric_array_keys If true, omit explicit key values for sequential numeric arrays
   * @return void
   */
  static function write_to_file ($x_data, string $s_file_name, bool $b_no_shorthand_lists = false, bool $b_omit_numeric_array_keys = false) {
    $s_hd_markup = self::s_stringify_x($x_data, $b_no_shorthand_lists, $b_omit_numeric_array_keys);
    if ( file_put_contents($s_file_name, $s_hd_markup) === false ) {
      throw new \Exception('Failed to write to file ' . $s_file_name . '. Check permissions and file path.');
    }
  }

  /**
   * Converts a PHP associative array or object to a Markdown string.
   *
   * @param mixed $x_data The associative array or object to be converted.
   * @param bool $b_no_shorthand_lists If true, don't use shorthand "dash" syntax for any lists
   * @param bool $b_omit_numeric_array_keys If true, omit explicit key values for sequential numeric arrays
   * @return string The generated Markdown content.
   */
  static function s_stringify_x ( $x_data, bool $b_no_shorthand_lists = false, bool $b_omit_numeric_array_keys = false ) {
    ob_start();
    self::echo_hd( $x_data, $b_no_shorthand_lists, $b_omit_numeric_array_keys);
    return trim(ob_get_clean()) . PHP_EOL;
  }

  /**
   * Echoes a PHP associative array or object as Markdown content.
   *
   * @param mixed $x_data The associative array or object to be echoed.
   * @param bool $b_no_shorthand_lists If true, don't use shorthand "dash" syntax for any lists
   * @param bool $b_omit_numeric_array_keys If true, omit explicit key values for sequential numeric arrays
   * @param int $i_current_level The current header level for recursive nesting.
   * @return void
   */
  static function echo_hd ($x_data, bool $b_no_shorthand_lists = false, bool $b_omit_numeric_array_keys = false, int $i_current_level = 1 ) {
    $b_shorthand_lists = (!$b_no_shorthand_lists);
    if ( is_object($x_data) ) {
      $x_data = json_decode(json_encode($x_data), true);
    }
    if ( ! is_array($x_data) ) {
      self::b_echo_scalar($x_data);
      return;
    }
    if ($b_shorthand_lists || $b_omit_numeric_array_keys) {
      $b_all_scalar = true;
      $b_all_sequential = true;
      $i_sequential_key = -1;
      foreach ($x_data as $s_key => $x_value) {
        if ( $s_key !== ++$i_sequential_key ) {
          $b_all_sequential = false;
          break;
        }
        if ( ! $b_shorthand_lists) continue;
        if ( is_array($x_value) ) {
          $b_all_scalar = false;
          break;
        }
      }
    }
    if ($b_shorthand_lists && $b_all_scalar && $b_all_sequential) {
      self::echo_list($x_data);
      return;
    }
    $b_omit_keys = ($b_omit_numeric_array_keys && $b_all_sequential);
    foreach ($x_data as $s_key => $x_value) {
      echo str_repeat('#', $i_current_level);
      if ( ! $b_omit_keys ) {
        echo ' ' . $s_key;
      }
      echo PHP_EOL;
      self::echo_hd( $x_value, $b_no_shorthand_lists, $b_omit_numeric_array_keys, $i_current_level + 1 );
    }
  }

  /**
   * Echoes a list of values as Markdown list items.
   *
   * @param array $a_array The list of values to be echoed.
   * @return void
   */
  private static function echo_list (array $a_array) {
    foreach ($a_array as $x_value) {
      echo '-';
      $is_multiline = self::b_echo_scalar($x_value, true);
      if ( $is_multiline) {
        echo PHP_EOL;
      }
    }
    // if it was multiline, a new line will have already been added above
    if ( ! $is_multiline) {
      echo PHP_EOL;
    }
  }

  /**
   * Echoes a scalar value, handling special characters and multiline content.
   *
   * @param string $s_value The value to be echoed.
   * @param bool $is_in_list Indicates if the value is part of a list.
   * @return bool Indicates if the value was multiline.
   */
  private static function b_echo_scalar (?string $s_value, $is_in_list = false) {
    if ( $s_value === '' || $s_value === null || $s_value === false ) {
      echo PHP_EOL;
      return;
    }

    $a_values = explode(PHP_EOL, $s_value);
    $a_special_chars = [
      // '>' => true,
      '#' => true,
      '-' => true,
      '\\' => true,
    ];
    $b_needs_to_be_literal = false;
    $i_literal_size = 3;
    foreach ($a_values as $s_key => $s_value) {
      // if the line is blank, or there's whitespace on either side, we must make it literal in order to preserve it
      // otherwise, the whitespace will be removed when we parse the hashdown markup later
      if ( $s_value === '' || $s_value !== trim($s_value) ) {
        $b_needs_to_be_literal = true;
      }
      if ( isset($a_special_chars[substr($s_value, 0, 1)]) ) {
        $b_needs_to_be_literal = true;
      }
      $i_literal_signature = self::i_leading_target_character_count('`', $s_value);
      if ($i_literal_signature > $i_literal_size) {
        $i_literal_size = $i_literal_signature + 1;
        $b_needs_to_be_literal = true;
      }
    }
    $is_multiline = $b_needs_to_be_literal || count($a_values) > 1;
    if ($is_in_list) {
      echo $is_multiline ? PHP_EOL : ' ';  // add either a space or a newline after the list dash
    }
    if ($b_needs_to_be_literal) echo str_repeat('`', $i_literal_size) . PHP_EOL;
    echo implode(PHP_EOL, $a_values) . PHP_EOL;
    if ($b_needs_to_be_literal) echo str_repeat('`', $i_literal_size) . PHP_EOL;

    // self::echo_list function will handle its own new lines
    if ($is_in_list) return $is_multiline;

    echo PHP_EOL;
    return $is_multiline;
  }

  /**
   * Calculates the number of consecutive occurrences of a specific character at the start of a string.
   *
   * @param string $s_character The target character to count occurrences of at the beginning of the string.
   * @param string $s The string to search within for the target character.
   * @return int The number of consecutive occurrences of the target character at the start of the string.
   *             If the target character is not found, returns the length of the string.
   */
  private static function i_leading_target_character_count(string $s_character, string $s) {
    for ($i = 0; $i < strlen($s); $i++) {
      if ($s[$i] !== $s_character) {
        return $i;
      }
    }
    return strlen($s);
  }

  /**
   * Parses a Markdown file into a PHP associative array.
   *
   * @param string $s_file_path The path to the Markdown file.
   * @return array|false The associative array representation of the Markdown content, or false on failure.
   */
  static function x_read_file ( string $s_file_path ) {
    if ( ! file_exists($s_file_path) ) {
      throw new \Exception('Failed to open non-existent file: ' . $s_file_path);
    }

    $a_md_lines = file($s_file_path, FILE_IGNORE_NEW_LINES);
    return self::x_parse_md_lines($a_md_lines, $s_file_path);
  }

  /**
   * Parses an array of Markdown lines into a PHP associative array.
   *
   * @param array $a_hd_lines Array of lines of a Markdown document
   * @param string $s_file_path The path to the Markdown file being parsed. only used for exception messaging.
   * @return array|false The associative array representation of the Markdown content, or false on failure.
   */
  static function x_parse_md_lines ( array $a_hd_lines, string $s_file_path = '' ) {
    $is_in_literal = false;
    $x_data = [];
    $x_data_cursor = &$x_data;
    $a_key_cursor_location = [];
    $i_object_key_current = -1;
    $s_object_key_current = '';
    $a_text_value_current = [];
    $i_list_depth = 0;
    $a_status = [''];
    foreach ($a_hd_lines as $i_line => $s_line) {
      $a_status = self::a_get_action_for_line($s_line, $a_status, $a_key_cursor_location, $i_list_depth, $x_data, $a_text_value_current, $i_line, $s_file_path);
    }
    self::set_object_key($x_data, $a_key_cursor_location, implode(PHP_EOL, $a_text_value_current));
    $a_text_value_current = [];
    return $x_data;
  }

  /**
   * Determines the action for a line of Markdown content.
   *
   * @param string $s_line The line of Markdown content.
   * @param array $a_status The current parsing status.
   * @param array &$a_key_cursor_location The current location in the array structure.
   * @param int &$i_list_depth The current depth of lists.
   * @param array &$x_data The current associative array being built.
   * @param array &$a_text_value_current The current text value being processed.
   * @param int $i_line The current line number of the file being processed.
   * @param string $s_file_path The path to the file being processed.
   * @return array The updated status.
   */
  private static function a_get_action_for_line (string $s_line, array $a_status, &$a_key_cursor_location, &$i_list_depth, &$x_data, &$a_text_value_current, int $i_line, string $s_file_path) {

    //  handle literals
    $i_literal_signature = self::i_leading_target_character_count('`', $s_line);
    if ($a_status[0] === 'within_literal') {
      if ($i_literal_signature === $a_status[1]) return ['end_literal'];
      array_push($a_text_value_current, $s_line);
      return $a_status;
    }
    if ($i_literal_signature > 2) return ['within_literal', $i_literal_signature];

    // always ignore whitespace if not within literal
    if ( trim($s_line) === '' ) return ['ignore', 'whitespace'];

    // // if line is a comment
    // if ( substr(trim($s_line), 0, 1) === '\\' ) return ['ignore', 'comment'];  // this is actually just a single, escaped backslash

    $a_line_type = self::a_line_type_summary($s_line);

    if ( $a_line_type[0] === false ) {
      array_push($a_text_value_current, trim($s_line));
      return ['within_scalar'];
    }

    // if we made it this far, it means we're about to make a new node
    // save off the waning node before making the new one
    if ($a_key_cursor_location) {
      self::set_object_key($x_data, $a_key_cursor_location, implode(PHP_EOL, $a_text_value_current));
      $a_text_value_current = [];
    }

    $i_max_hash_depth = ( count($a_key_cursor_location) - $i_list_depth ) + 1;
    $i_max_list_depth = $i_list_depth + 1;
    // if we are within a scalar data area
    if ($a_status[0] === 'within_scalar') {
      $i_max_list_depth--;
      $i_max_hash_depth--;
    }
    if ($a_status[0] === 'new_array') {
      $i_max_hash_depth--;
    }

    if ( $a_line_type[0] === 'array' ) {
      if ( $a_line_type[2] > $i_max_list_depth ) {
        throw new \Exception('Invalid node depth at line ' . ($i_line + 1) . ': ' . $s_line);
      }

      $i_relative_hash_depth = $a_line_type[2] - $i_list_depth;
      $i_list_depth = $a_line_type[2];
      for ($i = $i_relative_hash_depth; $i < 1; $i++) {
        array_pop($a_key_cursor_location);  // use array_slice instead: array_slice($food, 0, -3);
      }
      array_push($a_key_cursor_location, self::i_get_object_next_numeric_key($x_data, $a_key_cursor_location));
      if ( $a_line_type[1] ) {
        array_push($a_text_value_current, $a_line_type[1]);
        return ['new_array'];
      }
      return ['new_array', $a_line_type[1], $a_line_type[2]];
    }
    $i_list_depth = 0;
    if ( $a_line_type[0] === 'object' ) {
      if ( $a_line_type[2] > $i_max_hash_depth ) {
        throw new \Exception('Invalid node depth at line ' . ($i_line + 1) . ': ' . $s_line);
      }
      $i_relative_hash_depth = $a_line_type[2] - count($a_key_cursor_location);
      for ($i = $i_relative_hash_depth; $i < 1; $i++) {
        array_pop($a_key_cursor_location);  // use array_slice instead: array_slice($food, 0, -3);
      }
      if ( $a_line_type[1] === '') {
        array_push($a_key_cursor_location, self::i_get_object_next_numeric_key($x_data, $a_key_cursor_location));
      }
      else array_push($a_key_cursor_location, $a_line_type[1]);

      return ['new_object'];
    }
  }

  /**
   * Checks if a line indicates a new key in the Markdown structure.
   *
   * @param string $s_line The line to check.
   * @return array An array indicating the line type, key/value, and depth.
   */
  private static function a_line_type_summary (string $s_line) {
    $s_line = ltrim($s_line);
    $a_special_chars = [
      '#' => 'object',
      '-' => 'array',
      // '>' => 1,
      // '\\' => 1,
    ];
    $s_char_0 = $s_line[0];
    $default_return = [false, false, false];  // header, key, and level
    if ( ! isset($a_special_chars[$s_char_0]) ) return $default_return;

    $i_first_space = strpos($s_line, ' ');
    $i_loop_until = $i_first_space ? $i_first_space : strlen($s_line);
    for ($i = 0; $i < $i_loop_until; $i++) {
      $s_char = $s_line[$i];
      if ($s_char !== $s_char_0) return $default_return;
    }
    $is_key_or_value_present = $i_first_space ? $i_first_space < (strlen($s_line) - 1) : false;
    if ($is_key_or_value_present) {
      $s_key_or_value = substr($s_line, $i_first_space + 1);
    }
    return [
      $a_special_chars[$s_char_0],
      $s_key_or_value ?? '',
      $i_loop_until,
    ];
  }

  /**
   * Gets the next numeric key for an object in the array.
   *
   * @param array &$a_array The array to check.
   * @param array $a_keys The current keys path.
   * @return int The next numeric key.
   */
  private static function i_get_object_next_numeric_key (&$a_array, $a_keys = []) {
    $a_current = &$a_array;
    foreach($a_keys as $s_key) {
      $a_current = &$a_current[$s_key];
      if ( ! is_array($a_current) ) {
        // if one of the keys isn't an array, then it will be a brand new node, and so the '0' index will be available
        return 0;
      }
    }
    if ( ! is_array($a_current) ) return 0;

    $highest = 0;
    foreach ($a_current as $i => $dummy) {
      if ( ! is_numeric($i) ) continue;
      if ($i >= $highest) {
        $highest = $i + 1;
      }
    }
    return $highest;
  }

  /**
   * Sets a value in the associative array at the specified key path.
   *
   * @param array &$a_array The associative array.
   * @param array $a_keys The key path where the value should be set.
   * @param mixed $x_value The value to set.
   * @return void
   */
  private static function set_object_key (&$a_array, $a_keys = [], $x_value = '') {
    $a_current = &$a_array;
    foreach($a_keys as $s_key) {
      $a_current = &$a_current[$s_key];
    	if ( ! is_array($a_current) ) {
    		$a_current = [];
    	}
    }
    $a_current = $x_value;
  }
}
