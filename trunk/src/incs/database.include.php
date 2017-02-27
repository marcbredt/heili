<?php

  // TODO: should only be visible or adjustable via kind of admin panel
  //       currently for every client con the selector is invoked 
  //       laterwards the database setting from shm should be used 
  use core\db\connect\Selector as Database;

  global $database;
  $database = new Database();

?>
