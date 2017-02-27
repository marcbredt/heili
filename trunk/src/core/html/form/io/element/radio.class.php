<?php

namespace core\html\form\io\element;
use core\html\form\io\InputElement as InputElement;
use core\util\param\Validator as Validator;

class Radio extends InputElement {

  private $title    = null;
  private $value    = null;
  private $checked  = null;
  private $disabled = null;
  private $iclasses = null;

  public function __construct($module = "core", $fname = "", $name = "",
                              $groupid = "e", $row = -1, $col = -1, $num = -1,
                              $title = "radio input", $value="",
                              $checked = "", $disabled = "", $iclasses = "") {

    parent::__construct($module, $fname, $name, $groupid, $row, $col, $num, "radio");
    $this->title    = $title;
    $this->value    = $value;
    $this->disabled = $checked;
    $this->iclasses = $iclasses;

  }

  /** 
   * Build a radio element.
   * @param $data data used to set the value.
   * @return XHTML code for this element
   * Data need to be provided as array. The first entry will be used.
   * If no valid data is provided the value set in the form configuration
   * will be used.
   * <pre>
   *   array ( 
   *     array( "y" , "Choice #1" ), 
   *     array( "n" , "Choice #2" ) 
   *   );
   * </pre>
   */
  public function build($data = array()) {
    $radios = "";
    foreach($data as $r) {
      $radios .= "\n<input class=\"input_radio ".$this->iclasses."\" ".
                       "name=\"".$this->getName()."\" ".
                       "title=\"".$this->title."\" alt=\"".$this->title."\" ".
                       "type=\"".$this->type."\" ".$this->checked." ".
                       (Validator::isa($r,"array") && !Validator::isempty($r) 
                        ? "value=\"".$r[0]."\" " : "value=\"".$this->value."\" ").
                       $this->disabled."/>".(isset($r[1]) ? $r[1] : "")."<br/>";
    }
    return $radios;  
  }

}

?>
