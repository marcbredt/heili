<?php

namespace core\exception\auth;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of auth errors.
 * @author Marc Bredt
 */
class AuthException extends Exception {

  const AUTH_INSUFICIENT_RIGHTS = 0;

  const AUTH_INVALID_CREDENTIALS = 1;

  const AUTH_INVALID_METHOD = 2;

  public $eid = "0007";

  /**
   * Create a AuthException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Authentication error occured (".$message.",";
    
    switch($code) {
      case 0: 
        parent::__construct($message_prefix." AUTH_INSUFICIENT_RIGHTS)", 
                              $code, $previous); break;
      case 1: 
        parent::__construct($message_prefix." AUTH_INVALID_CREDENTIALS)", 
                              $code, $previous); break;
      case 2: 
        parent::__construct($message_prefix." AUTH_INVALID_METHOD)", 
                              $code, $previous); break;
      default: 
        parent::__construct($message_prefix." AUTH_INSUFICIENT_RIGHTS)", 
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
