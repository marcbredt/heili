<?php

namespace core\exception\shm;
use \Exception as Exception;

/**
 * Class used to throw an exception in case a semaphore operation failed.
 * @req PHP >= 5.1.0
 * @author Marc Bredt
 */
class SemaphoreException extends Exception {

  const SEM_ACQUISITION_FAILED = 0;

  const SEM_RELEASE_FAILED = 1;

  const SEM_HANDLER_INVALID_MODE = 2;

  const SEM_INVALID_KEY = 3;

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
      case 2:
        parent::__construct($msg_prefix." (".$message.", SEM_HANDLER_INVALID_MODE)",
                            self::SEM_HANDLER_INVALID_MODE, $previous);
        break;
      case 3:
        parent::__construct($msg_prefix." (".$message.", SEM_INVALID_KEY)",
                            self::SEM_HANDLER_INVALID_MODE, $previous);
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
