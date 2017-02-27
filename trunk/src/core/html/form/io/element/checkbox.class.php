<?php

namespace core\html\form\io\element;
use core\html\form\io\InputElement as InputElement;
use core\util\param\Validator as Validator;

class Checkbox extends InputElement {

  private $title    = null;
  private $value    = null;
  private $checked  = null;
  private $disabled = null;
  private $iclasses = null;

  public function __construct($module = "core", $fname = "", $name = "",
                              $groupid = "e", $row = -1, $col = -1, $num = -1,
                              $title = "checkbox input", $value = "", 
                              $checked = "", $disabled = "", $iclasses = "") {

    parent::__construct($module, $fname, $name, $groupid, $row, $col, $num, "checkbox");
    $this->title    = $title;
    $this->value    = $value;
    $this->checked  = $checked;
    $this->disabled = $disabled;
    $this->iclasses = $iclasses;

  }

  /**
   * Build a checkbox.
   * @param $data data used to set the value.
   * @return XHTML code for a checkbox
   * Data need to be provided as array. The first entry will be used.
   * <pre>
   *   array( array( "y", "One"), array( "n", "Two" ) );
   * </pre>
   */
  public function build($data = array()) {
    $checkboxes = "";
    foreach($data as $c) {
      $checkboxes .= "\n<input class=\"input_checkbox ".$this->iclasses."\" ".
                       "name=\"".$this->getName()."\" ".
                       "title=\"".$this->title."\" alt=\"".$this->title."\" ".
                       "type=\"".$this->type."\" ".$this->checked." ".
                       (Validator::isa($c,"array") && !Validator::isempty($c) 
                          && count($c)>0
                        ? "value=\"".$c[0]."\"": "value=\"".$this->value."\" ").
                       $this->disabled."/>".(isset($c[1]) ? $c[1] : "")."<br/>";
    }
    return $checkboxes;
  }

}

?>
