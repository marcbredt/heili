<?php 
  
  // the following files are the only files which need to be required explicitly
  // as long as PHP >= 5.1.2 introducing the spl_autoload_* functions is used
  require_once("../core/autoloader/autoloader.class.php");
  require_once("../core/autoloader/devisor.class.php");

  // namespace alias for the autoloader
  use core\autoloader\AutoLoader as AutoLoader;

  // setup the core autoloader
  $autoloader = new AutoLoader(false, dirname(__FILE__)."/..");
  $autoloader->expand(".class.php,.interface.php,.include.php,.php");
  $autoloader->load();

?>
