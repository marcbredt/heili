<?php 

  // session stuff

  // namespace alias for the session class 
  use core\session\handler\SessionHandler as SessionHandler;
  use core\session\Session as Session;

  // to implement ones own cookie based session management
  // set session.use_cookies = 0 and override the session handler via
  // session_set_save_handler() like SessionHandler::__construct()
  // actually does

  global $sessionhandler, $session;
  $sessionhandler = new SessionHandler();
  $session = new Session(SESSION_NAME);
  // validation need to be done after setup as the exception handler will 
  // access the global variable $session but is not able to provide access
  // from inside Session::__construct()
  $session->validate(); // check if setup was successful
  $session->restore();
  $session->validate(); // check if restore was successful 

  // TODO: keep in mind $_COOKIE need to be checked additionally later when running
  //       core\http\Request->validate() and probably unset

?>
