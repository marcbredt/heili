<?php

namespace core\exception\control;
use \Exception as Exception;

/**
 * Class used to throw an exception in case a timer problem occured.
 * @author Marc Bredt
 * @req PHP >= 5.1.0
 */
class TimerException extends Exception {

  /**
   * Create a TimerException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {
    parent::__construct("A timer problem occured ("
                          .$message.")", $code, $previous);
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
