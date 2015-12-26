<?php

namespace core\exception\register;
use \Exception as Exception;

/**
 * Class used to throw exception in case parameters passed
 * to a Register do not fit the requirements.
 * @author Marc Bredt
 */
class InvalidRegisterObjectException extends Exception {

  /**
   * Create a InvalidRegisterObjectException.
   * @param $message default message to be printed on raise.
   * @param $code code for differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "Object is invalid.", 
                              $code = 0, Exception $previous = null) {
    parent::__construct($message,$code,$previous);
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
