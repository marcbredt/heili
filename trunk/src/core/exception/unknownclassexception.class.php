<?php

namespace core\exception;

/**
 * Exception to be thrown whenever a class could not be loaded.
 * @author Marc Bredt
 */
class UnknownClassException extends Exception {

  /**
   * Create an UnknownClassException
   * @param $message default exception message
   * @param $code default exception code
   * @param $previous exception thrown before
   */
  public function __construct($message = "Class not found", $code = 0,
                              Exception $previous = null) {
    parent::__construct($message,$code,$previous);
  }

  /**
   * Get a formatted exception message.
   * @return formatted exception string
   */
  public function __toString() {
    return __CLASS__." [".$this->code."]: { ".$this->message." }";
  }

}
