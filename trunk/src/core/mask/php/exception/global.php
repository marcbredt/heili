<?php

  global $session;

  // namespace alias definitions
  use core\util\param\Validator as Validator;
  use core\session\Session as Session;

  // print the error
  /*
  if(!Validator::isa($session,"null")) {
    echo $session->get("error","message");
    if ( $session->get("error","trace")===true )
      echo "<br /><br />".$session->get("error","tracemsg");
  } else {
    echo "An error occured.";
  }
  */

?>  
