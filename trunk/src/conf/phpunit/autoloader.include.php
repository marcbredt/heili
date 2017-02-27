<?php

  // NOTE: no need to load the phpunit autoloader or token-stream directly
  //       as the autoloader below is able to load classes represented 
  //       by their names, e.g. Class_Name masquerades Class/Name.php as 
  //       phpunit/token-stream does for, e.g. PHPUnit_Framework_TestCase
  //       
  // NOTE: beware of classes that do not follow the structure mentioned above
  //       e.g. PHP_Token_OPEN_TAG is defined in /usr/share/php/PHP/Token.php
  //       and therefor need to be included manually or via the corresponding
  //       autoloader for testing purposes

  require_once("/usr/share/php/PHP/Token.php");
  //require_once("/usr/share/php/PHP/Token/Stream/Autoload.php");

  require_once("core/autoloader/autoloader.class.php");
  require_once("core/autoloader/devisor.class.php");

  use core\autoloader\AutoLoader as AutoLoader;
  use core\util\log\FileLogger as FileLogger;

  $al = new AutoLoader(false, ".");
  $al->expand(".test.class.php,.class.php,.interface.php,.php,.include.php");
  $al->load();

  // and global variables, necessary for generating coverage reports
  global $filelogger;
  $filelogger = new FileLogger("PHPUnit","../../log/test/heili-test.log");
  $filelogger->set_level("DEBUG");
 
?>
