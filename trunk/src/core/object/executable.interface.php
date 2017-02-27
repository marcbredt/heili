<?php

namespace core\object;

interface Executable {

  /**
   * Function to execute anything.
   */
  public function execute();
 
  /**
   * Execute multiple stuff e.g. prepared sql statements.
   * Not every executer needs a real implementation for this method.
   */
  public function batch();

}

?>
