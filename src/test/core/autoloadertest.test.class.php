<?php

namespace test\core;
use \PHPUnit_Framework_TestCase as PHPUnit_Framework_TestCase;
use \ReflectionFunction as ReflectionFunction;

/**
 * As autoloader.inc.php is bootstrapped for this project you
 * cannot really "trust" tests run using this class
 * because all classes required will be probably included by 
 * the bootstrap instance. You still can "test" some 	
 * @see AutoLoaderStandaloneTest
 * @author Marc Bredt
 */
class AutoLoaderTest extends PHPUnit_Framework_TestCase {

  /**
   * Test namespace usage by creating an instance of AutoLoader
   * @small
   */
  public function testNamespace() {
    $al = new \core\AutoLoader();
    $this->assertNotNull($al);
    $al = null;   
  }

  /**
   * Test creation for different directory/extensions
   * @small
   */
  public function testCreation() {
    $al = new \core\AutoLoader("src/test/core/");
    $this->assertNotNull($al);
    $al = null;   
    $al = new \core\AutoLoader("src/test/core/",".class.php,.test.class.php,.php");
    $this->assertNotNull($al);
    $al = null;   
    $al = new \core\AutoLoader("",".class.php");
    $this->assertNotNull($al);
    $al = null;   
    $al = new \core\AutoLoader("src/test/core","");
    $this->assertNotNull($al);
    $al = null;   
    $al = new \core\AutoLoader("","");
    $this->assertNotNull($al);
    $al->__destruct();
    $al = null;   
  }

  /**
   * Tests file suffix expansion/registration
   * @small
   */
  public function testRegisteredExtensions() {
    $al = new \core\AutoLoader();
    $al->expand(null);
    $this->assertRegExp("/.class.php/",
                        spl_autoload_extensions());
    $al->expand(array());
    $this->assertRegExp("/.class.php/",
                        spl_autoload_extensions());
    $al->expand($al);
    $this->assertRegExp("/.class.php/",
                        spl_autoload_extensions());
    $al->expand(".test.class.php,.php");
    $this->assertRegExp("/.class.php,.test.class.php,.php/",
                        spl_autoload_extensions());
    $al = null; 
  }

  /**
   * Test include path return. Should contains $p set.
   * @small
   */
  public function testIncludePathReturned() {
    $p = "src/core/files";
    $al = new \core\AutoLoader(true,$p);
    $this->assertRegExp("/".preg_replace("/\\//","\\\\/",$p)."/", $al->getp()); 
    $al = null; 
  }

  /**
   * Test extensions returned.
   * @small
   */
  public function testExtensionsReturned() {
    $al = new \core\AutoLoader();
    $this->assertRegExp("/\.class\.php/", $al->gete()); 
    $al = null; 
  }

  /**
   * Test string representation of the AutoLoader instance.
   * @small
   */
  public function testStringRepresentation() {
    /*
    $al = new core\AutoLoader();
    $this->assertStringStartsWith("core\AutoLoader-",$al);
    $al = null;
    */
  }

  /**
   * Test if classes loaded are available. Due to autoloader.inc.php
   * is bootstrapped each time phpunit is run all classes required
   * are probably already loaded. Therefor we do not need to require
   * manually and just check for presence here. Have a close look at 
   * <code>var_export($al->getc(),true);</code> to verify.
   * @see AutoLoaderStandaloneTest
   * @small
   */
  public function testDeclaredClasses() {
    $al = new \core\AutoLoader();
    $this->assertContains("core\util\xml\XMLDocument",$al->getc());
    $al = null; 
  }

  /**
   * Test unloading an initialized AutoLoader
   *
   * NOTE: as the unload function resets the autoloading function
   *       classes needed for phpunit will not be found anymore
   *       so the (default) autoloader for phpunit need to be loaded
   *       before calling this function
   * NOTE: tests are not executed in isolation so every AutoLoader
   *       created in other tests bloats the current include path.
   *       Therefor restore it completely where it is necessary.
   * NOTE: Watch out for the garbage collector at script ends.
   *       It calls the __destruct method so check what is set in it.
   *
   * @small
   */
  public function testUnloading() {
    $dip = ".:/usr/share/php:/usr/share/pear";
    set_include_path($dip); // restore default include path, see note above
    $dex = ".inc,.php";
    spl_autoload_extensions($dex); // restore default extensions
    
    $p = "src";
    $al = new \core\AutoLoader(true, $p, ",");

    // check extensions set
    $al->expand(".php,.inc.php,.class.php,.interface.php,.test.class.php");
    $this->assertEquals(".php,.inc.php,.class.php,.interface.php,.test.class.php",
                         spl_autoload_extensions());

    // store some values to be checked later, get extension after expansion 
    $al_oip = $al->getop(); // store old include path
    $al_ip = $al->getp(); // store include path
    $al_oe = $al->getoe(); // store old extensions
    $al_e = $al->gete(); // store extensions

    // unload 
    $al->unload();

    // check old include path is nulled and current is restored correctly
    // as the include path is set during the phpunit bootstrap execution
    // the include path is not minimal anymore
    $this->assertNotRegExp("/".preg_replace("/\\//","\\\\/",$p."\\\\/")."/", $al_oip); 
    $this->assertNULL($al->getop());

    // check include path is nulled and restored correctly
    $this->assertRegExp("/".preg_replace("/\\//","\\\\/",$p."/")."/", $al_ip); 
    $this->assertNotEquals($al_ip,get_include_path());
    $this->assertNULL($al->getp());

    // check old extensions are nulled and restored correctly
    $this->assertEquals($al_oe,spl_autoload_extensions());
    $this->assertNULL($al->getoe());

    // check extensions are null and restored correctly (default: .inc,.php)
    $this->assertNotEquals($al_e,spl_autoload_extensions());
    $this->assertNULL($al->gete());

    // check the spl_autoload_fuctions() for closures bound to this class
    $rf = null;
    foreach(spl_autoload_functions() as $f) {
      $rf = new ReflectionFunction($f);
      if($rf->isClosure() && !is_null($rf->getClosureThis())) {
        if(strncmp(gettype($rf->getClosureThis()),"object","6")==0)
          $this->assertNotEquals(get_class($rf->getClosureThis()),get_class($al));
      }
    }

    $al = null; 
  }

  /**
   * Test the state setting.
   * @small
   */
  public function testStateModification() {
    $al = new \core\AutoLoader();
    $this->assertEquals(false, $al->gett());
    $al->sett(null);
    $this->assertEquals(false, $al->gett());
    $al->sett(false);
    $this->assertEquals(false, $al->gett());
    $al->sett(true);
    $this->assertEquals(true, $al->gett());
    $al = null;
  }

}

?>
