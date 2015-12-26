<?php

namespace core\object;

/**
 * This interface is used to pin serilization methods onto object.
 * They are called during serialization (__sleep) and deserializaion
 * (__wakeup). Theese methods can be used to clean objects, save 
 * values where extending SerializableObject is not the best practice.
 * In most cases this interface will be used to close file handle or
 * restrict data being serialized as resources cannot be serialized.
 * On the otherhand the __wakeup function can be used to setup things
 * (again) e.g. FileLogger.
 * @author Marc Bredt  
 */
interface SerializationMethods {

  /**
   * This function is invoked by serialize() and used to restrict
   * access on class data or for cleanage. Data which is not
   * serilizable e.g. DOMDocument should be excluded from serialization.
   * @return array of data being serialized.
   */
  public function __sleep();
  
  /**
   * This function is invoked during deserialization. It can be used   
   * to setup full object functionality e.g. class logging using
   * FileLogger or initializing objects that cannot be serialized
   * like DOMDocument.
   */
  public function __wakeup();

}

?>
