<?php

namespace core\mask;

use core\util\param\Validator as Validator;

/**
 * Represents a mask going to be loaded by MaskLoader::load().
 * This class is mainly used to distinct the loading mechanism from the mask
 * itself and providing a way to restore local exceptions wheneever the mask
 * was the reason for the exception thrown.
 * @author Marc Bredt
 * @see ExceptionHandler::handle_previous_local_exception().
 */
class Mask {

  private $mask = null;

  private $template = null;

  private $type = null;

  private $module = null; 

  private $target = null;

  private $mime_type = null;

  /**
   * Setup a mask. Loads mask/$type/$mask/$template.$type from any module's 
   * mask directory. $mask can be ommitted. This is useful for when loading
   * masks from modules in mods/ and avoids doubling names.
   * @param $mask mask name 
   * @param $template template file name
   * @param $type template file type and extension, defaults to php
   * @param $module module the mask belongs to, defaults to core
   */
  public function __construct($mask = null, $template = null, $type = "php",
                              $module = "core") {

    // store mask values and module relationship for this mask
    $this->mask = $mask;
    $this->template = $template;
    $this->type = $type;
    $this->module = $module;

    // setup the default mask directory, defaults to the core mask directory
    if(!Validator::isa($this->module,"string") || Validator::isempty($this->module)
       || Validator::equals($this->module,"core")) $this->target = PATH_MASK;
    else $this->target = constant("MOD_".strtoupper($this->module)."_PATH_MASK");

    // append /$type/$mask/$template.$type to $target
    if(Validator::isa($type,"string") && !Validator::isempty($type)
       && is_dir($this->target.DIRECTORY_SEPARATOR.$type))
      $this->target .= DIRECTORY_SEPARATOR.$type;

    if(Validator::isa($mask,"string") && !Validator::isempty($type)
       && is_dir($this->target.DIRECTORY_SEPARATOR.$mask))
      $this->target .= DIRECTORY_SEPARATOR.$mask;

    if(Validator::isa($template,"string") && !Validator::isempty($type)
       && file_exists($this->target.DIRECTORY_SEPARATOR.$template.".".$type)) 
      $this->target .= DIRECTORY_SEPARATOR.$template.".".$type;

    $this->mime_type = @mime_content_type($this->target);

  }

  /**
   * Get the evaluated (php) mask.
   * @return htmlentities() encoded evaluated (php) mask.
   * @see ob_start()
   * @see ob_get_clean()
   * @see htmlentities()
   */
  public function get() {

    global $filelogger;

    if(!is_file($this->target) || !is_readable($this->target)) {
      $filelogger->err("%, mask=%, template=%, type=%",
        array(new MaskException("Mask not found"),$this->mask,$this->template,
              $this->type));
      throw(new MaskException("Mask not found"));

    } else if(!Validator::equals($this->mime_type,"text/x-php")
        && !Validator::equals($this->mime_type,"text/html")) {
      $filelogger->err("%, mask=%, template=%, type=%",
        array(new MaskException("Mask invalid type",1),$this->mask,$this->template,
              $this->type));
      throw(new MaskException("Mask invalid type",1));

    } else {
      // buffer output to evaluate any php code first to be able to add only
      // the corresponding template code where every HTML code has been already
      // built
      ob_start();
      include($this->target);
      if(!Validator::isa($filelogger,"null"))
        $filelogger->debug("oblvl=%, obstat=%, mask content=\n%",
          array(ob_get_level(),ob_get_status(),ob_get_contents()));
      return htmlentities(ob_get_clean());

    } 

  }

  /**
   * Print information on this mask.
   * @return a string containing information about this mask
   */
  public function __toString() {
    return get_class($this).spl_object_hash($this)."=( ".
           "mask=".$this->mask.", ".
           "template=".$this->template.", ".
           "type=".$this->type.", ".
           "module=".$this->module.", ".
           "target=".$this->target.", ".
           "mime_type=".$this->mime_type.
           " )";
  }

  /**
   * Try to figure out if this mask caused local exceptions if there were some.
   */
  public function restore() {}

  /**
   * Get full masks name identifier. Used to address mask nodes when evaluating
   * rights for a mask using the RightManager.
   * @return a string addressing this mask in the form 
   *         MODULE:MASK:TEMPLATE:TYPE
   * @see RightManager
   */
  public function get_nid() {
    return $this->module.":".$this->mask.":".$this->template.":".$this->type;
  }

}


?>
