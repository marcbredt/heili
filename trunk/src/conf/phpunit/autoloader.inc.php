<?php

  // NOTE: no need to load the phpunit autoloader or token-stream directly
  //       as the autoloader below is able to load classes represented 
  //       by their names, e.g. Class_Name masquerades Class/Name.php as 
  //       phpunit/token-stream does for, e.g. PHPUnit_Framework_TestCase

  // auto loading project classes
  // when running build script from .. prepend src directory
  // AND modify the path the autoloader should search for project classes
  //   as it defaults to '.' = 'src' for the build script
  require_once("@php-dir-include@/core/autoloader.class.php");

  // get an AutoLoader
  $al = new core\AutoLoader(false, "@php-dir-include@");

  // for phpunit, token, log and test classes
  $al->expand(".test.class.php,.interface.php,.php");

  // initialize $al manually to expand the suffix list first
  $al->load();
 
?>
