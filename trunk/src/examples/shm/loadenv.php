<?php

require_once("semaphore.class.php");
require_once("semaphorehandler.class.php");
require_once("timer.class.php");
require_once("sharedmemorysegment.class.php");
require_once("sharedmemoryhandler.class.php");

// handle a shared memory segment
$sh = new core\shm\handler\SharedMemoryHandler(); 
$sh->create();
$sh->attach();

// load/initialize server side global storage/variables
$d = new Document(); $d->loadXML('<?xml version="1.0" encoding="UTF-8" standalone="no" ?><root><foo /></root>');
$sh->put("dummyconfig", $d);

$sh->detach();
// do not free as this would remove the segment and the semaphores

?>
