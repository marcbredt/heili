<?php

namespace core\config;
use core\register\ManagerOperation as ManagerOperation;

/**
 * This class is used to handle all configurations regarded
 * during a user's session.
 * @author Marc Bredt
 */
class ConfigurationManager implements ManagerOperation {

  /**
   * ConfigurationRegister containing any Configuration.
   */
  private $cr = null;

  public function load($name = null, $type = null) {}

  public function unload($name = null, $type = null) {}

  public function info($name= null, $gets = false) {}

  public function register($name = null, $conf = null) {}

  public function unregister($name = null, $conf = null) {}

}

?>
