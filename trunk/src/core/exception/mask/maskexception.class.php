<?php

namespace core\exception\mask;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of mask file errors.
 * @author Marc Bredt
 */
class MaskException extends Exception {

  const MASK_NOT_FOUND = 0;

  const MASK_INVALID_TYPE = 1;

  const MASK_INVALID = 2;

  public $eid = "0005";

  /**
   * Create a MaskException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Mask error occured (".$message.",";
    
    switch($code) {
      case 0: parent::__construct($message_prefix." MASK_NOT_FOUND)", 
                                  $code, $previous); break;
      case 1: parent::__construct($message_prefix." MASK_INVALID)", 
                                  $code, $previous); break;
      default: parent::__construct($message_prefix." MASK_NOT_FOUND)", 
                                  $code, $previous); break;
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
