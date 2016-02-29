<?php

namespace core\object;

/**
 * This interface declares functions for comparing
 * any type of objects
 */
interface Comparable {

  /**
   * Compares the object implementing this function with
   * the one passed as parameter.
   * @param $o object to compare this instance with
   * @return true if the objects equal, otherwise false
   */
  public function compare($o = null);

}
