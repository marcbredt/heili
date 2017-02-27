<?php 
 
  // user stuff

  // namespace alias definitions
  use core\util\param\Validator as Validator;
  use core\session\auth\user\User as User;

  // restore the user from session, or initiate an empty one
  global $session;
  if($session->has("user") && !Validator::isempty($session->get("user"))) { 
    $user = unserialize($session->get("user"));
  } else { 
    $user = new User(); 
    $user->set_role("guest");
    $session->set("user",null,serialize($user)); 
  }

?>
