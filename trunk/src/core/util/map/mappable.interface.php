<?php

namespace core\util\map;

/**
 * This interface defines function for a map.
 * @author Marc Bredt
 */
interface Mappable {

  public function add($key = null, $value = null);

  public function remove($key = null);

  public function has($key = null);

  public function get($key = null);

}

?>
