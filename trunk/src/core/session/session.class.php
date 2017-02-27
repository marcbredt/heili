<?php

namespace core\session;
use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\string\StringUtil as StringUtil;
use core\util\file\File as File;
use core\object\Validatable as Validatable;
use core\session\token\Tokenizer as Tokenizer;
use core\exception\handler\ExceptionHandler as ExceptionHandler;
use core\exception\auth\SessionException as SessionException;

/**
 * This class is mainly used to access $_SESSION.
 * @author Marc Bredt
 */
class Session implements Validatable {

  /**
   * The session name set.
   */
  private $name = "core:pid";

  /**
   * Stores the session id set.
   */ 
  private $sid  = "";

  /**
   * Stores valid keys for $_SESSION. Read from session.xml
   */
  private $keys = null;

  /**
   * Stores an integer status of the session.
   * Known values are currently
   * 0 - invalid parameters
   * 1 - restore failed
   */
  private $valid = -1;

  /**
   * Create a session.
   *
   * Sets the session name and a session id following the specified layout 
   * "/^[a-z0-9]{32}$/".
   * 
   * TODO: Filter, sanitize and validate $_COOKIE
   *
   * NOTE: reinitializing if the session id does not exist but assuming it exists
   *       and resetting it if not found would provide the ability to get to know
   *       about present session ids
   *
   * NOTE: start a session to get SessionHandler::open() invoked at least once for
   *       a session id with a specific layout "/^[a-z0-9]{32}$/"
   *
   * NOTE: http://stackoverflow.com/questions/37622187
   *       any client $_COOKIE value with semicolons will be split to additional
   *       superglobal values if a section contains a "=" otherwise the data
   *       will be ommitted. This is in short a double explosion for ";" and "="
   *
   * @param $name session name
   */
  public function __construct($name = "core:pid") {

    global $filelogger, $session;

    // set the session name
    $this->set_name($name);

    $csid = (Validator::in($this->get_name(),array_keys($_COOKIE))
             ? $_COOKIE[$this->get_name()] : null);
    $filelogger->log("session = %, csid = %, cookie = %", 
                     array($this,$csid,$_COOKIE),"DEBUG");
   
    // if the session cookie is not set initiate one
    if (Validator::isa($csid,"null")){

      $this->set_sid(Tokenizer::get());

      // log a notice when starting an unintialized session
      $filelogger->log("started session \$_COOKIE = [ % ], \$_SESSION = [ % ]",
                       array(StringUtil::get_object_string($_COOKIE),$this), 
                       "NOTICE");

    // or set the session id from valid cookies passed
    } else if(
        !Validator::isa($csid,"null")
        && Validator::isa($csid,"string")
        && Validator::matches($csid,"/^[a-z0-9]{32}$/")) {

      $this->set_sid($csid);

      // log a note when continuing a session
      $filelogger->log("continuing session \$_COOKIE = [ % ], \$_SESSION = [ % ]",
                       array(StringUtil::get_object_string($_COOKIE),$this), 
                       "NOTICE");

    // or invoke the tokenizer if a weird session id was passed via cookies
    } else if(
        !Validator::isa($csid,"null")
        && (!Validator::isa($csid,"string")
            || !Validator::matches($csid,"/^[a-z0-9]{32}$/"))){

      $this->valid = 0;
      // log a session exception
      $filelogger->log("%, sn=%, \$_COOKIE = [ % ] \$_SESSION = [ % ]",
                       array(
                         new SessionException("invalid parameters"),
                         $this->get_name(),
                         StringUtil::get_object_string($_COOKIE),
                         $this,
                       ),
                       "ALERT");
      
    // otherwise set a random one
    } else {

      $this->set_sid(Tokenizer::get());

      // log a warning to recognize unhandled states
      $filelogger->log("%, sn=%, \$_COOKIE = [ % ] \$_SESSION = [ % ]",
                       array($_COOKIE,$this),"WARNING");

    }

    // initialize the session
    if($this->valid<0) {
      $this->init();
      $filelogger->log("initialized session = %", array($this), "INFO");
    }
  
  }

  /**
   * Initialize the $_SESSION with empty keys needed read from session.xml.
   *
   * NOTE: reinitializing if the session id does not exist would provide the
   *       ability to get to know about present session ids
   */
  public function init() {

    $this->start();
    // load valid keys, e.g. for tokens, user, ...
    $this->keys = array();
    $this->load();

  }

  /**
   * Restore session data from session storage accessible via SessionHandler.
   * @see core\session\handler\SessionHandler
   */
  public function restore() {

    global $filelogger, $sessionhandler;

    // decode the session if a valid session id is available
    if(!Validator::isempty($this->get_sid())) {

      $fn = $sessionhandler->get_save_path()."/sess_".$this->get_sid();
      $f = new File($fn);
      $fr = $f->read();
      $r = session_decode($fr);
      $filelogger->log("%, %, %", array($f,$fr,$this), "DEBUG");
 
      // set an validation code for this session if decoding failed
      if(Validator::equals($r,false)) {
        $filelogger->log("%",array(new SessionException("restore failed",1)),"ALERT");
        $this->valid = 1;
      }

    }

  }

