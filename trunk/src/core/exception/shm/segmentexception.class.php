<?php

namespace core\exception\shm;
use \Exception as Exception;

/**
 * Class used to throw an exception in case errors regarding shared memory
 * segments occured.
 * @author Marc Bredt
 */
class SegmentException extends Exception {

  const SHM_SEG_SPACE_FREEING = 0;

  const SHM_SEG_SPACE_FULL = 1;

  const SHM_SEG_INVALID_LAYOUT = 2;

  const SHM_SEG_INVALID_TYPE = 3;

  const SHM_SEG_WRITE_FAILED = 4;

  const SHM_SEG_ATTACH_FAILED = 5;

  /**
   * Create a SegmentException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $msg_prefix = "Shared memomry segment error";

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
      case 2: 
          parent::__construct($msg_prefix.
                                " (".$message.", SHM_SEG_INVALID_LAYOUT)",
                              self::SHM_SEG_INVALID_LAYOUT, $previous);
        break;
      case 3: 
          parent::__construct($msg_prefix.
                                " (".$message.", SHM_SEG_INVALID_TYPE)",
                              self::SHM_SEG_INVALID_TYPE, $previous);
        break;
      case 4: 
          parent::__construct($msg_prefix.
                                " (".$message.", SHM_SEG_WRITE_FAILED)",
                              self::SHM_SEG_WRITE_FAILED, $previous);
        break;
      case 5: 
          parent::__construct($msg_prefix.
                                " (".$message.", SHM_SEG_ATTACH_FAILED)",
                              self::SHM_SEG_ATTACH_FAILED, $previous);
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
