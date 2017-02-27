<?php

namespace core\object;
use core\object\Serializable as Serializable;

class SerializableObject implements Serializable {

  /**
   * This function returns only the child class attributes. Used to serialize
   * an object. Take care when declaring the childs attributes which sould be
   * visible. Private ones will not be found. Use "preteced" keyword instead. 
   * @return array containing a childs class attribute names
   */
  public function get_child_class_var_names() {
    $pc = get_parent_class($this); 
    $pcv = get_object_vars(new $pc); 
    $cv = get_object_vars($this);
    return array_keys(array_diff($cv,$pcv)); 
  }

  public function __sleep() {
    return $this->get_child_class_var_names();
  }

  public function __wakeup() {
  }

}

?>
