<?php

namespace core\layout;

/**
 * This class represents masks loadable via TemplateMap.
 * @author Marc Bredt
 * @see TemplateMap
 */
class TemplateMask {

  private $module = null;
  private $target = null;
  private $ttype = null;
  private $index = null;
  private $mask = null;
  private $template = null;
  private $type = null;

  public function __construct($module = "", $target = "", $ttype = "", 
                              $index = "", $mask = "", $template = "", 
                              $type = "") {

    
    // TODO: check params 
    $this->module   = $module;
    $this->target   = $target;
    $this->ttype    = $ttype;
    $this->index    = $index;
    $this->mask     = $mask;
    $this->template = $template;
    $this->type     = $type;

  }

  public function get_module() {
    return $this->module;
  }

  public function get_target() {
    return $this->target;
  }

  public function get_ttype() {
    return $this->ttype;
  }

  public function get_index() {
    return $this->index;
  }

  public function get_mask() {
    return $this->mask;
  }

  public function get_template() {
    return $this->template;
  }

  public function get_type() {
    return $this->type;
  }

}

?>
