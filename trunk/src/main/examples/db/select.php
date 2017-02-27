<?php

  namespace examples\db;
  use core\autoloader\AutoLoader as AutoLoader;
  use core\util\log\FileLogger as FileLogger;
  use core\db\data\DataGetter as DataGetter;
  use core\db\connect\Selector as Selector;

  // autoloader
  require_once("../../../core/autoloader/autoloader.class.php");
  require_once("../../../core/autoloader/devisor.class.php");
  $autoloader = new AutoLoader(false, "../../..");
  $autoloader->expand(".class.php,.interface.php,.include.php,.php");
  $autoloader->load();

  // some constants to let this example work properly
  define("PATH_CONF","../../../conf");
  define("PATH_DTD","../../../conf/dtd");

  // filelogger
  $filelogger = new FileLogger("select.php","./select.log");
  $filelogger->set_level("DEBUG");
  $filelogger->set_color(true);

  // $database is the global variable DataGetter accesses
  //global $database; 

  $database = new Selector();
  $d1 = new DataGetter("table3");
  print_r($d1->get());

  $database = new Selector("heili_test");
  $d2 = new DataGetter("table4");
  print_r($d2->get(array(":email"=>"admin@localhost")));

?>
