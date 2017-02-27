<?php

namespace core\session;

use core\util\param\Validator as Validator;
use core\session\Cookie as Cookie;
use core\exception\auth\CookieException as CookieException;

/**
 * This class is used to set cookies.
 * @author Marc Bredt
 */
class Cookie {

  /**
   * Sets a cookie if it is not available.
   * @param $name cookie name
   * @param $value cookie value
   */
  public function set($name = "", $value = "") {

    global $filelogger;
 
    // if $name and $value are non empty strings and are currently not set
    if(Validator::isa($name,"string") && Validator::isa($value,"string")
       && !Validator::isempty($name) && !Validator::isempty($value)
       && !$this->avail($name)) {

      // set the cookie, will be verified upon request verification
      setcookie($name, $value, time()+intval(COOKIE_LIFETIME),
               COOKIE_PATH, COOKIE_DOMAIN, true, true);
      $filelogger->log("setting cookie - cname=%, cvalue=%",
                       array($name,$value),"INFO");

    // if a cookie was already set log a notice
    } else if(Validator::isa($name,"string") && !Validator::isempty($name)
              && $this->avail($name)) {
   
      $filelogger->log("found cookie - cname=%, cvalue=%, \$_COOKIE[\"%\"]=%",
                       array($name,$value,$name,$_COOKIE[$name]),
                       "NOTICE");

    // otherwise log/throw an exception
    } else {
      $filelogger->log("%, rejecting cookie - cname=%, cvalue=%",
                       array(new CookieException(),$name,$value),
                       "ERR");
      throw(new CookieException());
    }

  }

  /**
   * Get a cookie value.
   * @param $name cookie name
   * @return string value for a cookie name set, otherwise empty string
   */
  public function get($name = "") {
    return ((Validator::isa($name,"string") && !Validator::isempty($name)
             && $this->avail($name)) ? $_COOKIE[$name] : "");
  }

  /**
   * Checks if a cookie value is already set.
   * @param $name cookie name
   * @return true if a cookie name is available in $_COOKIE, otherwise false
   */
  private function avail($name = "") {
    return Validator::in($name,array_keys($_COOKIE));
  }

}

?>
