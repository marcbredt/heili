<?php

namespace core\exception;
use \Exception as Exception;

/**
 * Class used to throw an exception in case a delimiter for shared memory 
 * segment entries is not available.
 * @author Marc Bredt
 */
class DelimiterNotFoundException extends Exception {

  /**
   * Create a DelimiterNotFoundException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {
    parent::__construct("Delimiter not found ("
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
