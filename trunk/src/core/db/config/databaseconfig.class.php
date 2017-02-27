<?php

namespace core\db\config;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\map\StringMap as StringMap;

/**
 * Stores configurtion for a database loaded from conf/db/databases.xml in
 * a string map to make it accessible to any underlying database backend 
 * layer which needs it.
 * @author Marc Bredt
 * @see core\db\connect\Connector
 * @see core\db\driver\DatabaseDriver
 * @see core\db\driver\PDODriver
 * @see core\db\vendor\mysql\MySQLPDODriver
 */
class DatabaseConfig {

  /**
   * Stores the database config in a StringMap.
   * @see core\util\map\StringMap
   */
  private $dbc = null;

  /**
   * Setup the database config.
   * @param $x XMLDocument to load the database config from
   */
  public function __construct(XMLDocument $x = null) {
    
    // initialize the string map
    $this->dbc = new StringMap();
    
    // set the values
    $this->dbc->set("name",
                    $x->xpath("normalize-space(string(//database/@name))")); 
    $this->dbc->set("type",
                    $x->xpath("normalize-space(string(//database/type))")); 
    $this->dbc->set("driver",
                    $x->xpath("normalize-space(string(//database/driver))")); 
    $this->dbc->set("host",
                    $x->xpath("normalize-space(string(//database/host))")); 
    $this->dbc->set("port",
                    $x->xpath("normalize-space(string(//database/port))")); 
    $this->dbc->set("user",
                    $x->xpath("normalize-space(string(//database/user))")); 
    $this->dbc->set("pass",
                    $x->xpath("normalize-space(string(//database/pass))")); 
    $this->dbc->set("db",
                    $x->xpath("normalize-space(string(//database/db))")); 
    $this->dbc->set("ssl",
                    $x->xpath("normalize-space(string(//database/ssl))")); 
    $this->dbc->set("persistent",
                    $x->xpath("normalize-space(string(//database/persistent))"));
    $this->dbc->set("charset",
                    $x->xpath("normalize-space(string(//database/charset))")); 
    $this->dbc->set("timeout",
                    $x->xpath("normalize-space(string(//database/timeout))")); 

  }

  public function get_name(){
    return $this->dbc->get("name");
  }

  public function get_type(){
    return $this->dbc->get("type");
  }

  public function get_driver(){
    return $this->dbc->get("driver");
  }

  public function get_host(){
    return $this->dbc->get("host");
  }

  public function get_port(){
    return $this->dbc->get("port");
  }

  public function get_user(){
    return $this->dbc->get("user");
  }

  public function get_pass(){
    return $this->dbc->get("pass");
  }

  public function get_db(){
    return $this->dbc->get("db");
  }

  public function get_ssl(){
    return $this->dbc->get("ssl");
  }

  public function get_persistent(){
    return $this->dbc->get("persistent");
  }

  public function get_charset(){
    return $this->dbc->get("charset");
  }

  public function get_timeout(){
    return $this->dbc->get("timeout");
  }

}

?>

