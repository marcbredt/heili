<?php

namespace core\util;

/**
 * Abstract class to declare some functions for any type of
 * writer and to provide elements which are definetly necessary.
 * @author Marc Bredt
 */
abstract class Writer {

  /**
   * Element to write to. Could be anything like a file, stream or shared
   * memory segment.
   */
  private $element = null;

  /**
   * Every writer must implement a write function.
   */
  public abstract function write();

  /**
   * Function to remove entries.
   */
  //public abstract function remove();

}

?>
