<?php

namespace core\html\form\io\element;
use core\html\form\io\InputElement as InputElement;
use core\util\param\Validator as Validator;

class Submit extends InputElement {

  private $title    = null;
  private $value    = null;
  private $disabled = null;
  private $iclasses = null;

  public function __construct($module = "core", $fname = "", $name = "",
                              $groupid = "e", $row = -1, $col = -1, $num = -1,
                              $title = "submit input", $value = "", 
                              $disabled = "", $iclasses = "") {

    parent::__construct($module, $fname, $name, $groupid, $row, $col, $num, "submit");
    $this->title    = $title;
    $this->value    = $value;
    $this->disabled = $disabled;
    $this->iclasses = $iclasses;

  }

  /** 
   * Build a submit element.
   * @param $data data used to set the value.
   * @return XHTML code for this element
   * Data need to be provided as array. The first entry will be used.
   * <pre>
   *   array ( "val" );
   * </pre>
   */
  public function build($data = array()) {
    return "<input class=\"input_submit ".$this->iclasses."\" ".
                  "name=\"".$this->getName()."\" ".
                  "title=\"".$this->title."\" alt=\"".$this->title."\" ".
                  "type=\"".$this->type."\" ".
                  "value=\"".(Validator::isa($data,"array")
                                && !Validator::isempty($data)
                              ? $data[0] : $this->value)."\" ".
                  $this->disabled." />";
  }

}

?>
