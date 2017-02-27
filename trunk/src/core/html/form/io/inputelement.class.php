<?php

namespace core\html\form\io;
use core\object\Buildable as Buildable;
use core\util\param\Validator as Validator;
use core\exception\html\form\FormException as FormException;

/**
 * This class abstracts HTML form input elements.
 * @author Marc Bredt
 * @see https://www.w3.org/2010/04/xhtml10-strict.html
 */
abstract class InputElement {

  protected $module   = null;
  protected $fname    = null;
  protected $name     = null;
  protected $groupid  = null;

  protected $row      = null;
  protected $col      = null;
  protected $num      = null;

  protected $type     = null;
  protected $types    = array("textarea","text","checkbox","radio","select",
                              "hidden","file","submit","reset","image",
                              "button","password","range","number");

  /**
   * Set up the HTML form input element's type.
   * @param $type one of the valid W3C defined types for <input ... /> elements
   */
  protected function __construct($module = "core", $fname = "", $name = "unknown",
                                 $groupid = "e", $row = -1, $col = -1, $num = -1, 
                                 $type = "text") {

    global $filelogger;

    $this->module  = $module;
    $this->fname   = $fname;
    $this->groupid = $groupid;

    $this->row     = $row;
    $this->col     = $col;
    $this->num     = $num;

    if(Validator::isa($type,"string") && Validator::in($type,$this->types)) {
      $this->type = $type;
    } else {
      $filelogger->log("%",
        array(new FormException("Invalid element type '".$type."'")),
        "ERR");
      throw(new FormException("Invalid element type '".$type."'"));
    }

  }

  /**
   * Get the name for an input element.
   *
   * Every input element follows a specific structure 
   * <pre>MODULNAME.FORMNAME.ELEMENTGROUP.ROW|NUM[.COL][.x|y]</pre>
   * where ELEMENTGROUP is one of "e" (entries) or "g" (globals),
   * FORMNAME is the name of the form the elements belong to,
   * ROW/COL represent the row and column index for the element,
   * NUM characterizes the enumeration index for global elements and
   * .x/.y are the x and y coordinates for input elements of type image.
   * Using this structure it is always clearly defined what kind of element
   * names are valid on form submission.
   * Additional application/module parameters can be defined elsewhere.
   * The following snippet shows some valid element names in forms.
   * <pre>
   *   foo.f1.e.0.0.x
   *   foo.f1.e.0.0.y
   *   foo.f2.e.0.1
   *   bar.f1.g.0
   *   bar.f1.g.1.x
   *   bar.f1.g.1.y
   * </pre>
   * 
   * @return A form elements default name
   */ 
  protected function getName() {
    return $this->module.".".$this->fname.".".$this->groupid.".".
           (Validator::equals($this->groupid,"g") 
            ? $this->num : $this->row.".".$this->col);
  }

  /**
   * As this function output depends on the specified form element itself
   * this need to be abstract.
   * @see https://www.w3.org/2010/04/xhtml10-strict.html
   */
  abstract protected function build($data = array());
 
}

?>
