<?php

namespace core\object;

class Signature {
  
  private $file  = "";
  
  private $type  = "";

  private $pkey  = "";
 
  private $text  = "";

  private $valid = false;

  public function __toString() {
    return get_class($this).spl_object_hash($this)." [ ".
           "file=".$this->file.", ".
           "type=".$this->type.", ".
           "pkey=".$this->pkey.", ".
           "text=".$this->text.", ".
           "valid=".($this->valid ? "true": "false")." ".
           "] ";
  }

}

?>
