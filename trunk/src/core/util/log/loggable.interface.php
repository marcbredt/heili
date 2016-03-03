<?php

namespace core\util\log;

/**
 * As PHP does not allow to inherit from multiple classes this class is used to
 * declare a function which can be used to provide logging functionality for 
 * any object through the definition of the following function which should 
 * forward its parameters to FileLogger.
 * @author Marc Bredt 
 * @see FileLogger
 * @see LoggableObject
 */
interface Loggable {

  /**
   * Wraps FileLogger::getLogger().
   */
  public function getLogger();

  /**
   * Wraps FileLogger::getFile().
   */
  public function getFile();

  /**
   * Wraps FileLogger::clean(). 
   */
  public function clean();

  /**
   * Wraps FileLogger::getFirstLine(). 
   */
  public function getFirstLine();

  /**
   * Wraps FileLogger::getLastLine(). 
   */
  public function getLastLine();

  /**
   * Wraps FileLogger::__toString(). 
   */
  public function __toString();

  /**
   * Wraps FileLogger::flatten().
   */
  public function flatten();

  /**
   * Wraps FileLogger::logge().
   */
  public function log();

}
