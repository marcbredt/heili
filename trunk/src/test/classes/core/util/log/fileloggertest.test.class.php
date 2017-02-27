<?php

namespace test\core\util\log;
use \PHPUnit_Framework_TestCase as PHPUnit_Framework_TestCase;
use core\util\log\FileLogger as FileLogger;
use core\util\param\Validator as Validator;
use core\exception\param\ParamNumberException as ParamNumberException;
use core\exception\param\ParamNotArrayException as ParamNotArrayException;

/*!
 *
 * \page testoverview Testcase Overview
 * \section testutil Utilities
 * \subsection testutilset Testcases for class util\log\FileLogger
 *   PHPUnit_framework_TestCase from package phpunit in /usr/share/php/
 * 
 */
class FileLoggerTest extends PHPUnit_Framework_TestCase {

  /**
   * logger that will be created
   */
  private $logger = NULL;

  /**
   * Simple function to check if a FileLogger can be
   * created using namespaces.
   * @small
   */
  public function testNamespace() {
    // if one wants to access a namespace outside of the class'
    // namespace it need to be prepended with '\' 
    // use another logfile for each FileLogger instace to avoid removal
    //   of lines in the global one
    $this->logger = new FileLogger("LoggerTest","../../log/test/fileloggertest.log");
    $this->assertNotNull($this->logger);
    $this->logger = NULL;
  }

  /**
   * Tests the creation of a file logger.
   * @small
   */
  public function testLoggerCreate() {
    $this->logger = new FileLogger("LoggerTest","../../log/test/fileloggertest.log");
    $this->assertNotNull($this->logger);
    $this->logger = NULL;
  }
  
  /**
   * Tests if the logger is accessible by the world.
   * @small
   */
  public function testLoggerAccess() {
    $this->logger = new FileLogger("LoggerTest","../../log/test/fileloggertest.log");
    $this->assertNotNull($this->logger);
    $this->logger = NULL;
  }
  
  /**
   * Tests the string string formation of file logger.
   * @small
   */
  public function testLoggerFormat() {
    $this->logger = new FileLogger("FileLoggerTest","../../log/test/fileloggertest.log");
    $this->assertStringStartsNotWith(" ", $this->logger->__toString());
    $this->assertStringStartsNotWith("4w5zsdgs", $this->logger->__toString());
    $this->assertStringStartsWith("l.", $this->logger->__toString());
    $this->logger = NULL;
  }
  
  /**
   * Tests the flattening of objects for a file logger.
   * @small
   */
  public function testLoggerFlatten() {
    $this->logger = new FileLogger("FileLoggerTest","../../log/test/fileloggertest.log");
    $this->assertStringStartsWith("string", $this->logger->flatten("string"));
    $this->assertStringStartsWith("Array", $this->logger->flatten(array()));
    $this->assertStringStartsWith("Array", $this->logger->flatten(array("eins")));
    $this->assertStringStartsWith("test\core\util\log\FileLoggerTest", 
                                  $this->logger->flatten($this));
    $this->assertStringStartsWith("l.", $this->logger->flatten($this->logger));
    $this->assertStringStartsWith("NULL", $this->logger->flatten(null));
    $this->logger = NULL;
  }
  
  /**
   * Tests the debug flattening of objects for a file logger.
   * @small
   */
  public function testLoggerFlattenDebug() {
    $this->logger = new FileLogger("FileLoggerTest","../../log/test/fileloggertest.log");
    $this->assertStringStartsWith("OBJECT(", $this->logger->flatten("string", true));
    $this->assertStringStartsWith("OBJECT(", $this->logger->flatten(array(), true));
    $this->assertStringStartsWith("OBJECT(", $this->logger->flatten($this, true));
    $this->assertStringStartsWith("OBJECT(", $this->logger->flatten($this,true));
    $this->assertStringStartsWith("OBJECT(", $this->logger->flatten($this->logger,true));
    $this->assertStringStartsWith("OBJECT()=NULL", $this->logger->flatten(null,true));
    $this->logger = NULL;
  }

