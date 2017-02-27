<?php

namespace core\module;
use \DOMXpath as DOMXpath;
use core\object\Loadable as Loadable;
use core\object\Version as Version;
use core\module\ModuleMap as ModuleMap;
use core\module\ModuleTree as ModuleTree;
use core\util\file\File as File;
use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;
use core\util\xml\XMLConfig as XMLConfig;
use core\util\xml\XMLDocument as XMLDocument;
use core\system\Command as Command;
use core\system\CommandExecutor as CommandExecutor;
use core\exception\module\ModuleException as ModuleException;

/**
 * This class is used to provide access to available module loaded.
 * @author Marc Bredt
 */
class ModuleLoader implements Loadable {

  /**
   * Load all active modules available.
   * @return a ModuleMap which is actually an array of Modules
   */
  public static function load() {
    // load section 'config' for module core to be able to access necessary constants
    self::load_section();
    // load secrions for all other modules as well
    $mmap = self::build_module_map();
    return $mmap;
  }

  /**
   * Determine potential additional modules in the main module directory.
   * @return array containing all module (directory) names present
   */
  private static function get_modules() {

    global $filelogger;

    $mods = array();

    $command = new Command("find ".PATH_MODULES." -mindepth 1 -maxdepth 1 -type d");
    $ce = new CommandExecutor();
    $ce->execute($command,true);

    foreach($ce->get_output() as $mp) {
      $m = trim(preg_replace("|^".PATH_MODULES."[".DIRECTORY_SEPARATOR."]*|","",$mp)); 
      $mods[] = $m;
    }

    $filelogger->log("found additional modules=%",
                     array($mods),"INFO");
    return $mods;

  }

  /**
   * Check dependencies of (valid) modules found.
   * @param $mmap module map containing valid modules found
   * @return void
   */
  private static function check_dependencies($mmap = null) {

    global $filelogger;
    $filelogger->log("#mmap=%, mmap=%",array(count($mmap),$mmap),"INFO");

    foreach($mmap->get_map() as $m) {

      $x = new XMLDocument($m->get_mconf(),$m->get_mdtd(),false);
      $deps = $x->xpath("//dependencies/dependency[@active=\"y\"]",false,true);

      foreach($deps as $d) {

        $dmodule = $d->getAttribute("module");
        $dversion = $d->getAttribute("version");

        // get the dependency comparator and version
        preg_match("/[0-9]/", $dversion, $matches, PREG_OFFSET_CAPTURE);
        $filelogger->log("matches=%",
                        array($matches),
                        "DEBUG");
        if(Validator::isempty($matches)) { 
          $dc = "=="; $version = "";
        } else { 
          $dc = ($matches[0][1]>0 ? substr($dversion,0,$matches[0][1]) : "=="); 
          $version = substr($dversion,$matches[0][1],strlen($dversion));
        }
        $dv = new Version($version);

        // check if the dependency module exists in the module map passed and if
        // the version number fits the requirements
        if(!$mmap->has($dmodule)
           || Validator::isa($mmap->get($dmodule)->get_version(),"null")
           || !$mmap->get($dmodule)->get_version()->validate($dc,$dv)) {

          $filelogger->log("module '%' depends on '%'%% ".
                             "(m=%, mv=%, mvv=%)",
                           array(
                                 $m->get_name(),$dmodule,$dc,$dv,
                                 $m,$mmap->get($dmodule)->get_version(),
                                 $mmap->get($dmodule)->get_version()->validate($dc,$dv)
                                ),
                           "ERR");
          throw(new ModuleException("dependency not available",1));

        } else {
 
          $filelogger->log("dependency (%%%) for module '%' ".
                             "found",
                           array($dmodule,$dc,$dv,$m->get_name()),"INFO");

        }
   

      } // for deps

    } // for map

  }

