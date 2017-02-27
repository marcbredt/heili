<?php

namespace core\object;
use core\object\Validatable as Validatable;
use core\util\param\Validator as Validator;

class Version implements Validatable {

  private $major = null;

  private $minor = null;

  private $fixes = null;

  private $build = null;

  public function __construct($version = "") {

    global $filelogger;

    if(Validator::isa($version,"string") 
       && Validator::matches($version,"/^[0-9]+(\.[0-9]+){0,3}$/")) {
      $filelogger->log("-----",array(),"DEBUG");
      $numbers = explode(".",$version);
      $this->major = "".(isset($numbers[0]) ? $numbers[0] : "0"); 
      $this->minor = "".(isset($numbers[1]) ? $numbers[1] : "0"); 
      $this->fixes = "".(isset($numbers[2]) ? $numbers[2] : "0"); 
      $this->build = "".(isset($numbers[3]) ? $numbers[3] : "0"); 

    } else {
      $filelogger->log("+++++ v=%, m=%",
                       array(
                         $version,
                         Validator::matches($version,"/^[0-9]+(\.[0-9]+){0,3}$/")),
                       "DEBUG");
      $this->major = "0"; 
      $this->minor = "0";
      $this->fixes = "0";
      $this->build = "0";
    }

  }
  
  public function validate($comparator = null, $version = null) {
    if(!Validator::isa($comparator,"string")
       || !Validator::isa($version,"core\object\Version")) return false;
    switch($comparator) {
      case "==" : return ($this->geti() == $version->geti()); break;
      case ">=" : return ($this->geti() >= $version->geti()); break;
      case "<=" : return ($this->geti() <= $version->geti()); break;
      case ">"  : return ($this->geti() >  $version->geti()); break;
      case "<"  : return ($this->geti() <  $version->geti()); break;
      default : return false; break;
    }
  }  

  private function geti() {
    return intval($this->major.$this->minor.$this->fixes.$this->build);
  }
 
  public function __toString() {
    return $this->major.".".$this->minor.".".$this->fixes.".".$this->build;
  }

}

?>
