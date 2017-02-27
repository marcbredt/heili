<?php

namespace core\object;

class License {
  
  protected $file  = "";
  
  protected $type  = "";

  protected $text  = "";

  protected $valid = false;
  
  public function __toString() {
    return get_class($this).spl_object_hash($this)." [ ".
           "file=".$this->file.", ".
           "type=".$this->type.", ".
           "text=".$this->text.", ".
           "valid=".($this->valid ? "true": "false")." ".
           "] ";
  }

}

?>
