<?php
 
  namespace examples;
  use core\AutoLoader as AutoLoader;
  use core\util\log\FileLogger as FileLogger;
  use core\util\xml\XMLDocument as XMLDocument;
  use core\exception\xml\XMLNotValidException as XMLNotValidException;

  // load the autoloader manually after suffix expansion for files in 
  // the include path like 'Log.php'
  require_once("../../core/autoloader.class.php");
  $al = new AutoLoader(false,"../../");
  $al->expand(".test.class.php,.interface.php,.php");
  $al->load();

  $fl = new FileLogger("config.php");
  $fl->logge("%",array($al));
  $fl->logge("%",array($fl));

  try {
    $xd = new XMLDocument("../../conf/xml/core/php-init.xml",
                          "../../conf/dtd/core/php-init.dtd");
    $fl->logge("%",array($xd));

  } catch(XMLNotValidException $xnve) {
    echo "XMLNotValidException catched<br />";

  }

?>
