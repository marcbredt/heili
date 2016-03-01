<?php

namespace core\object;
use core\object\SerializableObject as SerializableObject;
use core\util\log\Loggable as Loggable;
use core\util\log\FileLogger as FileLogger;

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
class LoggableObject extends SerializableObject implements Loggable {

  protected $logger = null;

  /**
   * This magic function gets called upon calling unavailable function.
   * As PHP does not know about multiple inheritation this method is
   * used to invoke any logging funtionality.
   * @see FileLogger::logge()
   */
  public function log($msgstr = "", $msgargs = array(), $type = "INFO") {
    
    // setup the logger if that wasn't done before
    if($this->logger===null)
      $this->logger = new FileLogger(get_class($this));
   
    // log it
    $this->logger->logge($msgstr,$msgargs,$type);

  }
  
}

?>
