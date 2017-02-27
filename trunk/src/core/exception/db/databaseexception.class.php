<?php

namespace core\exception\db;
use \Exception as Exception;

/**
 * Class used to throw an exception in case of database errors.
 * @author Marc Bredt
 */
class DatabaseException extends Exception {

  const DB_CONNECTION_INVALID = 0;

  const DB_PARAMS_INVALID = 1;

  const DB_SETS_AMOUNTS = 2;

  const DB_PSTMT_INVALID = 3;

  const DB_PSTMT_BATCH_INVALID = 4;

  const DB_INVLAID_DRIVER = 5;

  public $eid = "0003";

  /**
   * Create a DatabaseException.
   * @param $message additional message to be passed.
   * @param $code code to differentiate equal type of exceptions
   * @param $previous exception previously raised
   */
  public function __construct($message = "", 
                              $code = 0, Exception $previous = null) {

    $message_prefix = "Database error occured (".$message.",";
    
    switch($code) {
      case 0: parent::__construct($message_prefix." DB_CONNECTION_INVALID)", 
                                  $code, $previous); break;
      case 1: parent::__construct($message_prefix." DB_PARAMS_INVALID)", 
                                  $code, $previous); break;
      case 2: parent::__construct($message_prefix." DB_SETS_AMOUNTS)", 
                                  $code, $previous); break;
      case 3: parent::__construct($message_prefix." DB_PSTMT_INVALID)", 
                                  $code, $previous); break;
      case 4: parent::__construct($message_prefix." DB_PSTMT_BATCH_INVALID)", 
                                  $code, $previous); break;
      case 5: parent::__construct($message_prefix." DB_INVALID_DRIVER)", 
                                  $code, $previous); break;
      default: parent::__construct($message_prefix." DB_CONNECTION_INVALID)", 
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
