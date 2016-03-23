<?php

namespace examples\shm;
use \Exception as Exception; 
use core\AutoLoader as AutoLoader;
use core\util\log\FileLogger as FileLogger;
use core\control\Timer as Timer; 
use core\shm\handler\SharedMemoryHandler as SharedMemoryHandler; 

// load the autoloader manually after suffix expansion for files in 
// the include path like 'Log.php'
require_once("../../core/autoloader.class.php");
$al = new AutoLoader(false,"../..");
$al->expand(".test.class.php,.interface.php,.php");
$al->load();

// create a filelogger for this example
$fl = new FileLogger("shmhandler.php");
$fl->logge("%",array($al));
$fl->logge("%",array($fl));

// use a timer to control an action
$timeout = 2;
$tries = 3;

// set and start the timer
$timer = null;
if(strncmp(gettype($timeout),"integer",7)==0 && $timeout>0) 
  $timer = new Timer($timeout);
if($timer===null) {
  $fl->logge(__METHOD__.": %.", array(new TimerException("creation failed")));
  throw(new TimerException("creation failed"));
}   
$timer->start();

// now try to acquire a semaphore
$actiondone = false;
$try = 1;
while($actiondone===false && $timer!==null && $try<=$tries) {

  // run an action 
  $actiondone = false;
  
  // retart the timer
  if($timer->get()==0 && $timer->get_timed_out()) { 
    $fl->logge(__METHOD__.": Try #%. Timer timed out.", array($try));
    $try++;
    $timer->start();
  }   
}   
    
// if action failed 
if($actiondone===false) {
  $fl->logge(__METHOD__.": %.", array(new Exception("action failed",0)));
  throw(new Exception("action failed",0));
}   

// return when e.g. ued in functions
//return $actiondone;

?>

