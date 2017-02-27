<?php

namespace core\db\connect;
use \PDO as PDO;
use core\util\param\Validator as Validator;
use core\util\map\StringMap as StringMap;
use core\util\xml\XMLDocument as XMLDocument;
use core\db\connect\Connectable as Connectable;
use core\db\config\DatabaseConfig as DatabaseConfig;

/**
 * An abstract connector class to provide connections to a database.
 * Although it is declared abstract the connection functions are implemented.
 * The reason for is that any sepcific PDO driver or connector needs some 
 * specific values set before to work properly. 
 * This connector uses the PDO module to provide database functionality.
 * @author Marc Bredt
 */

abstract class Connector implements Connectable {

  /**
   * Stores the connector setup.
   */
  private $setup = null;

  /**
   * Stores connection options which can be derived from the DatabaseConfig.
   */
  private $conoptions = null;

  /**
   * Stores the current connection to the database.
   */
  private $connection = null;

  /**
   * Create a connector.
   * @param $dbc complete DatabaseConfig containing all database configurations
   * @see DatabaseConfig
   * TODO: driver handling, pdo/mysql/...
   */
  public function __construct(DatabaseConfig $dbc = null) {
  
    // setup the logger
    global $filelogger;
 
    // setup connection specific tags
    $this->setup = new StringMap();
    $this->setup->set("type",trim($dbc->get_type()));
    $this->setup->set("host",trim($dbc->get_host()));
    $this->setup->set("port",trim($dbc->get_port()));
    $this->setup->set("db",trim($dbc->get_db()));
    $this->setup->set("user",trim($dbc->get_user()));
    $this->setup->set("pass",trim($dbc->get_pass()));
    $this->setup->set("ssl",trim($dbc->get_ssl()));
    $this->setup->set("persistent",trim($dbc->get_persistent()));
    $this->setup->set("timeout",trim($dbc->get_timeout()));

  }

  /**
   * Connect to a database. This function can be expanded choosing a database
   * or cluster node to establish a connection on.
   * @return 
   * TODO: connection setup via driver name
   *       any PDODriver => PDOConnection -> DSN
   *       any ODBCDriver => ODBCConnection -> ::p/connect()
   */ 
  public function connect() {

    // create a connection
    if(Validator::isa($this->connection,"null")) 
      $this->connection = new Connection(
                            $this->setup->get("type"),$this->setup->get("host"),
                            $this->setup->get("port"),$this->setup->get("db"),
                            $this->setup->get("user"),$this->setup->get("pass"),
                            $this->conoptions);

    // set the connections database handle
    $this->connection->get();
  }

  /**
   * Disconnect from a database.
   * @return true if connection was established successfully, otherwise false
   */ 
  public function disconnect() {
    $this->connection->close();
  }

  /**
   * Get the connection currently set or connect.
   * @return an active connection
   */
  public function get_connection() {
    if(Validator::isa($this->connection,"null")) $this->connect();
    return $this->connection;
  }

}

?>
