<?php

namespace core\config;
use core\register\Register as Register;
use core\util\param\Validator as Validator;
use core\exception\register\RegisterException as RegisterException;

/**
 * A register implementation to store configuraions loaded.
 * @author Marc Bredt
 * @see Register
 */
class ConfigurationRegister extends Register {

  /**
   * This array maps unique configuration names to <a style="font-weight: bold;"
   * href="classcore_1_1config_1_1Configuration.html">Configuration</a> objects. 
   */
  private $map = array();

  /**
   * Create a map.
   */
  public function __construct() {
    $this->__wakeup();
    parent::__construct();    
  }

  /**
   * Insert an element. Object assignments to theese unique names
   * should be made in a class extending this one as the positional
   * information are needed to implement the 
   * @param $name name for the i-th object
   * @param $obj object which will be stored in <code>$this->map[$key]</code>
   * @return true if insertion was successful otherwise false
   */
  public function insert($key = null, $obj = null) {

    global $filelogger;

    // check if the key is a string
    if (Validator::isa($key,"string") && !Validator::equals($key,"")) {
      
      // check for a valid object passed as this class is a ConfigurationRegister
      if (Validator::isa($obj,"object") 
          && Validator::isclass($obj,"core\config\Configuration")) {

        // check if an register entry exist but override the object anyways
        if(!Validator::in($key,$this->reg)) $this->reg[] = $key;
        $this->map[$key] = $obj;

        return true;

      } else {

        $filelogger->log("%, object=%",
                   array(new RegisterException("invalid object"),$object));
        throw(new RegisterException("invalid object"));
   
      }

    // if the key type does not equal 'string'
    } else {
  
      $filelogger->log("%, key=%",
                 array(new RegisterException("invalid key"),$key));
      throw(new RegisterException("invalid key"));

    }

    return false;
  }

  /**
   * Remove an entry from a register.
   * @param $key valid numeric or unique name key
   * @return true if the removal succeeded otherwise false
   */
  public function remove($key = null) { 

    global $filelogger;

    // if $key is a string it must refer to $this->map
    if (Validator::isa($key,"string") && isset($this->map[$key])) {

        foreach($this->reg as $k => $v) {
          if(Validator::equals($v,$key)) unset($this->reg[$k]);
        }
        unset($this->map[$key]);
        return true;

    // if it is an integer the $key must refer to $this->reg
    } else if(Validator::isa($key,"integer") && isset($this->reg[$key])) {

        unset($this->map[$this->reg[$key]]);
        unset($this->reg[$key]);
        return true;

    // otherwise throw a key exception
    } else {
   
      $filelogger->log("%, key=%",
                 array(new RegisterException("invalid key"),$key));
      throw(new RegisterException("invalid key"));

    } 

    return false;
  }

  /**
   * Access the map.
   * @param $key key for the map
   * @return the object assigned to the map if $key is valid and available
   *         otherwise null
   */
  public function get($key = "") {

    global $filelogger;
    
    // if the $key is a string it refers to $this->map
    if(Validator::isa($key,"string") && Validator::equals($key,"")
       && isset($this->map[$key])) {
         return $this->map[$key];
    
    // if $key is an integer it refers to $this->reg 
    } else if(Validator::isa($key,"integer")) {
      return $this->map[$this->reg[$key]];

    // throw a key exception
    } else {

      $filelogger->log("%, key=%",
                 array(new RegisterException("invalid key"),$key));
      throw(new RegisterException("invalid key"));

    }

  }

  /* 
   * Methods for serialization
   * __sleep/__wakeup to exclude e.g. logger from being serialized 
   * and to reintialize ressources/objects that hold resources
   */

  /**
   * This function overrides the serialization method as there are other 
   * attributes ($reg,$position) are needed to be stored along the inheritage 
   * chain.
   * @return data that can be serialized
   */
  public function __sleep() {
    return array('position','reg','map');
  }
 
}

?>
