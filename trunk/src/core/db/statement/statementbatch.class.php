<?php

namespace core\db\statement;
use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;

/**
 * A statement batcho. Used to execute multiple
 * @see Executor
 * @author MarcBredt
 */
class StatementBatch {
  
  private $batch = null;

  public function __construct() {
    $this->batch = array();
  }

  public function add($stmt = null) {
    if(Validator::isclass($stmt,"core\db\statement\Statement"))
      $this->batch[] = $stmt;
  }

  public function remove() {
    array_pop($this->batch);
  }

  public function get() {
    return $this->batch;
  }

  public function __toString() {
    $ret = get_class($this).spl_object_hash($this)."=(";
    for($i=0; $i<count($this->batch); $i++) {
      if($i < count($this->batch)-1)
        $ret .= "{ stmt=".$this->batch[$i]->get_statement().", ".
          StringUtil::get_object_string($this->batch[$i]->get_params())."}, "; 
      else
        $ret .= "{ stmt=".$this->batch[$i]->get_statement().", ".
          StringUtil::get_object_string($this->batch[$i]->get_params())."} "; 
    }
    $ret .= ")";
    return $ret;
  }

}

?>