  /**
   * Load module config sections for each valid module found/defined. Loading
   * is done section wise to allow merging sections of different module 
   * configurations.
   * @param $mmap module map containing valid modules
   * @return void
   */ 
  private static function load_modules($mmap = null) {

    global $filelogger;

    // load config section
    foreach($mmap->get_map() as $m) {
      $filelogger->log("section 'config' for module '%' loaded?='%'", 
                       array($m->get_name(),$m->get_loaded("config")),"INFO");
      if(!$m->get_loaded("config")) self::load_section("config",$m->get_mconf());
      $m->set_loaded("config",true);
    }
    

    // load includes section
    $xmldummy = "<includes />";
    $filelogger->log("XXXXX-0 %",array($xmldummy),"DEBUG");

    $xi = new XMLDocument(null,null,false);
    $filelogger->log("XXXXX-1 %",array($xi),"DEBUG");

    $xi->create_doc($xmldummy);
    $filelogger->log("XXXXX-2 %",array($xi),"DEBUG");

    // concat all module includes sorted for dependency reasons
    foreach($mmap->get_map() as $m) {

      if(!$m->get_loaded("includes")) {
        $tmp = $xi;

        // module includes directive
        $mid = self::load_section("includes",$m->get_mconf());
        $filelogger->log("XXXXX-3 mid = %", array($mid), "DEBUG");

        // adjust node values with correct pathes
        $mid->adjust("//includes/*/text()", "nodeValue",
                     (Validator::equals($m->get_name(),"core")
                      ? constant("PATH_INCLUDE").DIRECTORY_SEPARATOR
                      : constant("MOD_".strtoupper($m->get_name())."_PATH_INCLUDE").
                          DIRECTORY_SEPARATOR));

        // merging module includes 
        $xi->concat($mid->get_doc(),"//includes/*","//includes",true,"order");
        $filelogger->log("concated documents %,% = %",
                         array($tmp->get_doc()->saveXML(),
                               $mid->get_doc()->saveXML(),
                               $xi->get_doc()->saveXML()),"DEBUG");
      }

      $m->set_loaded("includes",true);

    }
    
    // then load those files ordered
    foreach($xi->xpath("//includes/*[@active=\"y\"]/text()",false,true) as $n) {
      $filelogger->log("node=%",array($n),"DEBUG");
      $filelogger->log("loading include file '%'",array($n->nodeValue),"INFO");
      require_once($n->nodeValue);
    }


    // TODO: register actions

    // TODO: register superglobals

  }

  /**
   * Load a module configuration section. Defaults to the core config section
   * which contains e.g. the core constants.
   * @param $ms module section in module configuration
   * @param $mc module configuration file
   * @return the desired element for the section, otherwise null
   */
  private static function load_section($ms = "config", $mc = "../conf/module.xml") {

    global $filelogger;

    $md = "../conf/dtd/module/module.dtd";
    $filelogger->log("##### ms=%, mc=%, md=%",array($ms,$mc,$md),"INFO");
    $xd = new XMLDocument($mc,$md);
    $module = $xd->xpath("string(//module/@name)");
    
    switch($ms) {
     
      case "config" :
          $filelogger->log("##### ms=config",array(),"DEBUG");
          $xq = array();
          foreach($xd->xpath("//module/config/groups/group",false,true) as $n)
            array_push($xq, "//module/config/groups/group[@name=\"".
                               $n->getAttribute("name")."\"]/*");
          $xc = new XMLConfig($mc,$md);
          $xc->load($xq,$module);
          return null;
        break;

      // return an array containing blacklisted module names
      case "blacklist" : 
          $filelogger->log("##### ms=blacklist",array(),"DEBUG");
          return explode(",",$xd->xpath("string(//module/dependencies/@blacklist)"));
        break;

      // return the includes to build a module merged version regarding "order"
      case "includes" : 
          $filelogger->log("##### ms=includes",array(),"DEBUG");
          return $xd->xpath("//module/includes",true);
        break;

      default : break;
 
    }

  }

  /**
   * Build the module map containing all valid modules following the module
   * dependency tree.
   * @return ModuleMap
   */
  private static function build_module_map() {

    global $filelogger;

    // get potential modules and remove all blacklisted modules
    $modules = array_merge(array("core"),self::get_modules());
    $blacklist = self::load_section("blacklist"); 
    $filelogger->log("blacklist=%",array($blacklist));
    foreach($blacklist as $bm) { 
      if($mix=array_search($bm,$modules)) unset($modules[$mix]); }
    $filelogger->log("modules=%",array($modules));

    // setup the module map
    $mmap = new ModuleMap();
    foreach($modules as $mname) {
      
      $m = new Module($mname);
      if(Validator::equals($mname,"core")) {
        $filelogger->log("'%' section 'config' loaded",array($mname),"DEBUG");
        $m->set_loaded("config",true);
      }

      if(Validator::equals(boolval(GPG_VERIFY),true) && $m->validate()) { 
        $as = $mmap->add($mname,$m);
        $filelogger->log("adding validated module=% (%)",array($m,$as), "INFO");
  
      } else if(Validator::equals(boolval(GPG_VERIFY),false)) { 
        $as = $mmap->add($mname,$m);
        $filelogger->log("adding module=% (%)",array($m,$as), "INFO");

      } else {
        $filelogger->log("skipping invalid module=%",array($m), "ERR");

      }

    }

    // check dependencies
    self::check_dependencies($mmap);

    // load module's stuff (config,masks,actions,...)
    self::load_modules($mmap);

    return $mmap;

  }

}

?>
