<?php

namespace core\util\log;

/**
 * As PHP does not allow to inherit from multiple classes this class is used to
 * declare a function which can be used to provide logging functionality for 
 * any object through the definition of the following function which should 
 * forward its parameters to FileLogger::logge().
 * @author Marc Bredt 
 * @see FileLogger::logge()
 */
interface Loggable {

  /**
   * Declaration of the log function for loggable objects.
   */
  public function log();
 
}
