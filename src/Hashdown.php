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
   * @param mixed $o_object The associative array or object to be written.
   * @param string $s_file_name The file name where the Markdown will be saved.
   * @param bool $shorthand_lists Use shorthand syntax for lists if true.
   * @return void
   */
  static function write_hd_to_file($o_object, String $s_file_name, Bool $shorthand_lists = true ) {
    $s_hd_markup = self::s_hd_from_obj($o_object, $shorthand_lists);
    file_put_contents($s_file_name, $s_hd_markup );
  }

  /**
   * Converts a PHP associative array or object to a Markdown string.
   *
   * @param mixed $o_object The associative array or object to be converted.
   * @param bool $shorthand_lists Use shorthand syntax for lists if true.
   * @return string The generated Markdown content.
   */
  static function s_hd_from_obj ( $o_object, Bool $shorthand_lists = true ) {
    ob_start();
    self::echo_hd( $o_object, $shorthand_lists );
    return ob_get_clean();
  }

  /**
   * Echoes a PHP associative array or object as Markdown content.
   *
   * @param mixed $o_object The associative array or object to be echoed.
   * @param bool $shorthand_lists Use shorthand syntax for lists if true.
   * @param int $current_level The current header level for recursive nesting.
   * @return void
   */
  static function echo_hd ($o_object, Bool $shorthand_lists = true, Int $current_level = 1 ) {
    if ( is_object($o_object) ) {
      $o_object = json_decode(json_encode($o_object), true);
    }
    foreach ($o_object as $key => $value) {
      echo str_repeat('#', $current_level) . ' ' . $key;
      echo PHP_EOL;
      if ( is_object($value) ) {
        $value = json_decode(json_encode($value), true);
      }
      if ( is_array($value) ) {
        if ($shorthand_lists) {
          $all_scalar_and_sequential = true;
          $sequential_key = -1;
          foreach ($value as $sub_key => $sub_val) {
            if ( $sub_key === ++$sequential_key && ! is_array($sub_val) ) continue;
            $all_scalar_and_sequential = false;
            break;
          }
          if ($all_scalar_and_sequential) {
            self::echo_list($value);
          }
          else {
            self::echo_hd( $value, $shorthand_lists, $current_level + 1 );
          }
        }
        else {
          self::echo_hd( $value, $shorthand_lists, $current_level + 1 );
        }
      }
      else {
        self::echo_scalar($value);
      }
    }
  }

  /**
   * Echoes a list of values as Markdown list items.
   *
   * @param array $array The list of values to be echoed.
   * @return void
   */
  private static function echo_list ($array) {
    foreach ($array as $value) {
      echo '- ';
      $is_multiline = self::echo_scalar($value, true);
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
   * @param mixed $value The value to be echoed.
   * @param bool $in_list Indicates if the value is part of a list.
   * @return bool Indicates if the value was multiline.
   */
  private static function echo_scalar ($value, $in_list = false) {
    if ( $value === '' || $value === null || $value === false ) {
      echo PHP_EOL;
      return;
    }

    $a_values = explode(PHP_EOL, $value);
    $a_special_chars = [
      // '>' => true,
      '#' => true,
      '-' => true,
      '\\' => true,
    ];
    $needs_to_be_literal = false;
    foreach ($a_values as $key => $value) {
      // echo 'value: ' . $value;

      // if the line is blank, or there's whitespace on either side, we must make it literal in order to preserve it
      // otherwise, the whitespace will be removed when we parse the hashdown markup later
      if ( $value === '' || $value !== trim($value) ) {
        $needs_to_be_literal = true;
      }
      if ( isset($a_special_chars[substr($value, 0, 1)]) ) {
        $needs_to_be_literal = true;
      }
      // if ($value === '```') {
      //   $a_values[$key] = '`' . $value;
      //   $needs_to_be_literal = true;
      // }
      // if ($needs_to_be_literal && substr($value, 0, 3) === '```') {
      //   $a_values[$key] = '`' . $value;
      // }
      if ( substr($value, 0, 3) === '```' ) {
        $a_values[$key] = '`' . $value;
        $needs_to_be_literal = true;
      }
    }
    $is_multiline = $needs_to_be_literal || count($a_values) > 1;
    if ($in_list && $is_multiline ) {
      echo PHP_EOL;
    }
    if ($needs_to_be_literal) echo '```' . PHP_EOL;
    echo implode(PHP_EOL, $a_values) . PHP_EOL;
    if ($needs_to_be_literal) echo '```' . PHP_EOL;

    // self::echo_list function will handle its own new lines
    if ($in_list) return $is_multiline;

    echo PHP_EOL;
    return $is_multiline;
  }

  /**
   * Parses a Markdown file into a PHP associative array.
   *
   * @param string $file_path The path to the Markdown file.
   * @return array|false The associative array representation of the Markdown content, or false on failure.
   */
  static function obj_parse_hd ( String $file_path ) {
    if ( ! file_exists($file_path) ) return false;

    $a_hd_lines = file($file_path, FILE_IGNORE_NEW_LINES);
    $is_in_literal = false;
    $o_object = [];
    $o_object_cursor = &$o_object;
    $a_key_cursor_location = [];
    $i_object_key_current = -1;
    $s_object_key_current = '';
    $a_text_value_current = [];
    $i_list_depth = 0;
    $a_status = [''];
    foreach ($a_hd_lines as $i_line => $s_line) {
      $a_status = self::get_action_for_line($s_line, $a_status, $a_key_cursor_location, $i_list_depth, $o_object, $a_text_value_current);
    }
    self::set_object_key($o_object, $a_key_cursor_location, implode(PHP_EOL, $a_text_value_current));
    $a_text_value_current = [];
    return $o_object;
  }

  /**
   * Determines the action for a line of Markdown content.
   *
   * @param string $s_line The line of Markdown content.
   * @param array $a_status The current parsing status.
   * @param array &$a_key_cursor_location The current location in the array structure.
   * @param int &$i_list_depth The current depth of lists.
   * @param array &$o_object The current associative array being built.
   * @param array &$a_text_value_current The current text value being processed.
   * @return array The updated status.
   */
  private static function get_action_for_line (String $s_line, Array $a_status, &$a_key_cursor_location, &$i_list_depth, &$o_object, &$a_text_value_current) {
    //  if we're within a literal
    if ($a_status[0] === 'within_literal') {
      if ( trim($s_line) === '```' ) return ['end_literal'];
      if ( substr($s_line, 0, 4) === '````' ) {
        array_push($a_text_value_current, substr($s_line, 1) );
        return ['within_literal'];
      }
      array_push($a_text_value_current, $s_line);
      return ['within_literal'];
    }
    if ( trim($s_line) === '```' ) return ['within_literal'];

    // always ignore whitespace if not within literal
    if ( trim($s_line) === '' ) return ['ignore', 'whitespace'];

    // if line is a comment
    if ( substr(trim($s_line), 0, 1) === '\\' ) return ['ignore', 'comment'];  // this is actually just a single, escaped backslash

    $a_line_type = self::is_line_new_key($s_line);

    if ( $a_line_type[0] === false ) {
      array_push($a_text_value_current, trim($s_line));
      return ['within_scalar'];
    }

    // if we made it this far, it means we're about to make a new node
    // if there was any scalar data, we should save it to the waning node before making the new node
    if ($a_text_value_current) {
      self::set_object_key($o_object, $a_key_cursor_location, implode(PHP_EOL, $a_text_value_current));
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
      if ( $a_line_type[2] > $i_max_list_depth ) return ['error', 'invalid_list_depth'];
      $i_relative_hash_depth = $a_line_type[2] - $i_list_depth;
      $i_list_depth = $a_line_type[2];
      for ($i = $i_relative_hash_depth; $i < 1; $i++) {
        array_pop($a_key_cursor_location);  // use array_slice instead: array_slice($food, 0, -3);
      }
      array_push($a_key_cursor_location, self::get_object_next_numeric_key($o_object, $a_key_cursor_location));
      if ( $a_line_type[1] ) {
        array_push($a_text_value_current, $a_line_type[1]);
        return ['new_array'];
      }
      return ['new_array', $a_line_type[1], $a_line_type[2]];
    }
    $i_list_depth = 0;
    if ( $a_line_type[0] === 'object' ) {
      if ( $a_line_type[2] > $i_max_hash_depth ) return ['error', 'invalid_hash_depth'];
      $i_relative_hash_depth = $a_line_type[2] - count($a_key_cursor_location);
      for ($i = $i_relative_hash_depth; $i < 1; $i++) {
        array_pop($a_key_cursor_location);  // use array_slice instead: array_slice($food, 0, -3);
      }
      if ( $a_line_type[1] === '') {
        array_push($a_key_cursor_location, self::get_object_next_numeric_key($o_object, $a_key_cursor_location));
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
  private static function is_line_new_key (String $s_line) {
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
   * @param array &$array The array to check.
   * @param array $keys The current keys path.
   * @return int The next numeric key.
   */
  private static function get_object_next_numeric_key(&$array, $keys = []) {
    $current = &$array;
    foreach($keys as $key) {
      $current = &$current[$key];
      if ( ! is_array($current) ) {
        // if one of the keys isn't an array, then it will be a brand new node, and so the '0' index will be available
        return 0;
      }
    }
    if ( ! is_array($current) ) return 0;

    $highest = 0;
    foreach ($current as $i => $dummy) {
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
   * @param array &$array The associative array.
   * @param array $keys The key path where the value should be set.
   * @param mixed $value The value to set.
   * @return void
   */
  private static function set_object_key(&$array, $keys = [], $value = '') {
    $current = &$array;
    foreach($keys as $key) {
      $current = &$current[$key];
    	if ( ! is_array($current) ) {
    		$current = [];
    	}
    }
    $current = $value;
  }
}
