<?php

namespace core\exception\auth;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of verification of asset rights for
 * e.g. masks, forms or resources failed.
 * @author Marc Bredt
 */
class RightException extends Exception {

  const RIGHTS_INSUFFICIENT = 0;

  public $eid = "0013";

  /**
   * Create a AuthException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Rights error occured (".$message.",";
    
    switch($code) {
      case 0: 
        parent::__construct($message_prefix." RIGHTS_INSUFICIENT)", 
                              $code, $previous); break;
      default: 
        parent::__construct($message_prefix." RIGHTS_INSUFICIENT)", 
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
