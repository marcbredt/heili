<?php

namespace core\exception\register;
use \Exception as Exception;

/**
 * Class used to throw exception in case parameters passed
 * to a Register do not fit the requirements.
 * @req PHP >= 5.1.0
 * @author Marc Bredt
 */
class RegisterException extends Exception {

  const REG_INVALID_KEY = 0;

  const REG_INVALID_OBJ = 1;

  /**
   * Create a InvalidRegisterKeyException.
   * @param $message default message to be printed on raise.
   * @param $code code for differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", $code = 0, 
                              Exception $previous = null) {

    $msg_prefix = "Register error occured";

    switch($code) {
      case 0: 
        parent::__construct($msg_prefix." (".$message.", REG_INVALID_KEY)",
                            self::REG_INVALID_KEY, $previous);
        break;
      default:
        parent::__construct($msg_prefix." (".$message.", REG_INVALID_OBJ)",
                            self::REG_INVALID_OBJ, $previous);
        break;
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
