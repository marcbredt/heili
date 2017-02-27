<?php

namespace core\html\form\io;

use core\object\Buildable as Buildable;
use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;

use core\html\form\io\element\Text as Text;
use core\html\form\io\element\Textarea as Textarea;
use core\html\form\io\element\Password as Password;
use core\html\form\io\element\Checkbox as Checkbox;
use core\html\form\io\element\Radio as Radio;
use core\html\form\io\element\Select as Select;
use core\html\form\io\element\Hidden as Hidden;
use core\html\form\io\element\File as File;
use core\html\form\io\element\Submit as Submit;
use core\html\form\io\element\Reset as Reset;
use core\html\form\io\element\Image as Image;
use core\html\form\io\element\Button as Button;
use core\html\form\io\element\Slider as Slider;

/**
 * This class is used to represent any form element.
 * A form element configuration looks like
 * <pre><element name="" title="" show="y" disabled="y" required="y" type="password" 
 *               regex="[0-9]{8}[0-9]*" length="20" min="8" max="20" selected="n"
 *               checked="n" value="any" /></pre>
 * * disabled="y" -> "readonly"
 * @author Marc Bredt
 */
class FormElement implements Buildable {

  private $module   = null;
  private $fname    = null;
  private $groupid  = null;
  private $name     = null;
  private $title    = null;
  private $text     = null;
  private $show     = null;
  private $disabled = null;
  private $required = null;
  private $type     = null;
  private $btype    = null;
  private $regex    = null;
  private $size     = null;
  private $length   = null;
  private $min      = null;
  private $max      = null;
  private $cols     = null;
  private $rows     = null;
  private $selected = null;
  private $checked  = null;
  private $value    = null;
  private $source   = null;
  private $multiple = null;
  private $tclasses = null;
  private $iclasses = null;
  private $defines  = null;
  private $filters  = null;
  private $roles    = null;

