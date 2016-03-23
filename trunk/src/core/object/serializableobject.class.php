<?php

namespace core\object;
use \Serializable as Serializable;
use core\util\log\FileLogger as FileLogger;

/**
 * This class provides general serialization methods.
 * It is used to store objects, e.g. ConfigurationManager, during 
 * sessions to save resources.
 * @author Marc Bredt
 * @see ConfigurationManager
 * @see Serializable
 * @req PHP >= 5.1.0, interface Serializable
 */
class SerializableObject implements Serializable {

  /**
   * Serialize the object passed during initialization.
   * @return serialized object
   */
  public function serialize() { 
    return serialize($this); 
  }

  /**
   * Deserializes an serialized object (representation) passed 
   * and stores it as <code>$dobj</code>
   * @param $sobj serialized object representation
   */
  public function unserialize($sobj = "") { 
    // TODO: check serialization layout, sth like 'type:length:"payload"'
    return strncmp(gettype($sobj),"string",6)==0 ? unserialize($sobj) : ""; 
  }

  /**
   * Magic function called prior serialization. Primarly used to detach
   * any attributes that are not serializable like file handles or any 
   * other resources.
   * @return array with attributes of the calling or extending class
   * @see ReflectionClass
   * @see ReflectionProperty 
   * @req PHP >= 5, ReflectionClass, ReflectionProperty
   */
  public function __sleep() {

    $ret = array();

    // reflection stuff
    $rclass = new ReflectionClass($this); 
    $props = $rclass->getProperties(ReflectionProperty::IS_PUBLIC
                                    |ReflectionProperty::IS_PROTECTED);

    // return only caller attributes for $this, everything else can 
    // be restored upon creation when extending LoggableObject
    foreach($props as $p) {
      if(strncmp(get_class($this),$p->class,strlen(get_class($this)))==0) {
        $ret[] = $p->getName(); 
      }
    }

    return $ret;
  }
  
  /**
   * Magic function called prior unserialization. Primarly used to bind new
   * file or resource handles which were removed during serialization. E.g.
   * file handles for logging functionality need to be restored.
   */ 
  public function __wakeup() {
    
    // restore file handles for objects which extend logging functionality
    if(strncmp(get_parent_class($this),"core\object\LoggableObject",26)==0) {
      $this->logger = new FileLogger(get_class($this));
    }

  }

}

?>
