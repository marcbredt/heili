<?php

namespace core\object;

interface Validatable {

  /**
   * Function used to validate anything.
   * @return true if validation was successful, otherwise false
   */
  public function validate();

}

?>
