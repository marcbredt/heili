<?php

namespace core\object;
use \core\object\SerializableObject as SerializableObject;

/**
 * This class is used to provide logging functionality for each object
 * without pinning extra logger attributes onto an object. Additionally
 * this class avoids errors upon serialization as file handles or other
 * resources of that kind are not serializeable. To enable logging
 * for objects which will probably be serialized this class implements 
 * the magic functions __sleep() and __wakeup() with respect to the 
 * caller.
 * @author Marc Bredt
 * @see __sleep()
 * @see __wakeup()
 */
class LoggableObject extends SerializableObject {

  protected $logger = null;

}

?>
