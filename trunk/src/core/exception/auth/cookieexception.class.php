<?php

namespace core\exception\auth;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of cookie errors.
 * Mainly used when cookie name/value pairs could not be verified.
 * @author Marc Bredt
 */
class CookieException extends Exception {

  const COOKIE_INVALID_NAME = 0;

  const COOKIE_INAVLID_VALUE = 1;

  public $eid = "0009";

  /**
   * Create a CookieException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Cookie error occured (".$message.",";
    
    switch($code) {
      case 0: 
        parent::__construct($message_prefix." COOKIE_INVALID_NAME)", 
                              $code, $previous); break;
      case 1: 
        parent::__construct($message_prefix." COOKIE_INVALID_VALUE)", 
                              $code, $previous); break;
      default: 
        parent::__construct($message_prefix." COOKIE_INVALID_NAME)", 
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
