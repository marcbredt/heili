<?php

namespace core\db\statement;
use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;

/**
 * Store (prepared) statements.
 * @author Marc Bredt
 */
class Statement {

  private $statement = null;

  private $params = null;

  private $provides = null;

  public function __construct($sql = null, $params = array(), $provides = array()) {

     if(Validator::isa($sql,"null") || Validator::isa($sql,"string")) 
       $this->statement = $sql;

     if(Validator::isa($params,"array"))
       $this->set_params($params);

     if(Validator::isa($provides,"array"))
       $this->provides = $provides;

  }
  
  public function set_params($params = array()) {

     if(Validator::isa($params,"array")) {
       $this->params = $params;
     }

  }

  public function get_statement() {
    return $this->statement;
  }

  public function set_statement($sql = null) {
    if((Validator::isa($sql,"string") && !Validator::isempty($sql))
       || Validator::isa($sql,"null"))
      $this->statement = $sql;
  }

  public function get_params() {
    return $this->params;
  }

  public function get_provides() {
    return $this->provides;
  }

  public function __toString() {
    return get_class($this)."-".spl_object_hash($this)."=( ".
           "query=".$this->statement.", ".
           "params=".StringUtil::get_object_string($this->params).", ".
           "provides=".StringUtil::get_object_string($this->provides).
           " )";      
  }

}

?>
