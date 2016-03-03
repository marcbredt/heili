<?php

namespace core\exception\shm;
use \Exception as Exception;

/**
 * Class used to throw an exception in case a data going to be stored in a
 * shared memory segment cannot be stored due to space.
 * @author Marc Bredt
 */
class SegmentSpaceException extends Exception {

  const SHM_SEG_SPACE_FREEING = 0;

  const SHM_SEG_SPACE_FULL = 1;

  /**
   * Create a SegmentSpaceException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $msg_prefix = "Problems with the segment space occurred";

    switch($code) {
      case 0: 
          parent::__construct($msg_prefix.
                                " (".$message.", SHM_SEG_SPACE_FREEING)",
                              self::SHM_SEG_SPACE_FREEING, $previous);
        break;
      case 1: 
          parent::__construct($msg_prefix.
                                " (".$message.", SHM_SEG_SPACE_FULL)",
                              self::SHM_SEG_SPACE_FULL, $previous);
        break;
      default:
          parent::__construct($msg_prefix.
                                " (".$message.", SHM_SEG_SPACE_FREEING)",
                              self::SHM_SEG_SPACE_FREEING, $previous);
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
