<?php

namespace core\html\form\io\element;
use core\html\form\io\InputElement as InputElement;
use core\util\param\Validator as Validator;

class Slider extends InputElement {

  private $title    = null;
  private $size     = null;
  private $length   = null;
  private $value    = null;
  private $disabled = null;
  private $iclasses = null;

  public function __construct($module = "core", $fname = "", $name = "unknown",
                              $groupid = "e", $row = -1, $col = -1, $num = -1,
                              $title = "slider input", $min = null, $max = null, 
                              $value = null, $disabled = "", $iclasses = "") {

    parent::__construct($module, $fname, $name, $groupid, $row, $col, $num, "range");
    $this->title    = $title;
    $this->min      = $min;
    $this->max      = $max;
    $this->value    = $value;
    $this->disabled = $disabled;
    $this->iclasses = $iclasses;

  }

  /** 
   * Build a slider element.
   * @param $data data used to set the value.
   * @return XHTML code for this element
   * Data need to be provided as array. The first entry will be used.
   * <pre>
   *   array ( "min" => 0, "max" => 10, "step" => 1, "value" => "7", ...);
   * </pre>
   */
  public function build($data = array()) {

    // override element data with database values
    if(Validator::isa($data,"array") && count($data)>0 
       && Validator::in($this->name,$data)) 
      $this->value = array($data[$this->name]);

    // build the element
    return "<input ".
                  "class=\"input_range ".$this->iclasses."\" ".
                  "name=\"".$this->getName()."\" ".
                  "title=\"".$this->title."\" alt=\"".$this->title."\" ".
                  "value=\"".$this->value."\" type=\"".$this->type."\" ".
                  $this->disabled."/>";
  }

}

?>
