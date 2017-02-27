<?php

namespace core\object\handler;

/**
 * Interface to setup handlers. Especially for action handlers of modules.
 * @author Marc Bredt
 */
interface Handable {

  /**
   * Main handling function every handler should implement as this function
   * will be called when invoking any external handler.
   */
  public function handle();

}

?>
