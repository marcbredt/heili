<?php

// example file on how to generate keys for
// - semephores 
// - segments
// - uuids

namespace examples\key;
use core\util\key\Key as Key;
use core\AutoLoader as AutoLoader;
use core\util\log\FileLogger as FileLogger;
use core\exception\shm\SemaphoreException as SemaphoreException;

// load an autoloader and a logger, watch out for the relative pathes
// adjust the path prefix during test phase where PHPUnit processes uncovered
// files as well
$pprefix = "../..";
if(!strncmp(dirname(__FILE__),getcwd(),strlen(getcwd()))) $pprefix = "src";
require_once($pprefix."/core/autoloader.class.php");

$al = new AutoLoader(false,$pprefix."/");
$al->expand(".test.class.php,.class.php,.interface.php,.php");
$al->load();

// create a filelogger for this example
$fl = new FileLogger();
$fl->logge("%",array($al));
$fl->logge("%",array($fl));


// semaphore keys

// first a free one
//$key = new Key("sem",1,1); // normally 1 should be free
//echo "SEMK: ".$key."<br>";
// then one in a range with used ones
//$key = new Key("sem",0,0); // 0 should already be used by e.g. apache
//echo "SEMK: ".$key."<br>";


// segment keys 

// first handle invalid keys, 0 skipped, 1>0 
try { $key0 = new Key("seg",0,0); $key0->get(); echo "SEGK0: ".$key0."<br>";
} catch(SemaphoreException $se) { echo "SEGK0: ".$key0."<br>".$se."<br><br>"; }
// get a free one <> 0, normally 1 should be free, check with 'ipcs'
// 0 should always be skipped by implementation
$key1 = new Key("seg",0,1); $key1->get(); echo "SEGK1: ".$key1."<br>";
// then one in a range with used ones, therefor create a dummy segment first
$sid = shmop_open($key1->get_key(),"n",0600,512);
$key2 = new Key("seg",0,2); $key2->get(); echo "SEGK2: ".$key2."<br>";
// remove any previously created (dummy) segment
$ret = shmop_delete($sid); 


// uuids

// as uuids are time bound there is no need to pass a key range
$key3 = new Key("uuid"); echo "UUID: ".$key3."<br>";

?>
