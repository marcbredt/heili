<?php

namespace core\html\form\io\element;
use core\html\form\io\InputElement as InputElement;
use core\util\param\Validator as Validator;

class Hidden extends InputElement {

  private $value    = null;

  public function __construct($module = "core", $fname = "", $name = "",
                              $groupid = "e", $row = -1, $col = -1, $num = -1, 
                              $value = "") {

    parent::__construct($module, $fname, $name, $groupid, $row, $col, $num, "hidden");
    $this->value    = $value;

  }

  /** 
   * Build a hidden element.
   * @param $data data used to set the value.
   * @return XHTML code for this element
   * Data need to be provided as array. The first entry will be used.
   * <pre>
   *   array ( "val" );
   * </pre>
   */
  public function build($data = array()) {
    return "<input class=\"input_hidden\" name=\"".$this->getName()."\" ".
                  "type=\"".$this->type."\" ".
                  "value=\"".(Validator::isa($data,"array") 
                                && !Validator::isempty($data)
                              ? $data[0] : $this->value)."\" />";
  }

}

?>
