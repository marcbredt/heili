<?php

  // cookie stuff
  use core\session\Cookie as Cookie;

  // set a session cookie to avoid loop holes 
  global $session;
  $cookie = new Cookie();
  $cookie->set($session->get_name(),$session->get_sid());

  // set additional cookies here

?>
