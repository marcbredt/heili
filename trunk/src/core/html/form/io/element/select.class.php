<?php

namespace core\html\form\io\element;
use core\html\form\io\InputElement as InputElement;
use core\util\param\Validator as Validator;

class Select extends InputElement {

  private $title    = null;
  private $size     = null;
  private $multiple = null;
  private $disabled = null;
  private $iclasses = null;

  public function __construct($module = "core", $fname = "", $name = "",
                              $groupid = "e", $row = -1, $col = -1, $num = -1,
                              $title = "select input", $size = 5, 
                              $multiple = "", $disabled = "", $iclasses = "") {

    parent::__construct($module, $fname, $name, $groupid, $row, $col, $num, "select");
    $this->title    = $title;
    $this->size     = $size;
    $this->multiple = $multiple;
    $this->disabled = $disabled;
    $this->iclasses = $iclasses;

  }

  /**
   * Build a selection.
   * @param $data data going to be bind as array.
   * @return A selection with data bound.
   * Data need to be defined as follows.
   * <pre>
   * * data for "select" inputs with optgroups
   *   array(
   *     0 => array(
   *            "glabel"   => "Group #1",
   *            "options"  => array( 1, 2 ),
   *            "olabels"  => array ( "Label A", "Label B" ),
   *            "selected" => array( 1 ) 
   *          ),
   *     1 => array(
   *            "glabel"   => "Group #2",
   *            "options"  => array( 3, 4 ),
   *            "olabels"  => array ( "Label A", "Label B" ),
   *            "selected" => array( 3 ) 
   *          )
   *   );
   *
   * * data for "select" inputs without optgroups
   *   array (
   *     0 => array(
   *            "glabel"   => "",
   *            "options"  => array( 1, 2 ),
   *            "olabels"  => array ( "Label A", "Label B" ),
   *            "selected" => array ( 2 ) 
   *          )
   *   );
   * </pre>
   * TODO: validate data array passed, check for keys,...
   */
  public function build($data = array()) {
    
    global $filelogger;
    $filelogger->log("data = %", array($data),"INFO");

    $select = "";

    // only build a select if data was passed
    if(count($data)>0) {

      $select .= "\n<".$this->type." ".
                   "class=\"input_select ".$this->iclasses."\" ".
                   "name=\"".$this->getName()."\" ".
                   "title=\"".$this->title."\" size=\"".$this->size."\" ".
                   $this->disabled." ".$this->multiple.">";

      foreach($data as $optgroup) {
 
        $filelogger->log("optgroup = %", 
                         array($optgroup), "DEBUG");

        // decide wether to print optgroup labels
        $nosetgroups = (isset($optgroup["glabel"]) 
                          && !Validator::isempty($optgroup["glabel"]) 
                        ? false : true);

        // set optgroup
        $glabel = $optgroup["glabel"];
        $select .= (Validator::equals($nosetgroups,false)
                    ? "\n  <optgroup label=\"".$glabel."\">" : "");

        // create options
        $ogo = $optgroup["options"];
        foreach($ogo as $option_k => $option_v) {

          // is the value marked to be selected?
          $selected = (array_key_exists("selected",$optgroup)
                         && Validator::isa($optgroup["selected"],"array")
                         && Validator::in($option_v,$optgroup["selected"])
                       ? "y" : "n");

          // get option label, refers to the value if none is set
          $olabel = (isset($optgroup["olabels"][$option_k]) 
                     ? $optgroup["olabels"][$option_k] : $option_v); 

          $select .= "\n    <option value=\"".$option_v."\" ".
                       (Validator::equals($selected,"y") && !isset($nomoresel)
                        ? "selected=\"selected\"" : "").">".
                     $olabel."</option>";

          // only mark the first value as selected, if "multiple" is not set
          if(Validator::equals($selected,"y") 
             && !Validator::equals($this->multiple,"multiple=\"multiple\"")) { 
            $nomoresel = "y"; 
            $filelogger->log("selected=%, multiple=%, ".
                                                        "nomoresel=%",
                             array($selected,$this->multiple,$nomoresel),"DEBUG");
          }

        }

        // close optgroup if opened
        $select .= (Validator::equals($nosetgroups,false)
                    ? "\n  </optgroup>" : "");

      }

      $select .= "\n</".$this->type.">\n";

    }

    return $select;

  }

}

?>
