<?php

namespace modules\calendar\exception;
use \Exception as Exception;

/**
 * Class used to throw an exception in case errors regarding shared memory
 * segments occured.
 * @req PHP >= 5.1.0, class Exception
 * @author Marc Bredt
 */
class CalendarException extends Exception {

  const CAL_INVALID_NAME = 0;

  const CAL_INVALID_TYPE = 1;

  /**
   * Create a CalendarException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $msg_prefix = "Calendar error occured";

    switch($code) {
      case 0: 
          parent::__construct($msg_prefix.
                                " (".$message.", CAL_INVALID_NAME)",
                              self::CAL_INVALID_NAME, $previous);
        break;
      case 1: 
          parent::__construct($msg_prefix.
                                " (".$message.", CAL_INVALID_TYPE)",
                              self::CAL_INVALID_TYPE, $previous);
        break;
      default:
          parent::__construct($msg_prefix.
                                " (".$message.", CAL_INVALID_NAME)",
                              self::CAL_INVALID_NAME, $previous);
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
