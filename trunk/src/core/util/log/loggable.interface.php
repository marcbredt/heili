<?php

namespace core\util\log;

/**
 * As PHP does not allow to inherit from multiple classes this class is used to
 * declare a function which can be used to provide logging functionality for 
 * any object through the definition of the following function which should 
 * forward its parameters to FileLogger.
 * @author Marc Bredt 
 * @see FileLogger
 */
interface Loggable {

  /**
   * Declaration of the main log function for loggable objects.
   */
  public function log();

  /**
   * Declaration to wrap FileLogger::getFile() in e.g. LoggableObject
   * @see LoggableObject
   * @see FileLogger
   */
  public function getFile();
 
}
