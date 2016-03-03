<?php

namespace examples\shm;
use core\AutoLoader as AutoLoader;
use core\util\log\FileLogger as FileLogger;

// load the autoloader manually after suffix expansion for files in 
// the include path like 'Log.php'
require_once("../../core/autoloader.class.php");
$al = new AutoLoader(false,"../../");
$al->expand(".test.class.php,.interface.php,.php");
$al->load();

// create a filelogger for this example
$fl = new FileLogger("semaphore.php");
$fl->logge("%",array($al));
$fl->logge("%",array($fl));

// TODO: show semaphore usage

?>