  /**
   * Setup a form element.
   */
  public function __construct($module = null, $fname = null, $group = null,
                              $name = null, $title = null, $show = null, 
                              $disabled = null, $required = null, $type = null, 
                              $regex = null, $length = null, $min = null, 
                              $max = null, $selected = null, $checked = null, 
                              $value = null, $size = null, $cols = null,
                              $rows = null, $source = null, $multiple = null,
                              $btype = null, $text = null, $tclasses = null,
                              $iclasses = null, $defines = null, $filters = null,
                              $roles = null) {
    global $filelogger;

    $filelogger->debug("FormElement = [".
                       " (%,%,%), (%,%,%), (%,%,%), (%,%,%), (%,%,%),".
                       " (%,%,%), (%,%,%), (%,%,%), (%,%,%), (%,%,%),".
                       " (%,%,%), (%,%,%), (%,%,%), (%,%,%), (%,%,%),".
                       " (%,%,%), (%,%,%), (%,%,%), (%,%,%), (%,%,%),".
                       " (%,%,%), (%,%,%), (%,%,%), (%,%,%), (%,%,%),".
                       " (%,%,%), (%,%,%), (%,%,%) ]",
      array(
            "module",$module,gettype($module),"fname",$fname,gettype($fname),
            "group",$group,gettype($group),"btype",$btype,gettype($btype),
            "name",$name,gettype($name),"title",$title,gettype($title),
            "show",$show,gettype($show),"disabled",$disabled,gettype($disabled),
            "required",$required,gettype($required),"type",$type,gettype($type),
            "regex",$regex,gettype($regex),"length",$length,gettype($length),
            "min",$min,gettype($min),"max",$max,gettype($max),
            "selected",$selected,gettype($selected),"checked",$checked,gettype($checked),
            "value",$value,gettype($value),"size",$size,gettype($size),
            "cols",$cols,gettype($cols),"rows",$rows,gettype($rows),
            "source",$source,gettype($source),"multiple",$multiple,gettype($multiple),
            "text",$text,gettype($text),"tclasses",$tclasses,gettype($tclasses),
            "iclasses",$iclasses,gettype($iclasses),"defines",$defines,gettype($defines),
            "filters",$filters,gettype($filters),"roles",$roles,gettype($roles)
           )
    );   
 
    $this->module   = $module;
    $this->fname    = $fname;
    $this->groupid  = (Validator::equals($group,"globals") ? "g" : "e");
    $this->name     = $name;
    $this->title    = $title;
    $this->text     = $text;

    $this->show     = $show;
    $this->disabled = (Validator::equals($disabled,"y")
                       ? "disabled=\"disabled\"" : "");
    $this->required = $required;

    $this->type     = $type;
    $this->btype    = $btype;

    $this->size     = (Validator::matches($size,"/^[1-9]{1}[0-9]*$/")&&$size>0
                       ? $size : 1);
    $this->length   = (Validator::matches($length,"/^[1-9]{1}[0-9]*$/")&&$length>0
                       ? $length : 254);
    $this->min      = (Validator::matches($min,"/^[1-9]{1}[0-9]*$/")&&$min>0
                       ? $min : 0);
    $this->max      = (Validator::matches($max,"/^[1-9]{1}[0-9]*$/")&&$max>0
                       ? $max : 100);

    $this->cols     = (Validator::matches($cols,"/^[1-9]{1}[0-9]*$/")&&$cols>0
                       ? $cols : 30);
    $this->rows     = (Validator::matches($rows,"/^[1-9]{1}[0-9]*$/")&&$rows>0
                       ? $rows : 3);

    $this->regex    = $regex; // used to check values inserted on submit
    $this->value    = $value;
    $this->source   = $source;

    $this->selected = (Validator::equals($selected,"y")
                       ? "selected=\"selected\"" : "");
    $this->checked  = (Validator::equals($checked,"y")
                       ? "checked=\"checked\"" : "");
    $this->multiple = (Validator::equals($multiple,"y")
                       ? "multiple=\"multiple\"" : "");

    $this->tclasses = $tclasses;
    $this->iclasses = $iclasses;

    $this->defines  = $defines; 

    $this->filters  = (Validator::matches($filters,
                         "/^[a-z]+(_{0,1}[a-z]+)*(,[a-z]+(_{0,1}[a-z]+)*)*$/") 
                       ? $filters : "string"); 
    $this->roles    = (Validator::equals($roles,"") 
                       ? array("*") : explode(",",$roles));

  }

