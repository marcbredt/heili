<?php

namespace core\db\connect;
use \PDO as PDO;
use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;

/**
 * A PDO database connection.
 * @author Marc Bredt
 */
class Connection {

  /**
   * DSN needed to connect to a database.
   */
  private $dsn = "";
 
  /**
   * User.
   */
  private $user = "";
 
  /**
   * Password.
   */
  private $pass = "";
 
  /**
   * Array of attributes passed onto the PDO instance.
   */
  private $options = array();

  /**
   * Connection handle.
   */
  private $con = null;

  /**
   * Setup a connection.
   * TODO: driver handling, pdo/mysqli/...
   */
  public function __construct($type = "mysql", $host = "localhost", $port = 3306,
                              $dbname = null, $user = null, $pass = null,
                              $options = null) {

    $this->dsn = $type.":host".$host.";port=".$port.";dbname=".$dbname;
    $this->user = $user;
    $this->pass = $pass;
    $this->options = $options;

  }

  /**
   * Open a connection.
   */
  private function open() {

    global $filelogger;

    if(Validator::isa($this->con,"null")) {
      $this->con = new PDO($this->dsn, $this->user, $this->pass,
                           $this->options);
      $filelogger->info("connection created, connection=%", array($this->con));

    } else {
      $filelogger->warn("connection already exists, connection=%", array($this->con));

    }

  }

  /**
   * Close this connection.
   */
  public function close() {
    $this->con = null;
  }
 
  /**
   * Get the current connection opened.
   * @return connection handle, PDO object
   */ 
  public function get() {
    if(Validator::isa($this->con,"null")) $this->open();
    return $this->con;
  }

  /**
   * Dumps information for this connection.
   * @return string containing information for this connection
   */
  public function __toString() {
    return get_class($this).spl_object_hash($this)."=(".
           "dsn=".$this->dsn.", ".
           "user=".$this->user.", ".
           "pass=****, ".
           "con=".get_class($this->con).spl_object_hash($this->con)."{ ".
             StringUtil::get_object_string($this->con)." }, ".
           "options=".StringUtil::get_object_string($this->options).
           ")";
  }

}

?>
