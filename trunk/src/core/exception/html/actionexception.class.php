<?php

namespace core\exception\html\form;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of action errors invoked via forms.
 * @author Marc Bredt
 */
class ActionException extends Exception {

  const ACTION_INVALID_NAME = 0;

  const ACTION_INVALID_FORM_DEFINITION = 1;

  const ACTION_CHECKS_FAILED = 2;

  const ACTION_INVALID_TOKEN = 3;

  public $eid = "0015";

  /**
   * Create a ActionException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Action error occured (".$message.",";
    
    switch($code) {
      case 0: parent::__construct($message_prefix." ACTION_INVALID_NAME", 
                                  $code, $previous); break;
      case 1: parent::__construct($message_prefix." ACTION_INVALID_FORM_DEFINITION", 
                                  $code, $previous); break;
      case 2: parent::__construct($message_prefix." ACTION_INVALID_TOKEN", 
                                  $code, $previous); break;
      default: parent::__construct($message_prefix." FORM_ELEMENT_NAME)", 
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
