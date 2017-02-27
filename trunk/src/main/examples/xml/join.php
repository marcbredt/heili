<?php

namespace examples\xml;

use core\AutoLoader as AutoLoader;
use core\util\log\FileLogger as FileLogger;
use core\util\xml\XMLDocument as XMLDocument;

// load an autoloader and a logger, watch out for the relative pathes
// adjust the path prefix during test phase where PHPUnit processes uncovered
// files as well
$pprefix = "../..";
if(!strncmp(dirname(__FILE__),getcwd(),strlen(getcwd()))) $pprefix = "src";
require_once($pprefix."/core/autoloader.class.php");

$al = new AutoLoader(false,$pprefix."/");
$al->expand(".test.class.php,.class.php,.interface.php,.php");
$al->load();

// create a filelogger for this example
$fl = new FileLogger();
$fl->logge("%",array($al));
$fl->logge("%",array($fl));


// create two documents to join first
$x1 = new XMLDocument(dirname(__FILE__)."/j1.xml",null,false);
echo "XMLDocument I (view source)<br>".$x1."<br><br>";
$x2 = new XMLDocument(dirname(__FILE__)."/j2.xml",null,false);
echo "XMLDocument II (view source)<br>".$x2."<br><br>";

// join them
$x1->merge($x2);
echo "XMLDocument I (view source)<br>".$x1."<br><br>";


?>
