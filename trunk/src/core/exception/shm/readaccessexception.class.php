<?php

namespace core\exception\shm;
use \Exception as Exception;

/**
 * Class used to throw an exception in case a shared memory access is not
 * readable.
 * @req PHP >= 5.1.0
 * @author Marc Bredt
 */
class ReadAccessException extends Exception {

  /**
   * Create a ReadAccessException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {
    parent::__construct("Read access to the shared memory segment denied ("
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
