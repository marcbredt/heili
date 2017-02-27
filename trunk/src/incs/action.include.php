<?php 

  // namespace alias for the action handler
  use core\util\map\StringMap as InputProvider;
  use core\session\handler\ActionHandler as ActionHandler;
  
  // action evaluation
  global $inputprovider;
  $inputprovider = new InputProvider();
  ActionHandler::handle();

?>
