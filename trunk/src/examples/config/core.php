<?php
  
  require_once("../../core/autoloader.class.php");
  // load the autoloader manually after suffix expansion for files in 
  // the include path like 'Log.php'
  $al = new core\AutoLoader(false);
  $al->expand(".test.class.php,.interface.php,.php");
  $al->load();

  $fl = new core\util\log\FileLogger("index.php");
  $fl->logge("%",array($al));
  $fl->logge("%",array($fl));

  try {
    $xd = new core\util\xml\XMLDocument("../../conf/xml/core/php-init.xml",
                                        "../../conf/dtd/core/php-init.dtd");
    $fl->logge("%",array($xd));
  } catch(core\exception\xml\XMLNotValidException $xnve) {
    echo "Exception: core\exception\xml\XMLNotValidException catched!<br />";
  }

?>
<html>
  <head>
    <!--<link rel="shortcut icon" href="/image/@confvalue-doxygen-pjname@.ico" type="image/x-icon" />-->
  </head>
  <body>
    Currently just a dummy page to show how to load the core application.
  </body>
</html>
