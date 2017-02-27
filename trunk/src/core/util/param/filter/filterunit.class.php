<?php

namespace core\util\param\filter;
use core\util\string\StringUtil as StringUtil;
use core\util\param\Validator as Validator;
use core\util\param\filter\Filter as Filter;

/**
 * This class is used to store types, flags and other important stuff which is
 * needed to validate a global variable using regular expressions, filter or
 * other sanitizors.
 * @author Marc Bredt
 * @see core\util\param\Accessor
 * @see core\util\param\Validator
 * @see core\util\param\filter\Filter
 */
class FilterUnit {
 
  private $variables = null;
  private $source    = null;
  private $target    = null;

  private $regex     = null;
  private $filters   = null;

  /**
   * Setup the accessor unit.
   * @param $variables target in $source, a global variable or an array of
   *                   variables going to be checked.
   * @param $source location where $variable is stored in otherwise it is the
   *                global variable itself
   * @param $regex regular expression ::get_value() must match if set
   * @param $filter valid validation filter name in Filter::FILTERS
   * @param $sanitizor valid sanitization filter name in Sanitizor::FILTERS
   * @see filter_list()
   * @see core\util\param\Filter
   */
  public function __construct($variables = null, $source = "GET", $regex = "",
                              $filters = "string", $min = null, $max = null) {

    global $filelogger;

    $this->set_variables($variables);
    $this->set_source($source);
    $this->set_target();
    $this->set_regex($regex);
    $this->set_filters($filters);

    $filelogger->debug("setting up accessor unit = %",$this);

  }

  /**
   * Get the variable names.
   * @return variable names.
   */
  public function get_variables() {
    if(!Validator::isa($this->variables,"array")) return array($this->variables);
    else return $this->variables;
  }

  /**
   * Sets the variable name.
   * @param $variable variable name
   */
  private function set_variables($variables = null) {
    if(Validator::isa($variables,"string") || Validator::isa($variables,"array"))
      $this->variables = $variables;
    else
      $this->variables = null;
  }

  /**
   * Get the source.
   * @return the source where the variable set will be find.
   */
  public function get_source(){
    return $this->source;
  }

  /**
   * Set the source. This is the place where the variable set will be find.
   * @param the global variable
   */
  private function set_source($source = "GET"){
    $this->source = $source;
  }

  /**
   * Get the target for this unit and the corresponding filter.
   * @return the target for this unit and the corresponding filter
   */ 
  public function get_target() {
    return $this->target;
  }

  /**
   * Sets the target for this unit and the corresponding filter going to be
   * used.
   * @see core\util\param\access\Accessor
   * @see core\util\param\Filter
   */ 
  private function set_target() {

    switch($this->get_source()){
      case "COOKIE":  $this->target = INPUT_COOKIE;  break; 
      case "ENV":     $this->target = INPUT_ENV;     break; 
      case "GET":     $this->target = INPUT_GET;     break; 
      case "POST":    $this->target = INPUT_POST;    break; 
      case "REQUEST": $this->target = INPUT_REQUEST; break; 
      case "SERVER":  $this->target = INPUT_SERVER;  break; 
      case "SESSION": $this->target = INPUT_SESSION; break; 
      default:        $this->target = null;          break;
    }

  }

  /**
   * Get the regular expression set going to be checked.
   * @return regular expression designed for preg_match()
   * @see preg_match()
   */
  public function get_regex(){
    return $this->regex;
  }

  /**
   * Set the desired regular expression the value of 
   * $this->source[$this->variable] should match.
   * @param $regex regular expression w/o surrounding pattern encapsulation
   *               for e.g. preg_match()
   */
  private function set_regex($regex = "") {
    $this->regex = "/".StringUtil::escape_all(array("/"),$regex)."/";
  }

  /**
   * Get the validation filter names set.
   * @return validation filter names set
   */
  public function get_filters(){
    return $this->filters;
  }

  /**
   * Set the (default) names of the filters going to be applied.
   * @param $filter the names of the filters
   */
  private function set_filter($filter = "string") {
    $this->filter = (!Validator::isempty(array_diff(explode(",",$filters),
                                         Filter::NAMES))
                     ? "string" : $filters);
  }

  /**
   * Get the original value for this unit.
   * @return the value 
   */
  public function get_value(){
    //return $this->source[$this->variable];
  }

  /**
   * Set or update the units value.
   */
  private function set_value($value = null){
    //$this->source[$this->variable] = $value;
  }

  /**
   * Get printable information on this object.
   * @return string representation for this object
   */
  public function __toString() {
    return get_class($this).spl_object_hash($this)."=[ ".
             "variables=".$this->get_variables().", ".
             "source=".$this->get_source().", ".
             "target=".$this->get_target().", ".
             "regex=".$this->get_regex().", ".
             "filter=".$this->get_filter().", ".
             "sanitizor=".$this->get_sanitizor().
           " ]";
  }
 
}

?>