  /**
   * Tests the logging itself for a file logger.
   * @small
   */
  public function testLoggerLogging() {
    $this->logger = new FileLogger("FileLoggerTest","../../log/test/fileloggertest.log");
    $this->assertFileNotExists($this->logger->getf()->get_file());
    $this->logger->log("%",array($this));
    $this->assertFileExists($this->logger->getf()->get_file());
    $this->assertStringNotEqualsFile($this->logger->getf()->get_file(),"");
    $this->logger = NULL;
  }

  /**
   * Tests the cleanage of the logfile.
   * @small
   */
  public function testLogfileCleanage() {
    $this->logger = new FileLogger("FileLoggerTest","../../log/test/fileloggertest.log");
    $this->assertFileExists($this->logger->getf()->get_file());
    $this->logger->getf()->clean();
    $this->assertStringEqualsFile($this->logger->getf()->get_file(),"");
    $this->logger = NULL;
  }

  /**
   * Tests if the first line can be obtained from the logfile.
   * @small
   */
  public function testGetFirstLineFromLogfile() {
    $this->logger = new FileLogger("FileLoggerTest","../../log/test/fileloggertest.log");
    $this->logger->getf()->clean();
    $line = $this->logger->getf()->getfirst();
    $this->assertEquals($line, "");    
    $this->logger->log("%\n\n",array($this));
    $line = $this->logger->getf()->getfirst();
    $this->assertNotEquals($line, "");    
    $this->assertNotRegExp('/'.PHP_EOL.'/', $line);    
    $this->logger = NULL;
  }

  /**
   * Tests if the first line can be obtained from the logfile.
   * @small
   */
  public function testGetLastLineFromLogfile() {
    $this->logger = new FileLogger("FileLoggerTest","../../log/test/fileloggertest.log");
    $this->logger->getf()->clean();
    $line = $this->logger->getf()->getlast();
    $this->assertEquals($line, "");    
    $this->logger->log("%\n\n",array($this));
    $line = $this->logger->getf()->getlast();
    $this->assertNotEquals($line, "");    
    $this->assertNotRegExp('/'.PHP_EOL.'/', $line);    
    $this->logger = NULL;
  }
 
  /**
   * Tests for a specific type (string,array,object,...)
   * @large
   * @see Log::stringToPriority for logging types
   */
  public function testLoggerLoggingType() {
    $this->logger = new FileLogger("FileLoggerTest","../../log/test/fileloggertest.log");
    $this->logger->getf()->clean();
    $this->logger->log("%",array($this),"EMERG");
    $line = $this->logger->getf()->getlast();
    $this->assertRegExp('/.* FileLoggerTest \[emergency\] core\\\\util\\\\log\\\\FileLogger test\\\\core\\\\util\\\\log\\\\FileLoggerTest.*/',$line);
    $this->logger->log("%",array($this),"ALERT");
    $line = $this->logger->getf()->getlast();
    $this->assertRegExp('/.* FileLoggerTest \[alert\] core\\\\util\\\\log\\\\FileLogger test\\\\core\\\\util\\\\log\\\\FileLoggerTest.*/',$line);
    $this->logger->log("%",array($this),"CRIT");
    $line = $this->logger->getf()->getlast();
    $this->assertRegExp('/.* FileLoggerTest \[critical\] core\\\\util\\\\log\\\\FileLogger test\\\\core\\\\util\\\\log\\\\FileLoggerTest.*/',$line);
    $this->logger->log("%",array($this),"ERR");
    $line = $this->logger->getf()->getlast();
    $this->assertRegExp('/.* FileLoggerTest \[error\] core\\\\util\\\\log\\\\FileLogger test\\\\core\\\\util\\\\log\\\\FileLoggerTest.*/',$line);
    $this->logger->log("%",array($this),"WARNING");
    $line = $this->logger->getf()->getlast();
    $this->assertRegExp('/.* FileLoggerTest \[warning\] core\\\\util\\\\log\\\\FileLogger test\\\\core\\\\util\\\\log\\\\FileLoggerTest.*/',$line);
    $this->logger->log("%",array($this),"NOTICE");
    $line = $this->logger->getf()->getlast();
    $this->assertRegExp('/.* FileLoggerTest \[notice\] core\\\\util\\\\log\\\\FileLogger test\\\\core\\\\util\\\\log\\\\FileLoggerTest.*/',$line);
    $this->logger->log("%",array($this),"INFO");
    $line = $this->logger->getf()->getlast();
    $this->assertRegExp('/.* FileLoggerTest \[info\] core\\\\util\\\\log\\\\FileLogger test\\\\core\\\\util\\\\log\\\\FileLoggerTest.*/',$line);
    // really bad overhead for debug lines, found ~500k chars long lines
    // therefor this check should just be enabled if the dumped objects
    //   do not contain horrible long lines, therefor temporarily disabled
    // TODO: check getFirstLine for such overheads on long debug lines 
    //$this->logger->log("%",array($this),"DEBUG");
    //$line = $this->logger->getf()->getlast();
    //$this->assertRegExp('/.* FileLoggerTest \[debug\] core\\\\util\\\\log\\\\FileLogger OBJECT\(test\\\\core\\\\util\\\\log\\\\FileLoggerTest.*/',$line);
    $this->logger = NULL;
  }

