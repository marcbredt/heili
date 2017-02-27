<?php

namespace core\db\driver;

interface Drivable {

  /* statement functions */
  public function prepare();
  
  /* adjusting functions */ 
  public function set();
  public function get();

  /* transactional functions */
  public function begin();
  public function commit();
  public function in();
  public function rollback();

  /* executive functions */
  public function query();
  public function fetch();
  public function fetchall();

  /* meta functions */
  public function info();
  public function meta();
  public function stat();

  /* error functions */
  public function error();

}


?>
