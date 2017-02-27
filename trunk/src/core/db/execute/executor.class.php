<?php

namespace core\db\execute;
use core\util\param\Validator as Validator; 
use core\util\string\StringUtil as StringUtil;
use core\db\driver\DatabaseDriver as DatabaseDriver;
use core\db\statement\Statement as Statement;
use core\exception\db\DatabaseException as DatabaseException;

/**
 * This class executes statements using the database driver set.
 * @author Marc Bredt
 */
class Executor {

  /**
   * Driver used to execute statements.
   */
  private $driver = null;

  /**
   * Create an Executor.
   * @param $driver DatabaseDriver to use executing statements
   * @see Connection
   */
  public function __construct(DatabaseDriver $driver = null) {

    global $filelogger;
 
    if(!Validator::isclass($driver,"core\db\driver\DatabaseDriver")) {
      $filelogger->error("%, driver=%", 
        array(new DatabaseException("invalid driver",5),$driver));
      throw(new DatabaseException("ivalid driver",5));

    } else {
      $this->driver = $driver;

    }

  }
 
  /**
   * Execute a statement.
   * @param $sql sql (prepared) statement
   * @param $params parameters for the (prepared) $sql statement
   * @return an array containing the fetched results
   * @throws DatabaseException
   */
  public function execute(Statement $stmt = null) {
    return $this->driver->execute($stmt);
  }

  /**
   * Run a set of sql statements.
   * @param $stmtb statement batch
   * @param $transact run all statements as a transaction, defaults to true
   * @return array containing all results
   */
  public function batch($stmtb = null, $transact = true) {
    return $this->driver->batch($stmtb,$transact);
  }

  /**
   * Dump this Executor.
   * @return string representing this executor.
   */
  public function __toString() {
    return get_class($this).spl_object_hash($this)."=(".
             "driver=".$this->driver.
           ")";
  }

}

?>
