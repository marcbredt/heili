<?php

namespace core\session\handler;

class LoggoffHandler implements Handable {

  public function handle() {

    // update the user
    $user = unserialize($session->get("user"));
    $user->set_auth(false);
    $session->set("user",null,serialize($user));

    // destroy the session 
    session_destroy();

    // and redirect 
    header("Location: /");

  }

}

?>
