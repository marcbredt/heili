<?php

namespace core\util\log;

/**
 * Interface to pin logging functionality onto any objects.
 * @author Marc Bredt
 */ 
interface Loggable {

  /** 
   * Any loggable object needs a log function. 
   */
  public function log();

}

?>
