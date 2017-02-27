<?php

namespace examples\logserial;
use core\AutoLoader as AutoLoader;
use core\util\log\FileLogger as FileLogger;

// autoloader
require_once("../../../core/autoloader/autoloader.class.php");
$al = new AutoLoader(false,"../../..");
$al->expand(".test.class.php,.class.php,.interface.php,.include.php,.php");
$al->load();

// create a filelogger for this example
$fl = new FileLogger("logserial.php");
$fl->log("%",array($al));
$fl->log("%",array($fl));

// create a loggable and serializeable obje
class Foo {
  private $foo = "bar";
  public function __construct() {
    global $fl;
    $fl->log("Message from %", array(__FILE__));
    $fl->log("Message from %", array(__CLASS__), "DEBUG");
  }
}
$f = new Foo();

// serialize it
$serialized = $f->serialize();
echo "'".$serialized."'<br/>";

// and deserialize it
echo "'".var_export($f->unserialize($serialized),true)."'<br/>";

?>
