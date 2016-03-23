<?php

  namespace core;

  // look at ../../configset to update var_dump values
  //ini_set("xdebug.var_display_max_depth",-1);
  //ini_set("xdebug.var_display_max_children",-1);
  //ini_set("xdebug.var_display_max_data",-1);
 
  // REQUIRED FOR TESTING
  // token stream
  //require_once("/usr/share/php/PHP/Token/Stream/Autoload.php");
  // phpunit, loads files from phpunit-{selenium,story} too
  //require_once("/usr/share/php/PHPUnit/Autoload.php");

  // REQUIRED FOR RUNNING A WEB APP
  // http://de2.php.net/manual/en/language.oop5.autoload.php
  // when running build script from .. prepend src directory
  // AND modify the path the autoloader should search for project classes
  //   as it defaults to '.' = 'src' for the build script
  //require_once("core/autoloader.class.php");
  // auto loading project classes
  require_once("core/autoloader.class.php");
  $al = new AutoLoader();
  $al->expand(".test.class.php,.php");

  // http://stackoverflow.com/questions/32478962
  $logger = new util\log\FileLogger(NULL, "./logger.log"); 
  //$logger->logge("eins=%",array($logger),"DEBUG");

  //$unknown = new UnknownClass(); 

  //echo var_dump($al->getp())."\n".var_dump($al->getc())."\n".var_dump($al->gete())."\n"; 

  // autoloading test classes, add .php
  //$al->expand(".test.class.php,.php");
  //$lt = new test\log\FileLoggerTest();
  //echo var_dump($al->getp())."\n".var_dump($al->getc())."\n".var_dump($al->gete())."\n"; 
  //$lt->testNamespace();

  //$unknown = new UnknownClass(); 

  // TODO: check for multiple autoloads in phpunit 
  // TODO: namespaces for testsuites in phpunit 

?>