  /**
   * Checks if a key or subkey is set in $_SESSION. 
   * @param $name main key in $_SESSION
   * @param $key subkey in $_SESSION[$name]
   * @return true if $_SESSION[$name]/$_SESSION[$name][$key] is set,
   *         otherwise false
   */
  public function has($name = "", $key = "") {

    if(Validator::isa($name,"string") && Validator::isa($key, "string") 
       && !Validator::isempty($name) && !Validator::isempty($key) 
       && $this->isvalid($name))
      return isset($_SESSION[$name][$key]);
    
    if(Validator::isa($name, "string") && !Validator::isempty($name) 
       && $this->isvalid($name))
      return isset($_SESSION[$name]);

    return false;
  }

  /**
   * Get the $_SESSION[$name] or $_SESSION[$name][$key] respectively.
   * @param $name main key in $_SESSION
   * @param $key subkey in $_SESSION[$name]
   * @return $_SESSION element set, otherwise NULL
   */
  public function get($name = "", $key = "") {

    if($this->has($name)) {

      if (isset($_SESSION[$name][$key])) 
        return $_SESSION[$name][$key];
  
      else if(isset($_SESSION[$name])) 
        return $_SESSION[$name];

      else return null;

    }
   
    return null;

  }

  /**
   * Set an element in $_SESSION.
   * @param $name main key in $_SESSION
   * @param $key subkey in $_SESSION[$name]
   */
  public function set($name = "", $key = null, $value = "") {
    if(Validator::isa($name,"string")) {
      if(Validator::isa($key,"string")) $_SESSION[$name][$key] = $value;
      else $_SESSION[$name] = $value;
    }
  }

  /**
   * Unset an element in $_SESSION.
   * @param $name main key in $_SESSION
   * @param $key subkey in $_SESSION[$name]
   */
  public function uset($name = "", $key = null) {
    if(Validator::isa($name,"string")) {
      if(Validator::isa($key,"string")) unset($_SESSION[$name][$key]);
      else unset($_SESSION[$name]);
    }
  }

  /**
   * Initialize $_SESSION with keys needed. Read from session.xml
   */
  private function load($xml = "../conf/data/session.xml", 
                        $dtd = "../conf/dtd/data/session.dtd") {

    global $filelogger;
    
    $x = new XMLDocument($xml, $dtd, true);
    $xs = $x->xpath("//session/key",true);
    $filelogger->log("xs=%", array($xs));

    foreach($xs->get_doc()->documentElement->childNodes as $n) {

      if(Validator::isclass($n, "DOMElement") 
         && !Validator::in($n->getAttribute("name"), $this->keys)) {

        $this->keys = array_merge($this->keys, array($n->getAttribute("name")));

        // only initialize session variables if they are not set yet
        if(!isset($_SESSION[$n->getAttribute("name")])) {
 
          $filelogger->log("\$_SESSION[ % ]", 
                     array(StringUtil::get_object_string($_SESSION)));

          if(Validator::equals($n->getAttribute("type"),"array"))
            $this->set($n->getAttribute("name"),null,array());
          else if(Validator::equals($n->getAttribute("type"),"string"))
            $this->set($n->getAttribute("name"));

        }

      }

    }

  }

  /**
   * Checks if a main key is valid for $_SESSION. Corresponds to $this->keys
   * set up in $this->load which were read from session.xml.
   * @param $name main key in $_SESSION
   * @return true if key is allowed by definition, otherwise false   
   */
  private function isvalid($name = "") {
    if(Validator::isa($name,"string")) 
      return Validator::in($name, $this->keys);
    return false;
  }

  /**
   * Validate the session. Check if there is need for exceptions to be thrown.
   */
  public function validate() {

    global $filelogger;
    
    switch($this->valid) {

      case 0: 
          throw(new SessionException("invalid parameters"));
        break;

      case 1: 
          throw(new SessionException("restore failed",1));
        break;

      default: break;

    }

  }

  /**
   * Dump information on this object.
   * @return string representing this session object
   */
  public function __toString() {

    if(isset($_SESSION)) $session = $_SESSION;
    else $session = array();
    return get_class($this).spl_object_hash($this)."=( ".
           "name = '".$this->name."', ".
           "sid = '".$this->sid."', ".
           "valid = '".intval($this->valid)."', ".
           "keys = [ ".StringUtil::get_object_string($this->keys)." ], ".
           "\$_SESSION = [ ".StringUtil::get_object_string($session)." ] ".
           " )";
  }

  /**
   * Start a session.
   */
  public function start() {
    session_start();
  }

  public function set_name($name = "core:pid") {
    $this->name = $name;
    session_name($this->name);
  }

  public function get_name() {
    return $this->name;
  }

  public function set_sid($sid = "") {
    $this->sid = $sid;
    session_id($this->sid);
  }

  public function get_sid() {
    return $this->sid;
  }

}

?>
