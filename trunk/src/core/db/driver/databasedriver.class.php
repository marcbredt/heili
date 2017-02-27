<?php

namespace core\db\driver;
use core\db\connect\Connector as Connector;
use core\db\driver\Drivable as Drivable;
use core\object\Executable as Executable;
use core\db\config\DatabaseConfig as DatabaseConfig;
use core\db\statement\Statement as Statement;
use core\db\statement\StatementBatch as StatementBatch;

abstract class DatabaseDriver extends Connector
                              implements Drivable,Executable {

  private $type = null;

  public function __construct(DatabaseConfig $dbc = null) {
    parent::__construct($dbc);
    $this->type = $dbc->get_driver();
  }
 
  /* inherited from Drivable*/
  public abstract function prepare($sql = null, $opt = null);
  public abstract function set($attr = null, $value = null);
  public abstract function get($attr = null);
  public abstract function begin();
  public abstract function commit();
  public abstract function in();
  public abstract function rollback();
  public abstract function query($sql = null);
  public abstract function fetch();
  public abstract function fetchall();
  public abstract function info($type = "");
  public abstract function meta($type = "", $target = "");
  public abstract function stat();
  public abstract function error();

  /* inherited from Executable */
  public abstract function execute(Statement $stmt = null);
  public abstract function batch(StatementBatch $stmtb = null);

}

?>
