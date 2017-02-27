<?php 

  // namespace alias for the exception handler
  use core\exception\handler\ExceptionHandler as ExceptionHandler;
  
  // setup an exception handler for this session 
  global $exceptionhandler;
  $exceptionhandler = new ExceptionHandler();

?>
