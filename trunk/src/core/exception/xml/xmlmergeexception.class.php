<?php

namespace core\exception\xml;
use \Exception as Exception;

/**
 * This class describes exceptions which can occur using
 * XMLDocument.
 * @req PHP >= 5.1.0
 * @author Marc Bredt
 */
class XMLMergeException extends Exception {

  /**
   * This constant indicates the merging party is not avalid XMLDocument
   */
  const XML_MERGE_INVALID_DOC = 0;

  /** 
   * Create a XMLMergeException.
   * @param $message defaut exception message
   * @param $code reason for the exception raised
   * @param $previous previous exception causing this exception
   *                  to be raised.
   */
  public function __construct($message = "", $code = 0, 
                              Exception $previous = null) {

    $message_prefix = "Merging XML documents failed";

    switch($code) {

      case 0:
        parent::__construct($message_prefix." (".$message.", XML_MERGE_INVALID_DOC).",
                            self::XML_MERGE_INVALID_DOC, $previous);
        break;
     
      default: 
        parent::__construct($message_prefix." (".$message.", XML_MERGE_INVALID_DOC).",
                            self::XML_MERGE_INVALID_DOC, $previous);
        break;

    }

  }

  /**
   * Get message and stack trace for this exception.
   + @return exception message and stack trace
   */
  public function __toString() {
    return __CLASS__." [".$this->code."]: { ".$this->message." }";
  }

}

?>
