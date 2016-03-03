<?php

namespace core\exception\shm;
use \Exception as Exception;

/**
 * Class used to throw an exception in case a shared memory access is not
 * acquirable.
 * @author Marc Bredt
 */
class AcquisitionFailedException extends Exception {

  /**
   * Create an AcquisitionFailedException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {
    parent::__construct("Acquisition of shared memory segment failed (".$message.")",
                        $code, $previous);
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
