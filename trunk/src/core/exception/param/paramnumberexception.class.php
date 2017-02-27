<?php

namespace core\exception\param;
use \Exception as Exception;

/**
 * Class used to throw exception in case parameters passed
 * do not fit the amount of parameters needed.
 * @req PHP >= 5.1.0
 * @see AccessibleObject
 * @author Marc Bredt
 */
class ParamNumberException extends Exception {

  /** 
   * This stores an error id. If this attribute is not available for any reason
   * the default error id 0000 is used. 
   * @see conf/errors.xml
   */ 
  public $eid = "0002";

  /**
   * Create a ParamNumberException.
   * @param $message default message to be printed on raise.
   * @param $code code for differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "Invalid amount of parameters passed.", 
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
