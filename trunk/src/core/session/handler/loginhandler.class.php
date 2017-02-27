<?php

namespace core\session\handler;
use core\object\handler\Handable as Handable;

/**
 * Class to handle a login action.
 * @author Marc Bredt
 * @see core\session\handler\ActionHandler;
 */
class LoginHandler implements Handable {

  public function handle() {

    global $inputprovider;

    // TODO: access input variables via $inputprovider
    if(isset($_POST["core:email"]) && isset($_POST["core:password"])) {

      // get necessary credentials from a global var $_GET/REQUEST
      $email = $_POST["core:email"];
      $pass = $_POST["core:password"];

      // pin em onto the sessions user object
      $user = unserialize($session->get("user"));
      $user->set_email($email);
      $user->set_pass($pass);
    
      // serialize the user again
      $session->set("user",null,serialize($user));
      
    }

    // and try to authenticate the user
    Authenticator::authenticate();

    // print the session after the user was authenticated to exclude 
    // credentials from log
    $filelogger->info("session=% ", array($session));

    // TODO: redirect to the user panel, currently to home
    header("Location: /");
  
  }

}
