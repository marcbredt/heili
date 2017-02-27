<?php

namespace core\object;

interface Buildable {

  /**
   * Build and return some code.
   */
  public function build($data = array());

}


?>
