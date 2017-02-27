<?php

namespace core\exception\html\form;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of HTML form errors.
 * @author Marc Bredt
 */
class FormException extends Exception {

  const FORM_ELEMENT_INVALID_TYPE = 0;

  public $eid = "0003";

  /**
   * Create a FormException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Form error occured (".$message.",";
    
    switch($code) {
      case 0: parent::__construct($message_prefix." FORM_ELEMENT_INVALID_TYPE", 
                                  $code, $previous); break;
      default: parent::__construct($message_prefix." FORM_ELEMENT_INVALID_TYPE)", 
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
