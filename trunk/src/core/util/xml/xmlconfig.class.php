<?php

namespace core\util\xml;
use core\object\Config as Config;
use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\string\StringUtil as StringUtil;

/**
 * This class is used to provide constants defined at xpath queries to
 * the application via "define" function. 
 * @see define
 * @author Marc Bredt
 */
class XMLConfig extends Config {

  /**
   * Setup an XML configuration.
   */
  public function __construct($xml = "", $dtd = "") {
    $this->conf = new XMLDocument($xml,$dtd,true);  
  }

  /**
   * Load each constant bound to a query in $queries.
   * @param $queries array of strings representing xpath queries to access
   *                 constants to be set up via define
   * @param $module module name the key belongs to, MOD_<MODNAME>_ will be
   *                prepended to the key for any module config element to
   *                avoid conflicts with key value pairs already set
   * @return true upon success, otherwise false
   */
  public function load($queries = array(), $module = "core") {

    if(!Validator::isa($queries,"array")) return false;

    global $filelogger;

    $filelogger->log("module (%) queries = [ % ]",array($module,$queries),"DEBUG");

    // predefine some module path variables
    if(!Validator::equals($module,"core")) {
      define("MOD_".strtoupper($module)."_PATH",PATH_MODULES.DIRECTORY_SEPARATOR.
             $module);
      define("MOD_".strtoupper($module)."_PATH_CLASS",PATH_MODULES.DIRECTORY_SEPARATOR.
             $module.DIRECTORY_SEPARATOR."clss");
      define("MOD_".strtoupper($module)."_PATH_INCLUDE",PATH_MODULES.DIRECTORY_SEPARATOR.
             $module.DIRECTORY_SEPARATOR."incs");
      define("MOD_".strtoupper($module)."_PATH_MASK",PATH_MODULES.DIRECTORY_SEPARATOR.
             $module.DIRECTORY_SEPARATOR."mask");
      define("MOD_".strtoupper($module)."_PATH_CONF",PATH_MODULES.DIRECTORY_SEPARATOR.
             $module.DIRECTORY_SEPARATOR."conf");
    }

    // load config values for 
    foreach($queries as $q) {

      if(!Validator::isa($q,"string")) return false;

      $xd = $this->conf->xpath($q,true);
      $type = $this->conf->xpath("string(".$q."/../@type)");
      $prefix = $this->conf->xpath("string(".$q."/../@prefix)");

      foreach($xd->get_doc()->childNodes[0]->childNodes as $n) {

        $key = ((Validator::isa($module,"string") && !Validator::isempty($module)
                && !Validator::equals($module,"core"))
                ? "MOD_".strtoupper($module)."_" : "").$prefix.$n->getAttribute("key");
        $value = $n->getAttribute("value");

        switch($type) {

          case "const": 
              if(!defined($key)) {
                $filelogger->log("const = [ key=%, value=%, query=% ]", 
                                 array($key,$value,$q),"DEBUG");
                define($key,$value);
              } else {
                $filelogger->log("const = [ key=%, value=%, query=% ] already defined", 
                                 array($key,$value,$q),"WARNING");
              }
            break;

          case "ini": 
              $filelogger->log("ini = [ key=%, ".
                                 "value=%, query=% }", array($key,$value,$q) );
              ini_set($key,$value);
            break;
       
          default: break;

        }     
      }
    }  

    return true;

  }

}

?>
