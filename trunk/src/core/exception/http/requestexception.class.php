<?php

namespace core\exception\http;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of request errors like e.g. invalid
 * headers or invalid parameters passed via any request method.
 * @author Marc Bredt
 */
class RequestException extends Exception {

  const REQ_INVALID_PARAMS = 0;

  const REQ_INVALID_HDR = 1;

  const REQ_INVALID_FORMDATA = 2;

  public $eid = "0008";

  /**
   * Create a RequestException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Request error occured (".$message.",";
    
    switch($code) {
      case 0: parent::__construct($message_prefix." REQ_INVALID_PARAMS)", 
                                  $code, $previous); break;
      case 1: parent::__construct($message_prefix." REQ_INVALID_HDR)", 
                                  $code, $previous); break;
      case 2: parent::__construct($message_prefix." REQ_INVALID_FORMDATA)", 
                                  $code, $previous); break;
      default: parent::__construct($message_prefix." REQ_INVALID_PARAMS)", 
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
