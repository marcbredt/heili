<?php
 
namespace core;

/**
 * Interface used to define sorting functions for arrays in a
 * e.g. Register.
 * @author Marc Bredt
 */
interface Sortable {

  /**
   * Require the implementation to have this sorting function
   * defined.
   * @return sorted array or object as required by the implementation
   */
  public function sort();

}

?>
