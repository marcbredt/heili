<?php

namespace core\module;
use core\object\License as License;
use core\object\Validatable as Validatable;
use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;

class ModuleLicense extends License implements Validatable {

  private $module = "";

  /** 
   * Setup the module's license file.
   * @param $module module name
   */ 
  public function __construct($module = "") {
    $this->module = $module;
    if(Validator::equals($this->module,"core")) {
      $this->file = PATH_CONF.DIRECTORY_SEPARATOR."license.xml";
    } else {
      $this->file = PATH_MODULES.DIRECTORY_SEPARATOR.$this->module.
                                 DIRECTORY_SEPARATOR."license.xml";
    }
  }

  /** 
   * Inherited from Validatable. Validates the license file and the license
   * provided through this file.
   * @return true if the license is valid, otherwise false
   * @see Validatable
   */
  public function validate() {
    global $filelogger;
    $ld = PATH_DTD.DIRECTORY_SEPARATOR."module".DIRECTORY_SEPARATOR."license.dtd";
    $fv = XMLDocument::is_valid($this->file,$ld);
    $tv = $this->verify();
    $this->valid = $fv && $tv;
    $filelogger->log("license (fv=%, tv=%, this=%)",
                     array($fv,$tv,$this),"DEBUG");
    return $this->valid;
  }

  /**
   * Set (and verify) a module license.
   * @return true if verification was successfull, otherwise false
   */
  public function verify() {

    global $filelogger;

    // get the license values
    $ld = PATH_DTD.DIRECTORY_SEPARATOR."module".DIRECTORY_SEPARATOR."license.dtd";
    $xs = new XMLDocument($this->file,$ld,false); // already validated
    $this->type = $xs->xpath("string(/license/@type)");
    $this->text = preg_replace("/(\r|\t| )*\n/","\n",
                    preg_replace("/\n(\r|\t| )*/","\n",
                      $xs->xpath("string(/license/text())")));
    $filelogger->log("type=%, text=%",
                     array($this->type,$this->text),
                     "DEBUG");

    // currently a dummy implementation, as the license type is currently
    // not evaluated
    return true;

  }

  /**
   * Dump some information.
   * @return string describing this object
   */
  public function __toString() {
    return get_class($this).spl_object_hash($this)."=( ".
           "module=".$this->module.", ".
           parent::__toString().
           ") ";
  }

}

?>
