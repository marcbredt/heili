<?php

namespace core\html\form\io\element;
use core\html\form\io\InputElement as InputElement;
use core\util\param\Validator as Validator;

class Text extends InputElement {

  private $title    = null;
  private $size     = null;
  private $length   = null;
  private $value    = null;
  private $disabled = null;
  private $iclasses = null;

  public function __construct($module = "core", $fname = "", $name = "unknown",
                              $groupid = "e", $row = -1, $col = -1, $num = -1,
                              $title = "text input", $size = 5, $length = 254, 
                              $value = "", $disabled = "", $iclasses = "") {

    parent::__construct($module, $fname, $name, $groupid, $row, $col, $num, "text");
    $this->title    = $title;
    $this->size     = $size;
    $this->length   = $length;
    $this->value    = $value;
    $this->disabled = $disabled;
    $this->iclasses = $iclasses;

  }

  /** 
   * Build a text element.
   * @param $data data used to set the value.
   * @return XHTML code for this element
   * Data need to be provided as array. The first entry will be used.
   * <pre>
   *   array ( "val" );
   * </pre>
   */
  public function build($data = array()) {

    // override element data with database values
    if(Validator::isa($data,"array") && count($data)>0 
       && Validator::in($this->name,$data)) 
      $this->value = array($data[$this->name]);

    // build the element
    return "<input ".
                  "class=\"input_text ".$this->iclasses."\" ".
                  "name=\"".$this->getName()."\" ".
                  "title=\"".$this->title."\" alt=\"".$this->title."\" ".
                  "size=\"".$this->size."\" maxlength=\"".$this->length."\" ".
                  "value=\"".$this->value."\" type=\"".$this->type."\" ".
                  $this->disabled."/>";
  }

}

?>
