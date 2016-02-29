<?php

namespace core\object;
use core\object\Comparable as Comparable;

/**
 * This class implements comparison methods for objects
 * @author Marc Bredt
 */
class ComparableObject implements Comparable {

  public function __construct() {}

  private function has_attribute() {}

  /**
   * Compare (specific) attributes of objects.
   * @return true on success, otherwise false
   */
  public function compare($o = null, $attrs = array()) {}
  
  /**
   * Compare serialized versions of objects.
   * @return true on success, otherwise false
   */
  public function scompare($o = null) {}

}