  /**
   * Build HTML code for an input element.
   *
   * TODO: check if client based differents exist for e.g. IE,FF,SF,...
   *       layout should be bound to css defs, doc dtd defines elements/attrs
   * TODO: check data binding, htmlspecialchars()/...
   * TODO: $data validation
   * TODO: eventually Data object with XML resultset and array build from XML
   * TODO: implement html 5 elements like, slider/number/...
   *
   * NOTE: working with ids is currently not used here to be XHTML conform
   *       as it is possible to break conformity when there are equal ids
   *
   * Data for input elements need to be designed as arrays. See the 
   * corresponding classes to check how data need to be defined in detail.
   *
   * @param $row row index, for entry elements
   * @param $col col index, for entry elements
   * @param $num num index, for global elements
   * @param $data data passed for element group "entries"
   * @return HTML code for an input element
   * @see https://www.w3.org/TR/html5/
   */
  public function build($row = -1, $col = -1, $num = -1, $data = array()){

    global $filelogger;

    $filelogger->debug("row=%, col=%, num=%, data=%", 
                       array($row,$col,$num,StringUtil::get_object_string($data)));

    // element gonna be setup
    $e = null;

    switch($this->type) {

      // input fields
      // TODO: mapping data on to elements of group "entries"

      case "textarea" : 
        $e = new Textarea($this->module, $this->fname, $this->name,
                          $this->groupid, $row, $col, $num, 
                          $this->title, $this->rows, $this->cols,
                          $this->disabled, $this->value, $this->iclasses);
        break;

      case "text" : 
        $e = new Text($this->module, $this->fname, $this->name,
                      $this->groupid, $row, $col, $num, $this->title, 
                      $this->size, $this->length, $this->value, 
                      $this->disabled, $this->iclasses);
        break;

      case "password" : 
        $e = new Password($this->module, $this->fname, $this->name,
                          $this->groupid, $row, $col, $num, $this->title, 
                          $this->size, $this->length, $this->disabled, 
                          $this->iclasses);
        break;

      // name AND value characterize the element, same name/diff value => multichoice
      case "checkbox" : 
        $e = new Checkbox($this->module, $this->fname, $this->name, 
                          $this->groupid, $row, $col, $num, $this->title, 
                          $this->value, $this->checked, $this->disabled,
                          $this->iclasses);
        break;

      // ONLY value characterizes the element, the name is fix on single choices
      case "radio" : 
        $e = new Radio($this->module, $this->fname, $this->name,
                       $this->groupid, $row, $col, $num, $this->title, 
                       $this->value, $this->checked,$this->disabled,
                       $this->iclasses);
        break;

      // optgroups/options will be passed as data array, s.a. $data
      case "select" : 
        $e = new Select($this->module, $this->fname, $this->name,
                        $this->groupid, $row, $col, $num, $this->title, 
                        $this->size, $this->multiple, $this->disabled, 
                        $this->iclasses);
        break;

      // data fields

      // value must be adjustable, e.g. see Tokenizer
      case "hidden" : 
        $e = new Hidden($this->module, $this->fname, $this->name,
                        $this->groupid, $row, $col, $num, $this->value);
        break;

      case "file" : 
        $e = new File($this->module, $this->fname, $this->name, 
                      $this->groupid, $row, $col, $num, $this->title, 
                      $this->disabled, $this->iclasses);
        break;

      //  buttons 
      case "submit" : 
        $e = new Submit($this->module, $this->fname, $this->name,
                        $this->groupid, $row, $col, $num, $this->title, 
                        $this->value, $this->disabled, $this->iclasses);
        break;

      case "reset" : 
        $e = new Reset($this->module, $this->fname, $this->name, 
                       $this->groupid, $row, $col, $num, $this->title, 
                       $this->value, $this->disabled, $this->iclasses);
        break;

      case "image" : 
        $e = new Image($this->module, $this->fname, $this->name, 
                       $this->groupid, $row, $col, $num, $this->title, 
                       $this->value, $this->source, $this->disabled, 
                       $this->iclasses);
        break;

      case "button" : 
        $e = new Button($this->module, $this->fname, $this->name, 
                        $this->groupid, $row, $col, $num, $this->title, 
                        $this->btype, $this->value, $this->text, 
                        $this->disabled, $this->iclasses);
        break;

      default: return "-";

    }

    return $e->build($data);

  }

  /**
   * Get string representation for a form element.
   * @return string containing information on this element
   */
  public function __toString() {
    return get_class($this)."-".spl_object_hash($this)."=( ".
             "module=".$this->module.", ".
             "fname=".$this->fname.", ".
             "groupid=".$this->groupid.", ".
             "name=".$this->name.", ".
             "title=".$this->title.", ".
             "text=".$this->text.", ".
             "show=".$this->show.", ".
             "disabled=".$this->disabled.", ".
             "required=".$this->required.", ".
             "type=".$this->type.", ".
             "btype=".$this->btype.", ".
             "regex=".$this->regex.", ".
             "length=".$this->length.", ".
             "size=".$this->length.", ".
             "min=".$this->min.", ".
             "max=".$this->max.", ".
             "cols=".$this->cols.", ".
             "rows=".$this->rows.", ".
             "selected=".$this->selected.", ".
             "checked=".$this->checked.", ".
             "value=".$this->value.", ".
             "source=".$this->source.
             "tclasses=".$this->tclasses.", ".
             "iclasses=".$this->iclasses.", ".
             "filters=".$this->filters.", ".
             "roles=".$this->roles.
           " )";
  }

  public function getTitle() {
    return $this->title;
  }

  public function getTclasses() {
    return $this->tclasses;
  }

  public function get_roles() {
    return $this->roles;
  }

}

?>
