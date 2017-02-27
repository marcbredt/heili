<?php

namespace examples\shm;
use \Exception as Exception; 
use core\AutoLoader as AutoLoader;
use core\util\log\FileLogger as FileLogger;
use core\util\param\Validator as Validator;
use core\control\Timer as Timer; 
use core\shm\handler\SharedMemoryHandler as SharedMemoryHandler; 

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
$fl = new FileLogger("shmhandler.php");
$fl->logge("%",array($al));
$fl->logge("%",array($fl));

// use a timer to control an action
$timeout = 2;
$tries = 3;

// set and start the timer
$timer = null;
if(Validator::isa($timeout,"integer") && $timeout>0) 
  $timer = new Timer($timeout);
if($timer===null) {
  $fl->logge("%.", array(new TimerException("creation failed")));
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
    $fl->logge("Try #%. Timer timed out.", array($try));
    $try++;
    $timer->start();
  }   
}   
    
// if action failed 
if($actiondone===false) {
  $fl->logge("%.", array(new Exception("action failed",0)));
  //throw(new Exception("action failed",0));
}   

// return when e.g. ued in functions
//return $actiondone;

?>

