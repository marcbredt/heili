<?php

  namespace app;
  use core\autoloader\AutoLoader as AutoLoader;
  use core\util\log\FileLogger as FileLogger;
  use core\lang\Language as Language;
  use core\util\xml\XMLDocument as XMLDocument;
  use core\db\data\DataGetter as DataGetter;
  use core\html\form\Form as Form;
  use core\html\form\FormBuilder as FormBuilder;


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
  <title>A title</title>
</head>
<body>
<?php 

  // autoloader
  require_once("../../../core/autoloader/autoloader.class.php");
  require_once("../../../core/autoloader/devisor.class.php");
  $autoloader = new AutoLoader(false, "../../..");
  $autoloader->expand(".class.php,.interface.php,.include.php,.php");
  $autoloader->load();

  // 255 217 15

  // some internal const, for DataGetter and usage in this file
  // will be set on app initialization
  define("PATH_CONF","../../../conf");
  define("PATH_DTD","../../../conf/dtd");

  // logger
  $filelogger = new FileLogger("form.php","./form.log");
  $filelogger->set_level("DEBUG");

  // language, just for value/title localizator
  $language = new Language("en",PATH_CONF."/lang",
                                 PATH_DTD."/lang",
                                PATH_CONF."/lang/languages.xml",
                                 PATH_DTD."/lang/languages.dtd");
  $language->load();
  $filelogger->log("language = [ % ]", array($language));

  // get data
  $dg = new DataGetter("table1");
  $data = $dg->get();

  // build the form
  $f = new Form(PATH_CONF."/html/form/form.xml");
  $fb = new FormBuilder($f);
  echo $fb->build($data);

?>
</body>
</html>
