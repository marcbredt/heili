<?php

namespace core\module;
use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;
use core\object\Validatable as Validatable;
use core\object\Version as Version;
use core\util\map\BooleanMap as BooleanMap;
use core\module\ModuleLicense as ModuleLicense;
use core\module\ModuleSignature as ModuleSignature;
use core\exception\module\ModuleException as ModuleException;

/**
 * This class respresents an module loaded.
 * @author Marc Bredt
 * @see ModuleMap
 */
class Module implements Validatable {

  private $mpath     = "";

  private $mconf     = "";

  private $mdtd      = "";

  private $name      = "";

  private $type      = "";

  private $version   = null;

  private $license   = null;

  private $signature = null;

  /**
   * Validation state for this module. Defaults to true to combine it with
   * verification results in Module::validate().
   */ 
  private $valid = true;

  /**
   * Contains flags on module sections loaded.
   */
  private $loaded = null;

  /*
   * Setup a module.
   * @param $module the name of the module and the name of the sub directory in
   *                PATH_MODULES
   */
  public function __construct($module = "") {

    if(Validator::isa($module,"string")) $this->name = $module;

    if(Validator::equals($this->name,"core")) {
      $this->mpath = PATH_CONF;
      $this->mconf = PATH_CONF."/module.xml";

    } else {
      $this->mpath = constant("MOD_".strtoupper($module)."_PATH");
      $this->mconf = constant("MOD_".strtoupper($module)."_PATH_CONF").
                       DIRECTORY_SEPARATOR."module.xml";

    }

    $this->mdtd  = PATH_DTD.DIRECTORY_SEPARATOR."module".
                            DIRECTORY_SEPARATOR."module.dtd";
    $this->valid = $this->valid && XMLDocument::is_valid($this->mconf,$this->mdtd);

    // get type and version
    if(Validator::equals($this->valid,true)) {
      $mx = new XMLDocument($this->mconf,$this->mdtd,false);
      $this->type = $mx->xpath("string(//module[@name=\"".$this->name."\"]/@type)");
      $this->version = new Version($mx->xpath("string(//module[@name=\"".$this->name.
                                              "\"]/@version)"));
    }

    // create a map to be able set flags for module configuration sections loaded
    $this->loaded = new BooleanMap();

  }

  /** 
   * Check if a module is valid which means it has to be present, its module 
   * configuration need to follow the core module DTD and its signature need
   * to verified.
   *
   * A module signature can be created the following way:
   * <pre>
   *  $ find -type f -not -wholename "./signature.xml" -exec md5sum {} + | \
   *    cut -d' ' -f1 | sort | md5sum | cut -d' ' -f1 | \
   *    gpg --sign-with 55CEBC6B --passphrase "****" --detach-sign --armor -
   * </pre>
   *
   * To verify a signature use the following commands:
   * <pre>
   *   $ xmllint --xpath "string(/signature)" conf/xml/signature.xml | \
   *     sed -e "s#^[\t ]*\|[\t ]*\$##g" > /tmp/module.sig
   *   $ find -type f -not -wholename "./signature.xml" -exec md5sum {} + | \ 
   *     cut -d' ' -f1 | sort | md5sum | cut -d' ' -f1 | \ 
   *     gpg --verify /tmp/module.sig -
   * </pre>
   *
   * This function is inherited from Validatable.
   *
   * @param $module the name of the module and the name of the sub directory in
   *                PATH_MODULES
   * @see Validate
   */
  public function validate() {

    global $filelogger;

    // setup the module license
    $this->license = new ModuleLicense($this->name);

    // setup a module signature
    $this->signature = new ModuleSignature($this->name);

    // validate the module
    $this->valid = $this->valid 
                   && is_dir($this->mpath)
                   && $this->license->validate()
                   && $this->signature->validate();


    // only log an exception if the module is invalid, this module will then 
    // not be used invoking a module dependency exception on evaluating the 
    // module tree
    if(Validator::equals($this->valid,false)) {
      $filelogger->log("%, module=%, dir=%, dir?=%, ".
                         "mfile=%, mfile?=%, mtfile=%, mvalid?=%, license=%, ".
                         "signature=%",
                       array(
                         new ModuleException("'".$this->name."', ".
                                             "'".$this->signature->get_hash()."'",0),
                         $this->name, $this->mpath, is_dir($this->mpath), 
                         $this->mconf, file_exists($this->mconf), 
                         mime_content_type($this->mconf), $this->valid, 
                         $this->license, $this->signature
                       ),
                       "ERR");
      throw(new ModuleException("'".$this->name."', ".
                                "'".$this->signature->get_hash()."'",0));
    }

    return $this->valid;

  }

  public function get_name() {
    return $this->name;
  }

  public function get_mconf() {
    return $this->mconf;
  }

  public function get_mdtd() {
    return $this->mdtd;
  }

  public function get_version() {
    return $this->version;
  }

  public function get_loaded($key = "") {
    return ($this->loaded->has($key) ? $this->loaded->get($key) : false);
  }

  public function set_loaded($key = "", $loaded = true) {
    global $filelogger;
    $this->loaded->set($key,$loaded);
    $filelogger->log("section (%,%) in % set to '%'",
      array($key, $loaded, $this->loaded, $this->loaded->get($key)),"DEBUG");
  }

  /**
   * Dump some module information.
   * @return a string representing this module
   */
  public function __toString() {
    return get_class($this).spl_object_hash($this)." { ".
           "name=".$this->name.", ".
           "type=".$this->type.", ".
           "version=".$this->version.", ".
           "license=".$this->license.", ".
           "signature=".$this->signature.", ".
           "valid=".$this->valid." ".
           "} ";
  }

}

?>
