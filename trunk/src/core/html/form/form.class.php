<?php

namespace core\html\form;

use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\string\StringUtil as StringUtil;
use core\lang\Localizator as Localizator;
use core\html\form\io\FormElement as FormElement;
use core\layout\Evaluator as Evaluator;

/**
 * This class is used to represent any form.
 * A form configuration loos like
 * <pre>
 *   <form module="core" name="foo" type="grid" mode="horizontal" roles="admin,user">
 *     <element ... />
 *     ...
 *   </form>
 * </pre>
 * @author Marc Bredt
 * @see FormElement
 */
class Form {

  private $module   = null;
  private $name     = null;
  private $type     = null;
  private $size     = null;
  private $mode     = null;
  private $roles    = null;
  private $elements = null;

  /**
   * Initialize a form.
   * @param $xml XML file containing a form configuration for a result set.
   */
  public function __construct($xml = "") {

    global $language, $filelogger;

    $dtd = PATH_DTD."/html/form/form.dtd";
    $x = new XMLDocument($xml,$dtd);

    $this->module  = $x->xpath("string(/form/@module)");
    $this->name    = $x->xpath("string(/form/@name)");
    $this->type    = $x->xpath("string(/form/@type)");
    $this->mode    = $x->xpath("string(/form/@mode)");
    $this->roles   = $x->xpath("string(/form/@roles)");
    $this->entries = array();
    $this->globals = array();

    // entries, add all form elements defined in form.xml 
    $entries = $x->xpath("/form/element[@required='y' and ".
                           "(not(@group) or @group='entries')]",
                         false,true);
    foreach($entries as $e) { 
      $filelogger->log("%",array($e),"DEBUG");
      $this->push($e);
    }

    // globals, add all form elements defined in form.xml 
    $globals = $x->xpath("/form/element[@required='y' and ".
                           "@group='globals']",
                         false,true);
    foreach($globals as $e) { 
      $filelogger->debug("%",array($this));
      $this->push($e);
    }

  }

  /**
   * Store form elements.
   * @param $e element to push
   */
  private function push($e = null) {

    $egroup    = (Validator::equals($e->getAttribute("group"),"globals") 
                  ? $e->getAttribute("group") : "entries");
    $ename     = $e->getAttribute("name");
    $etitle    = Localizator::localize($e->getAttribute("title"));
    $eshow     = $e->getAttribute("show");
    $eedit     = $e->getAttribute("disabled");
    $erequired = $e->getAttribute("required");
    $etype     = $e->getAttribute("type");
    $eregex    = $e->getAttribute("regex");
    $esize     = $e->getAttribute("size");
    $elength   = $e->getAttribute("length");
    $emin      = $e->getAttribute("min");
    $emax      = $e->getAttribute("max");
    $ecols     = $e->getAttribute("cols");
    $erows     = $e->getAttribute("rows");
    $eselected = $e->getAttribute("selected"); // depend on result set/submit
    $echecked  = $e->getAttribute("checked"); // depend on result set/submit
    $evalue    = Localizator::localize(Evaluator::get($e->getAttribute("value")));
    $etext     = Localizator::localize($e->getAttribute("text"));
    $esource   = $e->getAttribute("source");
    $emultiple = $e->getAttribute("multiple");
    $ebtype    = $e->getAttribute("btype");
    $etclasses = $e->getAttribute("tclasses");
    $eiclasses = $e->getAttribute("iclasses");
    $edefines  = $e->getAttribute("defines");
    $efilters  = $e->getAttribute("filters");
    $eroles    = $e->getAttribute("roles");

    switch($egroup) {

      case "entries" : 
        array_push($this->entries,
                   new FormElement($this->module,$this->getName(),$egroup,
                                   $ename,$etitle,$eshow,$eedit,$erequired,
                                   $etype,$eregex,$elength,$emin,$emax,
                                   $eselected,$echecked,$evalue,$esize,$ecols,
                                   $erows,$esource,$emultiple,$ebtype,$etext,
                                   $etclasses,$eiclasses,$edefines,$efilters,
                                   $eroles));
        break;
 
      case "globals" : 
        array_push($this->globals,
                   new FormElement($this->module,$this->getName(),$egroup,
                                   $ename,$etitle,$eshow,$eedit,$erequired,
                                   $etype,$eregex,$elength,$emin,$emax,
                                   $eselected,$echecked,$evalue,$esize,$ecols,
                                   $erows,$esource,$emultiple,$ebtype,$etext,
                                   $etclasses,$eiclasses,$edefines,$efilters,
                                   $eroles));
        break;
        
      default : break;
     
    }   

  }

  /**
   * Get information on this form.
   * @return string containing information on this form
   */
  public function __toString() {
    return get_class($this)."-".spl_object_hash($this)."=( ".
             "module=".$this->module.", ".
             "name=".$this->name.", ".
             "type=".$this->type.", ".
             "size=".$this->size.", ".
             "mode=".$this->mode.", ".
             "roles=".$this->roles.", ".
             "elements=[ ".StringUtil::get_object_string($this->elements)." ]".
           " )";
  }

  public function getModule() {
    return $this->module;
  }

  public function getName() {
    return $this->name;
  }

  public function getType() {
    return $this->type;
  }

  public function getMode() {
    return $this->mode;
  }

  public function getRoles() {
    return $this->roles;
  }

  public function getElements($group = "entries") {
    switch($group) {
      case "entries" : return $this->entries;
      case "globals" : return $this->globals;
      default : break;
    }
  }

}

?>
