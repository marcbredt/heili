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
 * for objects which will probably be serialized this class probides 
 * the magic functions __sleep() and __wakeup() with respect to the 
 * caller.
 * @author Marc Bredt
 * @see __sleep()
 * @see __wakeup()
 */
class LoggableObject extends SerializableObject implements Loggable {

  /**
   * The FileLogger for any object that needs logging functionality.
   */
  protected $logger = null;

  /**
   * This function just sets up a logger if needed.
   */
  private function init() {
    // NOTE: Using a contructor or the magic __construct method is not useful 
    //       for the upcoming operations as this function must be invoked 
    //       directly from the extending class via parent::__construct()

    // setup the logger if that wasn't done before
    if($this->logger==null) {
      $this->logger = new FileLogger(get_class($this));
    }
  }
  
  /**
   * Wraps FileLogger::getLogger().
   * @return object Log 
   */ 
  public function getLogger() {
    // init
    $this->init(); 
    // get the logger
    return $this->logger->getLogger();
  }

  /**
   * Wraps FileLogger::getFile().
   * @return string representing the location to the log file the FileLogger 
   *         currently uses.
   */ 
  public function getFile() {
    // init
    $this->init(); 
    // get the log file location
    return $this->logger->getFile();
  }

  /**
   * Wraps FileLogger::clean().
   */ 
  public function clean() {
    // init
    $this->init(); 
    // clean the log file location
    return $this->logger->clean();
  }

  /**
   * Wraps FileLogger::getFirstine().
   * @return string representing the first line of the current log file 
   */ 
  public function getFirstLine() {
    // init
    $this->init(); 
    // get the logger
    return $this->logger->getFirstLine();
  }

  /**
   * Wraps FileLogger::getLastLine().
   * @return string representing the last line of the current log file 
   */ 
  public function getLastLine() {
    // init
    $this->init(); 
    // get the logger
    return $this->logger->getLastLine();
  }

  /**
   * Wraps FileLogger::__toString().
   * @return string representing the FileLogger object itself 
   */ 
  public function __toString() {
    // init
    $this->init(); 
    // get the string representation of the FileLogger
    return $this->logger->__toString();
  }

  /**
   * Wraps FileLogger::flatten().
   * @return string representing the FileLogger object itself 
   */ 
  public function flatten($o = null, $debug = false) {
    // init
    $this->init(); 
    // get a string representation for the object passed
    return $this->logger->flatten($o, $debug);
  }

  /**
   * This function provides access to the main logging function of FileLogger.
   * As PHP does not know about multiple inherits this method is
   * used to invoke any logging funtionality by wrapping public functions
   * of the FileLogger class.
   * @param $fn function name of the unknown function
   * @param $fa arguments passed on to $fn
   * @see FileLogger
   */
  public function log($msgstr = "", $msgargs = array(), $msgtype = "INFO") {
    // init
    $this->init(); 
    // log it 
    $this->logger->logge($msgstr, $msgargs, $msgtype);
  }

}

?>
