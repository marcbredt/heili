<?php

namespace core\exception\module;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of module errors.
 * @author Marc Bredt
 */
class ModuleException extends Exception {

  const MODULE_INVALID = 0;

  const MODULE_DEP_UNAVAIL = 1;

  public $eid = "0012";

  /**
   * Create a ModuleException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Module error occured (".$message.",";
    
    switch($code) {
      case 0: parent::__construct($message_prefix." MODULE_INVALID)", 
                                  $code, $previous); break;
      case 1: parent::__construct($message_prefix." MODULE_DEP_UNAVAIL)", 
                                  $code, $previous); break;
      default: parent::__construct($message_prefix." MODULE_INVALID)", 
                                  $code, $previous); break;
    }

  }

  /**
   * Get string representation and stack trace for this exception.
   * @return exception message and stack trace
   */
  public function __toString() {
    return __CLASS__." [".$this->code."]: { ".$this->message." }";
  }

}

?>
