<?php 
  
  // namespace alias for the filelogger
  use core\util\log\FileLogger as FileLogger;

  // setup a filelogger
  global $filelogger;
  $filelogger = new FileLogger("index.php");
  $filelogger->set_level("DEBUG");
  $filelogger->log("autoloader=%", array($autoloader), "DEBUG");
  $filelogger->log("filelogger=%", array($filelogger), "DEBUG");
  //$c = new core\foo\bar\KaksPups(); // UCE?

?>
