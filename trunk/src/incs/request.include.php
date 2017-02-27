<?php 

  // namespace alias for the request
  use core\http\Request as Request;
  
  // mass validation first, $_SERVER, $_COOKIE, $_GET, $_POST, ...
  // it validates all globals and dynamic built form data too
  $request = new Request();
  $request->validate();

?>
