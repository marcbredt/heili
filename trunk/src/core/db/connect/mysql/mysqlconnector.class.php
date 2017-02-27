<?php

namespace core\db\connect\mysql;
use core\db\connect\Connector as Connector;

/**
 * A MySQL connector. Used to set MySQL dependent stuff like e.g
 * PDO::MYSQL_ATTR_SSL_* attributes.
 * @author Marc Bredt
 */

class MySQLConnector extends Connector {

  /**
   * Set MySQL dependent stuff like e.g. PDO::MYSQL_ATTR_SSL_* attributes.
   */
  protected function set() {
    // append values to setup and probably reinstantiate any cached connection
  }

}

?>
