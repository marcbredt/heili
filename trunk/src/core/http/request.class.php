<?php

namespace core\http;
use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\string\StringUtil as StringUtil;
use core\html\form\FormEvaluator as FormEvaluator;
use core\exception\http\RequestException as RequestException;

/**
 * This class models the request sent to the application.
 * It is primarly used to validate requests using apache_request_headers().
 * @see apache_request_headers()
 * @author Marc Bredt
 */
class Request {

  /**
   * Request method.
   */
  private $method = null;

  /**
   * Request URI.
   */
  private $uri = null;

  /**
   * Protocol used and its version.
   */
  private $protocol = null;

  /**
   * Headers received.
   */
  private $headers = null;

  /**
   * Stores the main http request configuration.
   */
  private $xmldoc = null;

  /**
   * Marks any valid requests.
   */
  private $valid = true;

  /**
   * Load the main http request config into an XMLDocument.
   */
  public function __construct() {

    // set method
    $this->method = $_SERVER["REQUEST_METHOD"];

    // set uri
    $this->method = $_SERVER["REQUEST_URI"];

    // set protocol
    $this->protocol = $_SERVER["SERVER_PROTOCOL"];

    // set headers via apache_request_headers() or HTTP_* vals
    if(function_exists("apache_request_headers")) 
      $this->headers = apache_request_headers();
    else 
      $this->headers = $this->get_request_headers();

    // set headers configuration to follow $this->headers to
    $this->xmldoc = new XMLDocument(PATH_CONF."/http/request/headers.xml",
                                    PATH_DTD."/http/request/headers.dtd", true);
  }

  /**
   * Alternative function to get request headers sent.
   * @return an array containing request headers sent  
   */
  private function get_request_headers() {
    return array_intersect_key(
             $_SERVER,
             array_flip(preg_grep("/^HTTP_.+$/", array_keys($_SERVER)))
           );
  }

  /**
   * Validatate superglobal variables against the definitions mentioned in 
   * the main http request configuration.
   * @return false if any keys in any superglobals were discarded, otherwise true
   * @throws RequestException
   * @see <a href="https://www.ietf.org/rfc/rfc2616.txt" _target="blank">
   *      RFC 2616</a>
   */
  public function validate() {
  
    return $this->valid;

  }

}

?>
