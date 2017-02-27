<?php

namespace examples\shm;
use \DOMDocument as DOMDocument;
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
$fl = new FileLogger("loadenv.php");
$fl->logge("%",array($al));
$fl->logge("%",array($fl));

// parameters for shmh and key
$arch = "32"; $load = true; $size = 512;
$upper = intval(pow(2,$arch-1)-1);
$lower = intval(-1*$upper); # exponent -1 due to twos complement
                            # subtract -1 to avoid flipping bits on LSB = 1

// setup the handler and handle a shared memory segment
//$sh = new SharedMemoryHandler();
//$sh->create($load,$size,$lower,$upper);
//$sh->attach("w"); // put should invoke any (writing) attachment

// load/initialize server side global storage/variables
//$d = new DOMDocument(); 
//$d->loadXML("&lt;?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\" ?&gt;".
//            "<root>".
//              "<foo />".
//            "</root>");
//$sh->put("dummyconfig", $d);

//$sh->detach(); // any access mechanism should handle this as well
// do not free as this would remove the segment and the semaphores

?>
