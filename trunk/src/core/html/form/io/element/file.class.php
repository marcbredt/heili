<?php

namespace core\html\form\io\element;
use core\html\form\io\InputElement as InputElement;
use core\util\param\Validator as Validator;

class File extends InputElement {

  private $title    = null;
  private $disabled = null;
  private $iclasses = null;

  public function __construct($module = "core", $fname = "", $name = "",
                              $groupid = "e", $row = -1, $col = -1, $num = -1,
                              $title = "file input", $disabled = "", $iclasses = "") {

    parent::__construct($module, $fname, $name, $groupid, $row, $col, $num, "file");
    $this->title    = $title;
    $this->disabled = $disabled;
    $this->iclasses = $iclasses;

  }

  /** 
   * Build a file element.
   * @param $data data used to set the value.
   * @return XHTML code for this element
   * Data need to be provided as array. The first entry will be used.
   * <pre>
   *   array ( "val" );
   * </pre>
   */
  public function build($data = array()) {
    return "\n<input class=\"input_file ".$this->iclasses."\" ".
                    "name=\"".$this->getName()."\" ".
                    "title=\"".$this->title."\" alt=\"".$this->title."\" ".
                    "type=\"".$this->type."\" ".$this->disabled." />";
  }

}

?>