  /**
   * Tests for parameter exceptions thrown.
   *
   * NOTE: exceptions thrown does not allow further assertions
   *       without catching them explicitly
   *
   * @expectedException core\exception\param\ParamNotArrayException
   * @expectedExceptionMessage Parameters provided are not an array.
   *
   * @expectedException core\exception\param\ParamNumberException
   * @expectedExceptionMessage Invalid amount of parameters passed.
   *
   */
  public function testParamExceptionsThrown() {
    $this->logger = new FileLogger("FileLoggerTest",
                          "../../log/test/fileloggertest.log");
    
    // ParamNotArrayException
    $this->logger->log("A param log: %",$this);
    // ParamNumberException
    $this->logger->log("Two param log: %, %", array($this));
  }

  /**
   * Tests for parameter "exceptions" logged.
   *
   * NOTE: exceptions need to be catched by their fully qualified
   *       namespace or alias defined
   *
   * NOTE: '@exceptedException' does not work in conjunction with
   *       try-catch-blocks. it is just simply ignored then.
   *
   */
  public function testParamExceptionsLogged() { 
    $this->logger = new FileLogger("FileLoggerTest",
                          "../../log/test/fileloggertest.log");
    
    // ParamNotArrayException
    try {
      $this->logger->log("A param log: %",$this);
    } catch(ParamNotArrayException $ex) {
      // additionally check the log file
      $line = $this->logger->getf()->getlast();
      $this->assertRegExp('/.* FileLoggerTest \[warning\] core\\\\util\\\\log\\\\FileLogger line [0-9]+: Message replacements are not provided as an array\./',$line);
    }
    
    // ParamNumberException
    try {
      $this->logger->log("Two param log: %, %", array($this));
    } catch(ParamNumberException $ex) {
      // additionally check the log file
      $line = $this->logger->getf()->getlast();
      $this->assertRegExp('/.* FileLoggerTest \[warning\] core\\\\util\\\\log\\\\FileLogger line [0-9]+: Number of message replacements do not equal\./',$line);
    }

  }

  /**
   * Tests the destruction of a file logger.
   * @small
   */
  public function testLoggerDestroy() {
    // destructors will be called automatically if all references to an object got null'ed
    $this->logger = new FileLogger("LoggerTest","../../log/test/fileloggertest.log");
    $this->logger = NULL;
    $this->assertNull($this->logger);
  }
}

?>
