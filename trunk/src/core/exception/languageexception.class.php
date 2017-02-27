<?php

namespace core\exception;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of language errors.
 * @author Marc Bredt
 */
class LanguageException extends Exception {

  const LANG_NOT_AVAILABLE = 0;

  const LANG_NO_FILE = 1;

  const LANG_LOAD_VAR_FAILED = 2;

  public $eid = "0004";

  /**
   * Create a LanguageException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Language error occured (".$message.",";
    
    switch($code) {
      case 0: parent::__construct($message_prefix." LANG_NOT_AVAILABLE)", 
                                  $code, $previous); break;
      case 1: parent::__construct($message_prefix." LANG_NO_LFILE)", 
                                  $code, $previous); break;
      case 2: parent::__construct($message_prefix." LANG_LOAD_VAR_FAILED)", 
                                  $code, $previous); break;
      default: parent::__construct($message_prefix." LANG_NOT_AVAILABLE)", 
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
