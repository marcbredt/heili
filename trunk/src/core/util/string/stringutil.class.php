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

  /**
   * This function is used to get an object's string representation using
   * var_export. This function will often be used in combination with
   * exceptions when dumping or logging a parameter passed.
   * @param $o object or any other type
   * @return string representation for $o
   */
  public static function get_object_string($o = null) {
    return preg_replace("/[\t ]+/"," ", preg_replace("/[\r\n]/"," ",
             print_r($o,true)));
  }

  /**
   * This function is used to get an object's value. This function is often
   * used in conjunction with exception, especially ParamNotValidException
   * when one wants to determine or log an objects class or its type.
   * @param $o object or variable the type should be determined for
   * @return the object's type determined
   */
  public static function get_object_value($o = null) {
    return (strncmp(gettype($sem),"object",6)==0 ? get_class($sem) : 
            gettype($sem));
  }

}

?>
