<?php

namespace core\util\string;

/**
 * This class is used for string operations.
 * @author Marc Bredt
 */
class StringUtil {

  /**
   * String representing characters that are escapable for usage in
   * regular expressions like in preg_*() functions. Note that
   * constants are public visible.
   */
  const escapables = "[]";

  /**
   * Escape special characters like '[' or ']' to use them in regular
   * expressions or in preg_*() functions.
   * @param $character character to escape
   * @return escaped character escaped
   */
  public static function escape($character) {
    if(strpos(self::escapables, $character) !== false)
      return "\\".$character;
    else
      return $character;
  }
 
  /**
   * Check if a string has a specific layout.
   * @param $layout preg_match() regexp string
   * @param $haytack string that should match the needle $layout
   * @return true if $string matches $layout
   */
  public static function has_layout($layout = "", $haystack = "") {
    if(strncmp(gettype($layout),"string",6)==0
       && strncmp(gettype($haystack),"string",6)==0)
      return preg_match("/".$layout."/", $haystack);
    return false;
  }

  /**
   * Get offset for last occurrence $needle in $haystack.
   * @param $needle needle to search for
   * @param $haystack haystack to search in
   * @return last position of $needle in $haystack
   * @see SharedMemorySegment
   */
  public static function get_offset_last($needle = "", $haystack = "") {
    if(strncmp(gettype($needle),"string",6)==0
       && strncmp(gettype($haystack),"string",6)==0)
      $lp = strrpos($haystack,$needle);
      
    return ($lp!==false ? $lp : -1);
  }

  /**
   * Get offset for first occurrence $needle in $haystack.
   * @param $needle needle to search for
   * @param $haystack haystack to search in
   * @return last position of $needle in $haystack
   * @see SharedMemorySegment
   */
  public static function get_offset_first($needle = "", $haystack = "") {
    if(strncmp(gettype($needle),"string",6)==0
       && strncmp(gettype($haystack),"string",6)==0)
      $fp = strpos($haystack,$needle);
      
    return ($fp!==false ? $fp : -1);
  }

}

?>
