<?php

namespace test\core;
use \PHPUnit_Framework_TestCase as PHPUnit_Framework_TestCase;

/**
 * This class tests the "real" autoloading function.
 * @author Marc Bredt
 */
class AutoLoadingDevisorTest extends PHPUnit_Framework_TestCase {

  /** 
   * Test the autoloading function alf by loading different
   * types of classes.
   * @small
   */
  public function testAutoloading() {
    // default path
    $p = "src";

    // create an AutLoader with initiaization disabled 
    // to set testing state first and initialize it manually
    $al = new \core\AutoLoader(false,$p);
    $al->sett(true);
    $al->load();

    // run tests/assertions for a testing state
    $this->alt($al);

    // run same tests/assertions for a non testing state
    $al->unload();
    $al->sett(false);
    $al->expand(".class.php,.include.php,.interface.php,.test.class.php,.inc,.php");
    $al->load();
    $this->alt($al);

    $al = null;
  }

  /**
   * Assertions for AutoLoaderTest::testAutoloading().
   * @param $autoloader an Autooader instance
   * @see AutoLoader
   */
  public function alt($autoloader = null) {
    // inside project namespace
    // outside project namespace
    // external package classes with underscore
    //   Class_Name_SomeWhere -> Class/Name/SomeWhere.php
    // external package classes without underscore
    //   Log.php
    // lower and mixed case classes/classfile names 
  }

}

?>
