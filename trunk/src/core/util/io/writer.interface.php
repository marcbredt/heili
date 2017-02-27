<?php

namespace core\util\io;

/**
 * Abstract class to declare some functions for any type of
 * writer and to provide elements which are definetly necessary.
 * @author Marc Bredt
 */
interface Writer {

  /**
   * Every writer must implement a write function.
   */
  public function write();

}

?>
