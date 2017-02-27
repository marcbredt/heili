<?php

namespace core\exception\auth;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of session errors.
 * Mainly used when cookie name/value pairs could not be verified.
 * @author Marc Bredt
 */
class SessionException extends Exception {

  const SESSION_INVALID_ID = 0;

  const SESSION_RESTORE_FAILED = 1;

  public $eid = "0010";

  /**
   * Create a SessionException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Session error occured (".$message.",";
    
    switch($code) {
      case 0: 
        parent::__construct($message_prefix." SESSION_INVALID_ID)", 
                              $code, $previous); break;
      case 1: 
        parent::__construct($message_prefix." SESSION_RESTORE_FAILED)", 
                              $code, $previous); break;
      default: 
        parent::__construct($message_prefix." SESSION_INVALID_ID)", 
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
