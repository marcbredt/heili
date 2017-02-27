<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
  <title>A title</title>
</head>
<body>
<?php 
  require("./foo/validator.class.php");
  require("./foo/stringutil.class.php");
  require("./foo/file.class.php");
  require("./foo/filelogger.class.php");

  require("./foo/language.class.php");
  require("./foo/languageexception.class.php");
  
  require("./foo/xmldocument.class.php");
  require("./foo/xmlmergeexception.class.php");
  require("./foo/xmlnotvalidexception.class.php");
  require("./foo/xmlnovaliddtdexception.class.php");
  require("./foo/unresolvedxpathexception.class.php");
  require("./foo/invalidxpathexpressionexception.class.php");

  require("./foo/connector.class.php");
  require("./foo/connection.class.php");
  require("./foo/statement.class.php");
  require("./foo/statementbatch.class.php");
  require("./foo/databaseexception.class.php");
  require("./foo/datapresenter.class.php");
  require("./foo/executor.class.php");
  require("./foo/dataset.class.php");
  require("./foo/mysqlconnector.class.php");
  require("./foo/datagetter.class.php");

  // newly created classes
  require("./localizable.interface.php");
  require("./localizator.class.php");
  require("./buildable.interface.php");
  require("./form.class.php");
  require("./formelement.class.php");
  require("./formbuilder.class.php");

  // some internal const
 
  const PATH_CONF = ".";
  const PATH_DTD  = ".";

  // 255 217 15

  $filelogger = new core\util\log\FileLogger("resultset.php","./form.log");
  $filelogger->set_level("DEBUG");

  $dg = new core\db\data\DataGetter("table","./foo/statements.xml");
  $data = $dg->get();
  print_r($data);

  //$f = new core\html\form\Form("form.xml",$data);
  //$fb = new core\html\form\FormBuilder($f);
  //echo $fb->build();

?>
</body>
</html>
