<?php

  global $filelogger;
  use core\html\form\FormLoader as FormLoader;
  $htmlform = FormLoader::load(PATH_CONF."/html/form/login.xml");
  $filelogger->debug("form = \n%", array($htmlform));
  echo $htmlform;

?>
