<?php

namespace examples\shm;
use core\AutoLoader as AutoLoader;
use core\util\log\FileLogger as FileLogger;

// load the autoloader manually after suffix expansion for files in 
// the include path like 'Log.php'
// load an autoloader and a logger, watch out for the relative pathes
// adjust the path prefix during test phase where PHPUnit processes uncovered
// files as well
$pprefix = "../..";
if(!strncmp(dirname(__FILE__),getcwd(),strlen(getcwd()))) $pprefix = "src";
require_once($pprefix."/core/autoloader.class.php");

$al = new AutoLoader(false,$pprefix."/");
$al->expand(".test.class.php,.interface.php,.php");
$al->load();

// create a filelogger for this example
$fl = new FileLogger("semaphore.php");
$fl->logge("%",array($al));
$fl->logge("%",array($fl));

// TODO: show semaphore usage

?>

