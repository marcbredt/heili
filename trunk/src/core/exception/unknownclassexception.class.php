<?php

namespace core\exception;
use \Exception as Exception;

/**
 * Exception to be thrown whenever a class could not be loaded.
 * @req PHP >= 5.1.0
 * @author Marc Bredt
 */
class UnknownClassException extends Exception {

  /**
   * Create an UnknownClassException
   * @param $message default exception message
   * @param $code default exception code
   * @param $previous exception thrown before
   */
  public function __construct($message = "", $code = 0,
                              Exception $previous = null) {
    parent::__construct("Class '".$message."' not found.",$code,$previous);
  }

  /**
   * Get a formatted exception message.
   * @return formatted exception string
   */
  public function __toString() {
    return __CLASS__." [".$this->code."]: { ".$this->message." }";
  }

}
