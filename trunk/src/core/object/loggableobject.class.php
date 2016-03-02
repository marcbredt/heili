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
   * This function just sets up a logger if needed.
   */
  private function init() {
    // NOTE: Using a contructor or the magic __construct method is not useful 
    //       for the upcoming operations as this function must be invoked 
    //       directly from the extending class via parent::__construct()

    // setup the logger if that wasn't done before
    if($this->logger===null)
      $this->logger = new FileLogger(get_class($this));
  }
  
  /**
   * This magic function gets called upon calling unavailable functions.
   * As PHP does not know about multiple inheritation this method is
   * used to invoke any logging funtionality by wrapping public functions
   * of the FileLogger class.
   * @param $fn function name of the unknown function
   * @param $fa arguments passed on to $fn
   * @return mixed values (e.g. void, string, ...) 
   * @see FileLogger
   */
  public function log($msgstr = "", $msgargs = array(), $msgtype = "INFO") {
    // init
    $this->init(); 
    // log it 
    $this->logger->log($msgstr, $msgargs, $msgtype);
  }
 
  /**
   * Wraps FileLogger::getFile().
   * @return location to the log file FileLogger currently uses.
   */ 
  public function getFile() {
    // init
    $this->init(); 
    // get the log file location
    return $this->logger->getFile();
  }

}

?>
