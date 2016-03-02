<?php

namespace examples;
use core\AutoLoader as AutoLoader;
use core\util\log\FileLogger as FileLogger;
use core\object\LoggableObject as LoggableObject;

// load the autoloader manually after suffix expansion for files in 
// the include path like 'Log.php'
require_once("../../core/autoloader.class.php");
$al = new AutoLoader(false,"../../");
$al->expand(".test.class.php,.interface.php,.php");
$al->load();

// create a filelogger for this example
$fl = new FileLogger("index.php");
$fl->logge("%",array($al));
$fl->logge("%",array($fl));

// create a loggable and serializeable object
class Foo extends LoggableObject {
  private $foo = "bar";
  public function __construct() {
    $this->log("Message from %", array(__FILE__));
    $this->log("Message from %", array(__CLASS__), "DEBUG");
  }
}
$f = new Foo();

// the getFile function can be only called directly if LoggableObject's
// $logger is set via $f->logger->getFile()
// to avoid any problems when $f->logger===null the interface Loggable
// declares methods to the FileLogger which LoggableObject should wrap
echo "check '".$f->getFile()."' for log messages written<br/>";

// serialize it
$serialized = $f->serialize();
echo "'".$serialized."'<br/>";
// and deserialize it
echo "'".var_export($f->unserialize($serialized),true)."'<br/>";

?>
