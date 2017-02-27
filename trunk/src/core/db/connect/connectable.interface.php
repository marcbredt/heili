<?php

namespace core\db\connect;

interface Connectable {

  /* connective functions */
  //public function open();
  //public function close();
  public function connect();
  public function disconnect();

}

?>
