<?php

namespace core\html\form\io\element;
use core\html\form\io\InputElement as InputElement;
use core\util\param\Validator as Validator;

class Button extends InputElement {

  private $title    = null;
  private $btype    = null;
  private $btypes   = array("button","submit","reset");
  private $value    = null;
  private $disabled = null;
  private $iclasses = null;

  public function __construct($module = "core", $fname = "", $name = "",
                              $groupid = "e", $row = -1, $col = -1, $num = -1,
                              $title = "button input", $btype = "submit", 
                              $value = "", $text = "submit", $disabled = "",
                              $iclasses = "") {

    parent::__construct($module, $fname, $name, $groupid, $row, $col, $num, "button");
    $this->title    = $title;
    $this->btype    = $btype;
    $this->value    = $value;
    $this->text     = $text;
    $this->disabled = $disabled;
    $this->iclasses = $iclasses;

  }

  /** 
   * Build a button element.
   * @param $data data used to set the value.
   * @return XHTML code for this element
   * Data need to be provided as array. The first entry will be used.
   * <pre>
   *   array ( "button-type" => array ( "button-value", "button-text" ) );
   *   array ( "button" => array ( "1234567890", "Button text" ) );
   *   array ( "submit" => array ( "1234567890", "Submit button text" ) );
   *   array ( "reset" => array ( "1234567890", "Reset button text" ) );
   * </pre>
   */ 
  public function build($data = array()) {

    // build the button from (first) data passed
    foreach($data as $key => $values) {
      return "<".$this->type." ".
               "class=\"input_button ".$this->iclasses."\" ".
               "name=\"".$this->getName()."\" ".
               "title=\"".$this->title."\" ".
               (!Validator::isempty($data) && Validator::isa($data,"array")
                && Validator::in($key,$this->btypes) 
                  ? "type=\"".$key."\" " : "type=\"button\" ").
               (!Validator::isempty($values) && Validator::isa($values,"array") 
                && count($values)>=1 ? "value=\"".$values[0]."\" " : "").
               $this->disabled.">".
             (!Validator::isempty($values) && Validator::isa($values,"array") 
              && count($values)>=2 ? $values[1] : "").
             "</".$this->type.">";
    }

    // otherwise build from values set upon creation
    return "<".$this->type." ".
             "class=\"input_button ".$this->iclasses."\" ".
             "name=\"".$this->getName()."\" ".
             "title=\"".$this->title."\" type=\"".$this->btype."\" ".
             "value=\"".$this->value."\" ".$this->disabled.">".
           $this->text."</".$this->type.">";
  }

}

?>
