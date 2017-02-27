<?php

namespace core\db\driver;
use \PDO as PDO;
use core\db\driver\DatabaseDriver as DatabaseDriver;
use core\db\config\DatabaseConfig as DatabaseConfig;
use core\util\map\IntegerMap as IntegerMap;

/**
 * This class provides implementations for methods which are realizable via
 * PDO functions. This driver class describes the second abstraction level
 * for database drivers using the underlying PDO driver. Therefor the driver
 * extending this class only need to implement the (abstract) functions
 * inherited from the interface core\object\Executable and 
 * core\db\driver\DatabaseDriver respectively as all other precedures can be
 * predefined using the PDO methods as there usage is independent from the 
 * execution itself.
 * @author Marc Bredt
 * @see core\object\Executable
 * @see core\db\driver\DatabaseDriver
 */
abstract class PDODriver extends DatabaseDriver {

  /** 
   * (Prepared) Statement to run it multiple times without prepaing it again.
   */
  private $statement = null;

  /**
   * Stores PDO options which can be derived from the DatabaseConfig.
   */
  private $pdooptions = null;

  /**
   * Setup the PDODriver. Especially PDO attributes.
   * TODO: check options that are driver (PDO) specific.
   * TODO: set driver specific options, e.g. persistence
   */
  public function __construct(DatabaseConfig $dbc = null) {
    parent::__construct($dbc);
    //$this->pdooptions = new IntegerMap();
    //$this->pdooptions->set(PDO::ATTR_CURSOR,PDO::CURSOR_FWDONLY);
  }

  public function prepare($sql = null, $opt = null) {
    return $this->get_connection()->prepare($sql,$opt);
  }

  public function set($attr = null, $value = null) {
    return $this->get_connection()->setAttribute($attr,$value); 
  }

  public function get($attr = null) {
    return $this->get_connection()->getAttribute($attr); 
  }

  public function begin() {
    return $this->get_connection()->beginTransaction(); 
  }

  public function in() {
    return $this->get_connection()->inTransaction(); 
  }

  public function commit() {
    return $this->get_connection()->commit(); 
  }

  public function rollback() {
    return $this->get_connection()->rollBack(); 
  }

  public function query($sql = null) {
    return $this->get_connection()->query($sql); 
  }

  public function fetch() {
    return $this->statement->fetch(); 
  }

  public function fetchall() {
    return $this->statement->fetchall(); 
  }

  public function info($type = "") {
    switch($type) {
      case "drivers": break;
      case "lid": break;
      default: break;
    }
    return "";
  }

  public function meta($type = "column", $target = null) {
    switch($type) {
      case "column": break;
      default: break;
    }
    return "";
  }

  public function stat() {
    return "";
  }

  public function error() {
    return "";
  }

  

}

?>
