<?php

namespace core\html\form\io\element;
use core\html\form\io\InputElement as InputElement;
use core\util\param\Validator as Validator;

class Password extends InputElement {

  private $title    = null;
  private $size     = null;
  private $length   = null;
  private $disabled = null;
  private $iclasses = null;

  public function __construct($module = "core", $fname = "", $name = "", 
                              $groupid = "e", $row = -1, $col = -1, $num = -1,
                              $title = "text input", $size = 5, $length = 254, 
                              $disabled = "", $iclasses = "") {

    parent::__construct($module, $fname, $name, $groupid, $row, $col, $num, "password");
    $this->title    = $title;
    $this->size     = $size;
    $this->length   = $length;
    $this->disabled = $disabled;
    $this->iclasses = $iclasses;

  }

  /** 
   * Build a password element.
   * @param $data data used to set the value. Here it is omitted as 
   *              password values should not be set
   * @return XHTML code
   */
  public function build($data = array()) {
    return "<input class=\"input_password ".$this->iclasses."\" ".
                  "name=\"".$this->getName()."\" ".
                  "title=\"".$this->title."\" alt=\"".$this->title."\" ".
                  "size=\"".$this->size."\" maxlength=\"".$this->length."\" ".
                  "type=\"".$this->type."\" ".$this->disabled."/>";
  }

}

?>
