<?php

namespace core\exception\shm;
use \Exception as Exception;

/**
 * Class used to throw an exception in case a semaphore operation failed.
 * @author Marc Bredt
 */
class SemaphoreException extends Exception {

  const SEM_ACQUISITION_FAILED = 0;

  const SEM_RELEASE_FAILED = 1;

  /**
   * Create an SemaphoreException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {
    $msg_prefix = "Semaphore error occured";
    switch($code) {
      case 0: 
        parent::__construct($msg_prefix." (".$message.", SEM_ACQUISITION_FAILED)",
                            self::SEM_ACQUISITION_FAILED, $previous);
        break;
      case 1:
        parent::__construct($msg_prefix." (".$message.", SEM_RELEASE_FAILED)",
                            self::SEM_RELEASE_FAILED, $previous);
        break;
      default:
        parent::__construct($msg_prefix." (".$message.", SEM_ACQUISITION_FAILED)",
                            self::SEM_ACQUISITION_FAILED, $previous);
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
