<?php

namespace core\db\connect;
use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;
use core\db\config\DatabaseConfig as DatabaseConfig;

/**
 * This selector allows to initiate connections to a specified database.
 * It is useful when switching between databases or storing data on different
 * databases. E.g. protocol sql actions like updates, insertions or deletions
 * in a protocol database. Simply create another selector passing the name
 * @author Marc Bredt
 */
class Selector {

  private $driver = null;

  /**
   * Setup the database selector.
   * @param $db database to connect to, defaults to //databases/@default
   */
  public function __construct($db = "") {

    global $filelogger;

    // configurations
    $xdriver = new XMLDocument(PATH_CONF."/db/drivers.xml",PATH_DTD."/db/drivers.dtd");
    $xdbases = new XMLDocument(PATH_CONF."/db/databases.xml",PATH_DTD."/db/databases.dtd");

    // setup the database driver
    $defaultdb = $xdbases->xpath("normalize-space(string(//databases/@default))");
    $database = (!Validator::isempty($db) ? $db : $defaultdb);
    $xdbc = $xdbases->xpath("//database[@active=\"y\" and @name=\"".$database."\"]",true);
    $dbconf = new DatabaseConfig($xdbc);
    $dclass = $xdriver->xpath("normalize-space(string(//driver[@name=\"".
                              $dbconf->get_driver()."\"]/@class))");
    $filelogger->debug("db=%, defaultdb=%, database=%, dclass=%, xdbc=%, dbc=%",
                       array($db,$defaultdb,$database,$dclass,$xdbc,$dbconf));
    $this->driver = new $dclass($dbconf);

  }

  public function get_driver() {
    return $this->driver;
  }

}

?>
