<?php

namespace core\util\map;
use \Iterator as Iterator;
use \Countable as Countable;
use core\util\param\Validator as Validator;
use core\util\map\Mappable as Mappable;

/**
 * This class is used to store any kind of objects and provide access to those.
 * @author Marc Bredt
 */
class Map implements Mappable,Iterator,Countable {

  /**
   * Positional iterator for the implementation of Iterator methods.
   */
  private $pos = 0;

  /**
   * Array storig all elements passed via Map::add().
   */
  protected $map = array();

  /**
   * Type for values gong to be stored. Defaults to "null" but could and should
   * be adjusted whenever an object inherits from this class.
   */
  protected $type = null;

  /**
   * Add a key and the corresponding value to the map.
   * @param $key string identifing the map element, should be unique
   * @param $value value that should be stored, need to be of the type defined
   * @return true if the $value was inserted at $key, otherwise false
   */ 
  public function add($key = null, $value = null) {
    global $filelogger;
    $filelogger->log("k=%, v=%, t=%, is?=%, ie?=%, hk?=%, it?=%",
                     array(
                       $key, $value, $this->type,
                       Validator::isa($key,"string"),
                       Validator::isempty($key),
                       $this->has($key),
                       Validator::isa($value,$this->type)
                     ),"DEBUG");
    if(Validator::isa($key,"string") && !Validator::isempty($key)
       && !$this->has($key) && Validator::isa($value,$this->type)){
      $this->map[$key] = $value;
      return true;
    }

    $filelogger->log("invalid map type (k=%,v=%) for %", 
                     array($key,$value,$this), "WARNING");
    return false;
  }
 
  /**
   * Remove a $key from the map.
   * @param $key string identifing the map element, should be unique
   * @return true if there is an element gonna be removed and the removal was 
   *              successful, otherwise fals
   */
  public function remove($key = null) {

    global $filelogger;
    $filelogger->log("key=%, str?=%, empty?=%, haskey?=%",
                     array($key, Validator::isa($key,"string"),Validator::isempty($key),
                           $this->has($key)),"DEBUG");

    if(Validator::isa($key,"string") && !Validator::isempty($key)
       && $this->has($key)) {
      unset($this->map[$key]);
      return true;

    } else if(!$this->has($key)) {
      return true;

    }

    return false;
  }

  /**
   * Check if a $key is available in the map.
   * @param $key string identifing the map element, should be unique
   * @return true if $key exists, otherwise false
   */
  public function has($key = null) {
    return (Validator::isa($key,"string") && !Validator::isempty($key)
            && array_key_exists($key,$this->map));
  }

  /**
   * Get a map element.
   * @param $key string identifing the map element, should be unique
   * @return the corresponding element, otherwise null
   */
  public function get($key = null) {
    return ($this->has($key) ? $this->map[$key] : null);
  }
 
  /**
   * Set a map element.
   * @param $key string identifing the map element, should be unique
   * @return true if setting succeeded, otherwise false
   */
  public function set($key = null, $value = null) {
    return ($this->remove($key) && $this->add($key,$value));
  }
 
  /**
   * Get the current map.
   * @return array containing all elements currently stored
   */
  public function get_map() {
    return $this->map;
  }

  public function set_type($type = "") {
    $this->type = $type;
  }

  /**
   * Print information on this map.
   * @return string representing this object
   */
  public function __toString() {
    $s = get_class($this)."-".spl_object_hash($this)."= [ ";
    foreach($this->map as $k => $v) $s .= $k."='".$v."', ";
    $s .= rtrim($s,", ")." ]"; // remove last ", "
    return $s;
  }

  /* inherited methods from Iterator interface */

  public function current() { 
    return $this->map[$this->key()]; 
  }

  public function key() { 
    if(count($this->map)>0 && $this->pos<count($this->map))
      return array_keys($this->map)[$this->pos];
    else return 0;
  }

  public function next() { 
    $this->pos++; 
  }

  public function rewind() { 
    $this->pos = 0; 
  }

  public function valid() { 
    return isset($this->map[$this->key()]); 
  }

  /* inherited method from Contable interface */

  public function count() {
    return count($this->map);
  }

}
