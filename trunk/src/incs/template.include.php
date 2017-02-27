<?php

  // template stuff

  // namespace alias
  use core\layout\Template as Template;
  use core\exception\handler\ExceptionHandler as ExceptionHandler;

  global $template, $session, $exceptionhandler;

  // load the default template if none set for this session
  if($session->has("template")) $template = new Template($session->get("template"));
  else $template = new Template(TEMPLATE_DEFAULT);

  // handle local exception means those that respond on user actions but do not
  // interrupt the application's process flow like e.g. LoginException or any
  // other input exceptions e.g. regex mismatches
  // in this case $template will be adjusted importing <div id="lerror"> from
  // mask exception/local.php
  $exceptionhandler->handle_previous_local_exception();

  // load the adjusted template, use buffering for exceptions being catched
  ob_start();
  echo $template->get();
  ob_end_flush();

?>
