<?php

namespace module\db\driver;
use core\db\driver\DatabaseDriver as DatabaseDriver;

class MySQLiDriver extends DatabaseDriver {

  public function prepare($sql = null, $opt = null){}
  public function set($attr = null, $value = null){}
  public function get($attr = null){}
  public function begin(){}
  public function commit(){}
  public function in(){}
  public function rollback(){}
  public function query($sql = null){}
  public function fetch(){}
  public function fetchall(){}
  public function info($type = ""){}
  public function meta($type = "", $target = ""){}
  public function stat(){}
  public function error(){}
  public function execute(Statement $stmt = null){}
  public function batch(StatementBatch $stmtb = null){}

}

?>
