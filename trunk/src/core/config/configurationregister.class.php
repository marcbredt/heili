<?php

namespace core\config;
use \core\register\Register as Register;
use \core\object\SerializationMethods as SerializationMethods;

/**
 * A register implementation to store configuraions loaded.
 * @author Marc Bredt
 * @see Register
 */
class ConfigurationRegister extends Register implements SerializationMethods {

  /**
   * A logger for this class.
   */
  private $logger = null;

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

    // check if the key is a string
    if (strncmp(gettype($key),"string",6)==0 && strncmp($key,"",1)!=0) {
      
      // check for a valid object passed as this class is a ConfigurationRegister
      if (strncmp(gettype($obj),"object",6)==0 
          && strncmp(get_class($obj),"core\config\Configuration",13)==0) {

        // check if an register entry exist but override the object anyways
        if(!in_array($key,$this->reg)) $this->reg[] = $key;
        $this->map[$key] = $obj;

        return true;

      } else {
        $this->logger->logge("%",array(
          new \core\exception\register\InvalidRegisterObjectException(
            "Object is invalid. Expected 'Configuration' got '".
              (strncmp(gettype($obj),"object",6)==0 ? 
                 substr(get_class($obj),strrpos(get_class($obj),"\\")+
                   (preg_match("/\\\\/",get_class($obj)) ? 1 : 0)) : 
                     gettype($obj))."'.")));
        throw(new \core\exception\register\InvalidRegisterObjectException(
                "Object is invalid. Expected 'Configuration' got '".
                  (strncmp(gettype($obj),"object",6)==0 ? 
                    substr(get_class($obj),strrpos(get_class($obj),"\\")+
                      (preg_match("/\\\\/",get_class($obj)) ? 1 : 0)) : 
                        gettype($obj))."'."));
      }

    // if the key type does not equal 'string'
    } else {
      $this->logger->logge("%",array(
        new \core\exception\register\InvalidRegisterKeyException(
          "Key is invalid. Expected 'string' got '".gettype($key)."' ".
            "for ".var_export($key,true).".")));
      throw(new \core\exception\register\InvalidRegisterKeyException(
             "Key is invalid. Expected 'string' got '".gettype($key)."' ".
               "for ".var_export($key,true)."."));
    }

    return false;
  }

  /**
   * Remove an entry from a register.
   * @param $key valid numeric or unique name key
   * @return true if the removal succeeded otherwise false
   */
  public function remove($key = null) { 

    // if $key is a string it must refer to $this->map
    if (strncmp(gettype($key),"string",6)==0 && isset($this->map[$key])) {

        foreach($this->reg as $k => $v) {
          if(strcmp($v,$key)==0) unset($this->reg[$k]);
        }
        unset($this->map[$key]);
        return true;

    // if it is an integer the $key must refer to $this->reg
    } else if(strncmp(gettype($key),"integer",7)==0 && isset($this->reg[$key])) {

        unset($this->map[$this->reg[$key]]);
        unset($this->reg[$key]);
        return true;

    // otherwise throw a key exception
    } else {
      $this->logger->logge("%",array(
        new \core\exception\register\InvalidRegisterKeyException(
          "Key is invalid. Expected 'string' or 'integer' ".
            "got '".gettype($key)."' for ".var_export($key,true).".")));
      throw(new \core\exception\register\InvalidRegisterKeyException(
              "Key is invalid. Expected 'string' or 'integer' ".
                "got '".gettype($key)."' for ".var_export($key,true)."."));
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
    
    // if the $key is a string it refers to $this->map
    if(strncmp(gettype($key),"string",6)==0 && strncmp($key,"",1)==0
       && isset($this->map[$key])) {
         return $this->map[$key];
    
    // if $key is an integer it refers to $this->reg 
    } else if(strncmp(gettype($key),"integer",7)==0 ) {
      return $this->map[$this->reg[$key]];

    // throw a key exception
    } else {
      $this->logger->logge("%",array(
        new \core\exception\register\InvalidRegisterKeyException(
          "Key is invalid. Expected 'string' or 'integer' ".
            "got '".gettype($key)."' for ".var_export($key,true).".")));
      throw(new \core\exception\register\InvalidRegisterKeyException(
              "Key is invalid. Expected 'string' or 'integer' ".
                "got '".gettype($key)."' for ".var_export($key,true)."."));
    }

  }

  /* 
   * methods for SerializationMethods
   * __sleep/__wakeup to exclude e.g. logger from being serialized 
   * and to reintialize ressources/objects that hold resources
   */

  /**
   * Interfere during the serialization of this object and avoid
   * pinning resources e.g. open file handles onto serialized data
   * @return data that can be serialized
   */
  public function __sleep() {
    return array('position','reg','map');
  }
 
  /**
   * Interfere during deserialization and reinitiate data that can
   * contain resources which cannot be serialized liked DOMDocument
   * or file handles.
   */
  public function __wakeup() {
    $this->logger = new \core\util\log\FileLogger("ConfigurationRegister");
  }

}

?>
