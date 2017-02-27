<?php

namespace examples\shm;
use core\AutoLoader as AutoLoader;
use core\util\log\FileLogger as FileLogger;
use core\shm\handler\SharedMemoryHandler as SharedMemoryHandler; 

// load the autoloader manually after suffix expansion for files in 
// the include path like 'Log.php'
// adjust the path prefix during test phase where PHPUnit processes uncovered
// files as well
$pprefix = "../..";
if(!strncmp(dirname(__FILE__),getcwd(),strlen(getcwd()))) $pprefix = "src";
require_once($pprefix."/core/autoloader.class.php");

$al = new AutoLoader(false,$pprefix."/");
$al->expand(".test.class.php,.interface.php,.php");
$al->load();

// create a filelogger for this example
$fl = new FileLogger("shmhandler.php");
$fl->logge("%",array($al));
$fl->logge("%",array($fl));

// create a loggable shared memory handler, put and get e.g. conf variables
//$shm_h = new SharedMemoryHandler(); 
//$shm_h->create();
//$shm_h->put(array("conf","ueberconf:present"));
//echo "r='".$shm_h->get()."'<br/>";
//$shm_h->destroy();

?>

