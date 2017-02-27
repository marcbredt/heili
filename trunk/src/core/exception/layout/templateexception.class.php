<?php

namespace core\exception\layout;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of database errors.
 * @author Marc Bredt
 */
class TemplateException extends Exception {

  const TEMPLATE_INVALID_FILES = 0;

  const TEMPLATE_VALIDATION_FAILED= 1;

  public $eid = "0011";

  /**
   * Create a TemplateException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Template error occured (".$message.",";
    
    switch($code) {
      case 0: parent::__construct($message_prefix." TEMPLATE_INVALID_FILES)", 
                                  $code, $previous); break;
      case 1: parent::__construct($message_prefix." TEMPLATE_VALIDATION_FAILED)", 
                                  $code, $previous); break;
      default: parent::__construct($message_prefix." TEMPLATE_INVALID_FILES)", 
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
