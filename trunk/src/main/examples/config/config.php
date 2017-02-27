<?php
 
  namespace examples\examples;
  use core\AutoLoader as AutoLoader;
  use core\util\log\FileLogger as FileLogger;
  use core\util\param\Validator as Validator;
  use core\util\xml\XMLDocument as XMLDocument;
  use core\exception\xml\XMLNotValidException as XMLNotValidException;

  // load the autoloader manually after suffix expansion for files in 
  // the include path like 'Log.php'
  // adjust the path prefix during test phase where PHPUnit processes uncovered
  // files as well 
  $pprefix = "../..";
  if(!strncmp(dirname(__FILE__),getcwd(),strlen(getcwd()))) $pprefix = "src";
  require_once($pprefix."/core/autoloader.class.php");

  $al = new AutoLoader(false,$pprefix."/");
  $al->expand(".test.class.php,.class.php,.interface.php,.php");
  $al->load();

  $fl = new FileLogger("config.php");
  $fl->logge("%",array($al));
  $fl->logge("%",array($fl));

  try {

    $xd = new XMLDocument("../../conf/xml/core/php-init.xml",
                          "../../conf/dtd/core/php-init.dtd");
    $fl->logge("%",array($xd));

  } catch(XMLNotValidException $xnve) {}

?>
