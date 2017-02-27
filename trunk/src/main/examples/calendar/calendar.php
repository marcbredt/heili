<?php

namespace examples\calendar;

use core\AutoLoader as AutoLoader;
use core\util\log\FileLogger as FileLogger;
use core\util\xml\XMLDocument as XMLDocument;

use modules\calendar\Calendar as Calendar;
use modules\calendar\exception\CalendarException as CalendarException;

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

// create a gregorian calendar instance
$lang = "de";
//$xd = new XMLDocument("../../conf/modules/calendar/xml/gregorian-".$lang.".xml",
//                      "../../conf/modules/calendar/dtd/gregorian-".$lang.".dtd",
//                      true);
//echo "XMLDocument (original):<br>".htmlentities($xd);
//$xda = new XMLDocument("../../conf/modules/calendar/xml/gregorian-".$lang.
//                         "_adjust.xml",
//                       "../../conf/modules/calendar/dtd/gregorian-".$lang.".dtd",
//                       false);
//echo "<br><br>XMLDocument (adjusted):<br>".htmlentities($xda);
//$xd->merge($xda);
//echo "<br><br>XMLDocument (merged):<br>".htmlentities($xd);
//$c = new Calendar($xd);
//echo "<br><br>Calendar:<br>".$c;

// create a darian calendar instance

?>
