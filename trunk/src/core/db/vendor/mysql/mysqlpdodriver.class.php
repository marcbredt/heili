<?php

namespace core\db\vendor\mysql;
use core\util\param\Validator as Validator;
use core\db\driver\PDODriver as PDODriver;
use core\db\config\DatabaseConfig as DatabaseConfig;
use core\db\statement\Statement as Statement;
use core\db\statement\StatementBatch as StatementBatch;
use core\exception\db\DatabaseException as DatabaseException;

/**
 * A MySQL PDO driver.
 * @author Marc Bredt
 */
class MySQLPDODriver extends PDODriver {

  private $dbc = null;

  /**
   * Stores mysql pdo options which can be derived from the DatabaseConfig.
   */
  private $mysqlpdooptions = null;
 
  /**
   * Setup the MySQLPDODriver. 
   * TODO: check options that are driver (MySQL and PDO) specific.
   * TODO: set driver specific options, e.g. persistence
   */
  public function __construct(DatabaseConfig $dbc = null) {
    parent::__construct($dbc);
    $this->dbc = $dbc;
    // set mysql and pdo specific attributes here 
    // or use PDODriver::set()
  }

  /**
   * Execute a statement.
   * @param $stmt Statement
   * @see core\db\statement\Statement
   */
  public function execute(Statement $stmt = null) {
    
    global $filelogger;

    $filelogger->log("stmt=%", array($stmt));

    if(!Validator::isclass($stmt,"core\db\statement\Statement")) {
      $filelogger->log("batch=%, exception=%", 
                 array($stmtb, new DatabaseException("not a Statement",3)));
      throw(new DatabaseException("not a Statement",3));
    }   

    $sql = $stmt->get_statement();
    $params = $stmt->get_params();

    // setup prepared statement and execute it
    if(Validator::isa($sql,"string") && Validator::isa($params,"array") 
       && count($params)>0) {
    
      $this->statement = $this->prepare($sql,$this->opts->map);
      $psexe = $this->statement->execute($params);

      if(Validator::equals($this->statement,"false") 
         || Validator::equals($psexe,false)) {
        $filelogger->log("%, this=%, stmt=%",
                   array(new DatabaseException(
                           "executing prepared statement failed",3),
                         $this, $stmt));
        throw(new DatabaseException("executing prepared statement failed",3));
      }

      return $this->statement->fetchAll();

    // return immediate sql execution
    } else if(Validator::isa($sql,"string") && Validator::isa($params,"array")
              && count($params)==0) {

      return $this->query($sql)->fetchAll();

    // return previously set prepared statement execution
    } else if(Validator::isa($sql,"null") && Validator::isa($params,"array")
              && count($params)>0) {

      $psexe = $this->statement->execute($params);
      if(Validator::equals($psexe,false)) {
        $filelogger->log("%, this=%",
                   array(new DatabaseException(
                           "executing prepared statement failed",3),
                         $this));
        throw(new DatabaseException("executing prepared statement failed",3));
      }
      return $this->statement->fetchAll();

    // otherwise there is something wrong with the parameters
    } else {
      $filelogger->log("%, this=%",
                 array(new DatabaseException("invalid parameters",1),$this));
      throw(new DatabaseException("invalid parameters",1));

    }

  }

  /**
   * Run a set of sql statements.
   * @param $stmtb statement batch
   * @param $transact run all statements as a transaction, defaults to true
   * @return array containing all results
   */
  public function batch(StatementBatch $stmtb = null, $transact = true) {

    global $filelogger;

    if(!Validator::isclass($stmtb,"core\db\statement\StatementBatch")) {
      $filelogger->log("batch=%, exception=%",
                 array($stmtb, new DatabaseException("not a StatementBatch",4)));
      throw(new DatabaseException("not a StatementBatch",4));

    } else if(!Validator::isa($transact,"boolean")) {
      $filelogger->log("batch=%, exception=%",
                 array($stmtb, new ParamNotValidException("not a boolean")));
      throw(new ParamNotValidException("not a boolean"));
    }

    $results = array();

    // start a transaction
    if($transact === true) $this->begin();

    try {

      $last_statement = null;
      $last_results = array();
      $updated_params = array();

      foreach($stmtb->get() as $s) {

        $updated_params = array_merge( $updated_params,
          $this->get_updated_params($s, $last_statement,  $last_results) );
        $last_statement = $s;

        $init = true;
        foreach($updated_params as $up) {

          // get the parameter pair
          $up = array_shift($updated_params);
          // but discard the invocation dummy
          if(array_key_exists(":dummy", $up)) $up = array();

          $filelogger->log("ups=%, params=%",
                     array(StringUtil::get_object_string($updated_params),
                           StringUtil::get_object_string($up)));
          $s->set_params($up);

          if($init===true) {
            $init = false;
            // provides the sql for $this->statement
            $last_results = $this->execute($s);
            // set the statements sql to null, further executions will then 
            // use $this->statement
            $s->set_statement();
          } else {
            $last_results = $this->execute($s);
          }

          $filelogger->log("lrs=%",array(StringUtil::get_object_string($last_results)));
          $results = array_merge($results, $last_results);

        }

      }

    // catch any exceptions and rollback if batch is run as transaction 
    } catch(Exception $e) {
      if($this->in()) $this->rollback();
      throw($e); // pass the exception on to the ExceptionHandler 

    }

    // finally commit a transaction if batch was run as
    if($this->in()) $this->commit();

    return $results; 

  }

  /**
   * To execute a statement tree there is need to update the parameter set 
   * for the current statement going to be executed with the results of the 
   * last statement execution.
   * @param $stmt current statement gonna be executed
   * @param $last_stmt last statement executed
   * @param $last_results results for the execution of $last_stmt 
   * @return next parameter set for $stmt
   */
  private function get_updated_params($stmt = null, $last_stmt = null,
                                      $last_results = null) {

    global $filelogger;

    // (probably a multidimensional) array containing param-tupel for the 
    // upcoming statement execution
    $next_statement_params = array();
    if(count($last_results)==0) return array($stmt->get_params());

    // build param-tupel for each result
    foreach($last_results as $lr) {

      if(!Validator::isa($last_stmt,"null")) {
        // set each param that each resultset provides
        $ptupel = array();
        foreach($last_stmt->get_provides() as $p) {
          $filelogger->log("p=%, lr=%",
            array($p,StringUtil::get_object_string($lr)));
          if(array_key_exists($p,$lr)) $ptupel[":".$p] = $lr[$p];
        }

        // add params-tupel  
        $next_statement_params[] = $ptupel;
      }

    }

    return $next_statement_params;

  }

}

?>
