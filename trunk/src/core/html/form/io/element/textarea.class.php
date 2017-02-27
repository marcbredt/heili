<?php

namespace core\html\form\io\element;
use core\html\form\io\InputElement as InputElement;
use core\util\param\Validator as Validator;

class Textarea extends InputElement {

  private $title    = null;
  private $nrows    = null;
  private $ncols    = null;
  private $disabled = null;
  private $value    = null;
  private $iclasses = null;

  public function __construct($module = "core", $fname = "", $name = "unknown",
                              $groupid = "e", $row = -1, $col = -1, $num = -1,
                              $title = "textarea input", $nrows = 1, 
                              $ncols = 20, $disabled = "", $value = "", 
                              $iclasses = "") {

    parent::__construct($module, $fname, $name, $groupid, $row, $col, $num, "textarea");
    $this->title    = $title;
    $this->nrows    = $nrows;
    $this->ncols    = $ncols;
    $this->disabled = $disabled;
    $this->value    = $value;
    $this->iclasses = $iclasses;

  }

  /** 
   * Build a textarea element.
   * @param $data data used to set the contents.
   * @return XHTML code for this element
   * Data need to be provided as array. The first entry will be used.
   * <pre>
   *   array ( "some tetarea contents" );
   * </pre>
   */
  public function build($data = array()) {

    // override element data with database values
    if(Validator::isa($data,"array") && count($data)>0 
       && Validator::in($this->name,$data)) 
      $this->value = array($data[$this->name]);

    // build the element
    return "<".$this->type." ".
             "class=\"input_textarea ".$this->iclasses."\" ".
             "name=\"".$this->getName()."\" ".
             "cols=\"".$this->ncols."\" rows=\"".$this->nrows."\" ".
             "title=\"".$this->title."\" ". $this->disabled.">".
             $this->value.
           "</".$this->type.">";

  }

}

?>
