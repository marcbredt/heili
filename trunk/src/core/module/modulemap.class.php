<?php

namespace core\module;
use \Countable as Countable;
use core\util\map\Map as Map;
use core\util\string\StringUtil as StringUtil;

/**
 * A module map.
 * @author Marc Bredt
 * @req PHP >= 5.1.0, interface Countable
 */
class ModuleMap extends Map implements Countable {

  public function __constuct() {
    $this->type= "core\module\Module";
  }

  public function count() {
    return count($this->map);
  }

  public function __toString() {
    return get_class($this).spl_object_hash($this)." [".
           StringUtil::get_object_string($this->get_map())." ]";
  }
  
}

?>
