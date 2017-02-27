<?php

namespace core\exception\param;
use \Exception as Exception;

/**
 * Class used to throw exception in case parameters passed
 * are not encapsulated by datatype array. Used to check
 * input.
 * @req PHP >= 5.1.0
 * @author Marc Bredt
 */
class ParamNotArrayException extends Exception {
  
  /**
   * This stores an error id. If this attribute is not available for any reason
   * the default error id 0000 is used. 
   * @see conf/errors.xml
   */ 
  private $eid = "0001";

  /**
   * Create a ParamNotArrayException.
   * @param $message default message to be printed on raise.
   * @param $code code for differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "Parameters provided are not an array.", 
                              $code = 0, Exception $previous = null) {
    parent::__construct($message,$code,$previous);
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
