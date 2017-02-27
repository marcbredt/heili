<?php

namespace core\util\param;
use core\util\string\StringUtil as StringUtil;
use core\util\param\Filter as Filter;
use core\util\param\Sanitizor as Sanitizor;

/**
 * This class provides static methods to verify parameters passed onto any
 * object or method. It additionally wraps php filter functions when using
 * function self::filter().
 * @author Marc Bredt
 */
class Validator {

  /**
   * This function is used to check the type of any object.
   * @param $object object which' type is going to be checked
   * @param $type type $object should be of
   * @return true if $object matches the $type spec, otherwise false
   */
  public static function isa($object = null, $type = "") {
    return (
            (strncmp(strtolower(gettype($object)),
               strtolower($type),strlen($type))==0)
             || 
            (strncmp(gettype($object),"object",6)==0 
               && $object instanceof $type)
           );
  }

  /**
   * This function is used to check any parameter against a regular expression.
   * @param $param parameter, object or whatever which should match $regex
   * @param $regex regular expression that $param should match, $regex is 
   *               passed onto preg_match so provide a valid one
   * @return true if $param matches $regex, other false
   */
  public static function matches($param = null, $regex = "//") {
    return ((self::isa($regex,"string") 
             && StringUtil::has_layout("\/.*\/",$regex)) ? 
               preg_match($regex, print_r($param,true))===1 : false);
  }

  /**
   * This function is used to check if any kind of list datatype contains
   * a specified value.
   * @param $element element which is searched in the $container provided
   * @param $container container providing (valid) elements
   * @return true if $element is in $container, otherwise false
   */
  public static function in($element = null, $container = array()) {
    return (Validator::isa($container,"array") 
            ? in_array($element, $container, true) : false);
  }

  /**
   * This function is used to check if a parameter is empty.
   * @return true if $element is empty, otherwise false
   */
  public static function isempty($element = null) {
    return empty($element);
  }

  /**
   * Check the class for any object.
   * @param $object object that should be an instance of
   * @return true if $object is an instance of $class 
   */
  public static function isclass($object = null, $class = "") {
    if(self::isa($object,"object"))
      return ($object instanceof $class);
    else 
      return false;
  }
 
  /**
   * Check the parent class for any object.
   * @param $object object that should be an instance of
   * @return true if $object is an instance of $class 
   */
  public static function ispclass($object = null, $class = "") {
    if(self::isa($object,"object") && self::isa($class,"string"))
      return self::equals(get_parent_class($object),$class);
    else 
      return false;
  }

  /**
   * Check if two parameter equal.
   * @param $one element one
   * @param $two element two
   * @return true if the elements equal
   */
  public static function equals($one = null, $two = null) {
    return ($one === $two); 
  }

  /** 
   * Checks wether the element passed is an object being an exception or not.
   * Every exception object needs to have its class to be an Exception or must
   * at least extend the Exception class for now.
   * @param $e exception passed
   * @return true if the element passed is an object and an instance of 
   *         Exception or its parent class is an Exception, otherwise false
   */
  public static function isexception($e = null) {
    return (self::isa($e,"object") && ( Validator::isclass($e,"Exception")
              || Validator::ispclass($e,"Exception")));
  }

  /**
   * Get an ojects type.
   * @param $object any object going to determine the type for
   * @return the type of th $object passed
   */
  public static function get_type($object = null) {
    return (Validator::isa($object,"object")?get_class($object):gettype($object));
  }

}

?>
