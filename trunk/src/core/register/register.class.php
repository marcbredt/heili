<?php

namespace core\register;
use \Iterator as Iterator;
use \Countable as Countable;
use core\object\Sortable as Sortable;

/**
 * This class implements a simple (array) register.
 * @author Marc Bredt
 * @see Loggablebject
 */
abstract class Register implements Iterator, Countable, Sortable  {

  /**
   * Stores the current pointer positionition. Needed for Iterator.
   */
  protected $position = 0;

  /**
   * Represents a register - an accessible container/array
   * Unique names will be assigned in the form [id] => $name.
   */
  protected $reg = null;

  /**
   * Create a register. Initialized positionition <code>$position</code> is 0
   * showing at the next element to be used.
   */
  public function __construct() {
    $this->position = 0;
    $this->reg = array();
  }

  /* methods that need to be implemented by the extending class */

  /**
   * Insert an entry into a register.
   * @param $key valid numeric or unique name key
   * @return true if insertion succeeded otherwise false
   */
  abstract public function insert($key = null, $obj = null);

  /**
   * Remove an entry from a register.
   * @param $key valid numeric or unique name key
   * @return true if insertion succeeded otherwise false
   */
  abstract public function remove($key = null);

  /* methods for Sortable */
  public function sort() {
    asort($this->reg);
  }

  /* methods needed for Iterator */

  public function current() {
    return $this->reg[$this->position];
  }

  public function key() {
    return $this->position;
  }

  public function next() {
    ++$this->position;
  }

  public function rewind() {
    $this->position = 0;
  }

  public function valid() {
    return isset($this->reg[$this->position]);
  }
  
  /* methods for Countable */

  public function count() {
    return count($this->reg);
  }
}

?>
