<?php

  // display local errors
  global $session;

  echo $session->get("error","message");
  if ( $session->get("error","trace")===true )
    echo "<br /><br />".$session->get("error","tracemsg");
  // local/temporary errors can always be unset
  $session->set("error",null,array());

?>
