<?php

namespace core\exception\shm;
use \Exception as Exception;

/**
 * Class used to throw an exception in case the payload part of a
 * shared memory segment entry cannot be extracted properly.
 * @author Marc Bredt
 */
class PayloadExtractionException extends Exception {

  const SHM_SEG_ENTRY_AMOUNT = 0; 

  const SHM_SEG_ENTRY_LAYOUT = 1; 

  /**
   * Create a PayloadExtractionException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $msgprefix = "Payload extraction failed";
    switch($code) {
      case 0: 
          parent::__construct($msgprefix." (".$message.
                              ", SHM_SEG_ENTRY_AMOUNT)", $code, $previous);
        break;
      case 1: 
          parent::__construct($msgprefix." (".$message.
                              ", SHM_SEG_ENTRY_LAYOUT)", $code, $previous);
        break;
      default: break;
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
