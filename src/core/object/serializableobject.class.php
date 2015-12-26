<?php

namespace core\object;

/**
 * This class provides general serialization methods.
 * It is used to store objects, e.g. ConfigurationManager, during 
 * sessions to save resources and
 * @author Marc Bredt
 * @see ConfigurationManager
 */
class SerializableObject implements Serializable {

  /**
   * Object that is going to be serialized.
   * NOTE: Just user defined classes/objects can be serialized.
   */
  private $dobj = null;

  /**
   * Serialized object representation.
   */
  private $sobj = null;

  /**
   * Set the object to be serialized.
   * @param $obj object going to be serialized
   */
  public function __construct($obj) { 
    $this->$dobj = $obj; 
  }

  /**
   * Provides access to the deserialized/original object.
   * @return object which is going to be serialzed
   */
  public function getd() { 
    return $this->dobj; 
  }

  /**
   * Provides access to the serialized object (representation).
   * @return serialization representation of <code>$dobj</code>
   */
  public function gets() { 
    return $this->sobj; 
  }

  /* methods inherited from Serializable */

  /**
   * Serialize the object passed during initialization.
   * @return serialized object
   */
  public function serialize() { 
    $this->sobj = serialize($this->dobj); 
    return $this->sobj; 
  }

  /**
   * Deserializes an serialized object (representation) passed 
   * and stores it as <code>$dobj</code>
   * @param $sobj serialized object representation
   */
  public function unserialize($sobj = null) { 
    $this->dobj = unserialize($sobj); 
  }

}

?>
