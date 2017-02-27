<?php

namespace core\object;

abstract class Config {

  private $conf = null;

  public abstract function __construct();

  public abstract function load();

}

?>
