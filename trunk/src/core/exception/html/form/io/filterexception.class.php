<?php

namespace core\exception\html\form\io;
use \Exception as Exception;

/**
 * Class used to throw a local exception in case of invalid parameters passed.
 * @author Marc Bredt
 */
class FilterException extends Exception {

  const FILTER_INVALID_INPUT = 0;

  const FILTER_INVALID_OPTIONS = 1;

  public $eid = "0014";

  /**
   * Create a FilterException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Input error occured (".$message.",";
    
    switch($code) {
      case 0: parent::__construct($message_prefix." FILTER_INVALID_INPUT)", 
                                  $code, $previous); break;
      case 1: parent::__construct($message_prefix." FILTER_INVALID_OPTIONS)", 
                                  $code, $previous); break;
      default: parent::__construct($message_prefix." FILTER_INVALID_INPUT)", 
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
