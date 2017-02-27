<?php

namespace core\exception\handler;

use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\string\StringUtil as StringUtil;

use core\mask\MaskLoader as MaskLoader;
use core\layout\Template as Template;

use core\exception\xml\XMLNotValidException as XMLNotValidException;
use core\exception\xml\XMLNoValidDTDException as XMLNoValidDTDException;
use core\exception\xml\xpath\UnresolvedXPathException as UnresolvedXPathException;
use core\exception\xml\xpath\InvalidXPathExpressionException  
    as InvalidXPathExpressionException;

/**
 * This class is used to handle any exception thrown.
 * @author Marc Bredt
 */
class ExceptionHandler {

  /**
   * Stores global exceptions.
   */
  private $globals = null;

  /**
   * Stores local exceptions.
   */
  private $locals = null;

  /**
   * Stores the exception.
   */
  private $exception = null;

  /**
   * Stores the exception type.
   */
  private $exception_type = null;

  /**
   * Strores the mask causing the "previous" local exception.
   */
  private $mask = null;

  /**
   * Mask data sent causing local exceptions.
   */
  private $mask_data = null;
  
  /**
   * Register the exception handler and load exception classifications.
   */
  public function __construct() {
   
    // initialize the classification arrays
    $this->globals = array();
    $this->locals = array();

    // set the exception
    $this->load_exceptions();
 
    // set the default exception handler
    set_exception_handler(array($this,'handle'));
  }

  /**
   * Exception handling function. Sets $_SESSION["error"] for further
   * procession after redirection.
   * @param $exception exception thrown
   */
  public function handle($exception = null) {

    // TODO: pass mask data as well
    // TODO: check errors thrown inside the handler

    $this->set_exception($exception);
    $this->set_exception_type();

    // handle the error using a template
    $this->handle_exception();

  }

  /**
   * Store the exception raised. 
   * @param $exception exception raised
   */
  private function set_exception($exception = null) {
   $this->exception = $exception;
  }

  /**
   * Set the exception type depending on the exception classifiers $this->globals
   * and $this->locals. Any other and undefined exception not set in $this->locals 
   * is a global one by default.
   */
  private function set_exception_type() {
    
    if(Validator::in(get_class($this->exception),$this->globals) 
       || (!Validator::in(get_class($this->exception),$this->globals) 
           && !Validator::in(get_class($this->exception),$this->locals))) { 
      $this->exception_type = "global";
    } else {
      $this->exception_type = "local";
    }
  }

  /**
   * Initialize the exception arrays $this->globals and $this->locals.
   */
  private function load_exceptions() {

    global $filelogger;
    $xtypes = new XMLDocument("../conf/data/exception.xml",
                              "../conf/dtd/data/exception.dtd");
    foreach($xtypes->xpath("//global/*/@name",false,true) as $a) 
      $gamount = array_push($this->globals,$a->value);
    foreach($xtypes->xpath("//locals/*/@name",false,true) as $a)
      $lamount = array_push($this->locals,$a->value);
    if(!Validator::isa($filelogger,"null"))
      $filelogger->log("#globals = %, globals = [ % ], #locals = %, locals = [ % ]",
                       array($gamount,$globals,$lamount,$locals),"DEBUG");

  }

  /**
   * Handle any exception like global or local ones.
   */
  private function handle_exception() {

    // handle local exceptions
    switch($this->exception_type) {
      case "local": $this->handle_local_exception(); break;
      case "global": $this->handle_global_exception(); break;
      default: $this->handle_unknown_exception(); break;
    }

  }

  /**
   * This method creates a global exception using the error template provided
   * by the template container currently choosen.
   */
  public function handle_global_exception() {

    global $filelogger, $session, $template;

    // build from error template
    if(!Validator::isa($filelogger,"null")) $filelogger->info("handling global error");

    if(!Validator::isa($session,"null") && $session->has("template")) 
      $tname = $session->get("template");
    else if(!Validator::isa(@constant("TEMPLATE_DEFAULT"),"null")) 
      $tname = TEMPLATE_DEFAULT;
    else 
      $tname = "heili";

    // setup the error template
    $template = new Template($tname,"error");

    // build message string
    $message = "\n\n".$this->get_error_message($this->get_eid())."&lt;br/&gt;".
               $this->get_trace_message();
 
    // and pin it to the error node
    $template->add($message,
                   $template->get_map()->get("c_gerror")->get_target(),
                   $template->get_map()->get("c_gerror")->get_index());

    // remove error from the session if some was set before
    if(!Validator::isa($session,"null") && $session->has("error"))
      $session->set("error",null,array());
    session_write_close();

    // print the modified (global) error template and submit it to the client
    echo $template->get();
    ob_flush();

  }
  
  /**
   * Handle local exception. Local exceptions are by definition those that will
   * be thrown if invalid input data was passed. Sanitized input data will be 
   * redirected to PHP_SELF after storing the exception into the session. 
   * Using the session to store "previous" exceptions allows masks to restore
   * exception information on data passed using ::handle_previous_local_exception().
   * 
   * TODO: Currently the local exception restore is done right before the script
   *       echoes the final template built. This need to be done by any mask
   *       laterwards when data handling is completed.
   */
  public function handle_local_exception() {

    // store the local exception into the session
    // bind the causal mask to the exception stored as well
    // redirect to PHP_SELF with sanitized data

  }

  /**
   * Try to log unknown exception types.
   */
  private function handle_unknown_exception() {
    global $filelogger;
    if(!Validator::isa($filelogger,"null")) 
      $filelogger->log("Invalid exception type '%'",array($this->exception_type),
                       "ALERT");
  }

  public function handle_previous_local_exception() {
  
    // get exception data from session
    // setup error node in the template

  }

 
  public function handle2($e = null) {

    // make the language object available to the exception handler 
    global $language, $filelogger, $sessionhandler, $session;

    // TODO: bind global and local exception definition to a config
    // distinction of exception types, global vs local, e.g. wrong login 
    // credentials is kind of local exception which should be displayed in
    // layout frame context whereas pdo or xml exceptions cause the complete
    // application to fail and should therefor displayed in a global way
    // extend these arrays to control the exception display method
    $globals = array(
                 "PDOException",
                 "core\\exception\\http\\RequestException",
                 "core\\exception\\xml\\XMLNotValidException"
               );
    $locals = array(              
                "core\\exception\\auth\\SessionException",
                "core\\exception\\auth\\AuthException",
                "core\\exception\\mask\\MaskException"
              );

    // TODO: exception handling for exceptions thrown inside the handling
    //       mechanism

    // TODO: two states implementation, if $session is null global exceptions
    //       only and if $session exists the template template node exists local
    //       ones as well
    // initialize a session if none has been created yet
    if(Validator::isa($sessionhandler,"null") && Validator::isa($session,"null")) {
      include("../incs/session.include.php");
      $session->set("error", "type", "global");
      $session->set("error", "state", "unhandled");
    }

    $filelogger->log("e=%, isexc?=%, isemptyerror?=%, session=%", 
                     array($e, Validator::isclass($e,"Exception"), 
                           Validator::isempty($session->get("error")),
                           $session),
                     (Validator::isa($e,"null") ? "INFO" : "ERR"));

    // only handle that exception if there wasn't an exception thrown yet
    // this avoids infinite redirects and overriding exceptions
    if(!Validator::isa($e,"null") 
       && Validator::isclass($e,"Exception")
       && Validator::isempty($session->get("error"))) {

      // any unknown or "global" exception needs be displayed globally
      if(Validator::in(get_class($e),$globals) 
         || (!Validator::in(get_class($e),$globals) 
             && !Validator::in(get_class($e),$locals))) { 
        $session->set("error", "type", "global");
      } else {
        $session->set("error", "type", "local");
      }

      $filelogger->log("XXXXX-**1 %",array($session),"DEBUG"); 

      // set "trace" for specific exceptions like pdo but not for credentials
      if(Validator::equals($session->get("error","type"),"global")) {
        $session->set("error", "trace", true);
      }

      $filelogger->log("XXXXX-**2 %",array($session),"DEBUG"); 

      // header already sent warning/exception is thrown
      $filelogger->log("XXXXX-**3 obstat=%, %",
                       array(StringUtil::get_object_string(ob_get_status()),
                             $session),"DEBUG");
      if(!Validator::isempty(ob_get_status())) ob_clean(); 

      $filelogger->log("XXXXX-**4 %",array($session),"DEBUG");

      // handling global errors by using the error template and flush the error set
      if(Validator::equals($session->get("error","type"),"global")) {

        $filelogger->log("handling global error ...",array(),"INFO");
        if($session->has("template")) $template_name = $session->get("template");
        else $template_name = TEMPLATE_DEFAULT;
        $template = new Template($template_name,"error",true);
        $session->set("error",null,array()); // unset error
        session_write_close(); // remove error from the session 
        echo $template->get();
        ob_flush();

      } else if(Validator::equals($session->get("error","type"),"local")) {

        // redirect to a clean state in case of local errors but beware of loops 
        // when an error always occurs e.g. when the database is down
        // TODO: validate response
        $filelogger->log("redirecting local error ...",array(),"INFO");
        header("Location: /");

      }
 
    // print a global error warning at this point to be able to show exceptions
    // at include level as well
    } else if(!Validator::isempty($session->get("error")) 
              && Validator::equals($session->get("error","type"),"global")
              && Validator::equals($session->get("error","state"),"unhandled")){
   
      $this->handle_global_error(); 

      // invoke current session data to be written
      session_write_close(); 

    } else if(Validator::equals($session->get("error","state"),"handled")) {

      $session->set("error",null,array());

      // invoke current session data to be written
      session_write_close(); 

    }

  }

  /** 
   * Get the error id of an exception. Defaults to "0000".
   * @return error id of $this->e if it is an exception and has a class variable 
   *         named "eid" set, otherwise "0000"
   */
  private function get_eid() {
    return (Validator::isexception($this->exception) 
            ? (array_key_exists("eid",get_class_vars(
                 get_class($this->exception))) 
               ? $this->exception->eid : "0000")
            : "0000");
  }

  /**
   * Get the error message from XML by ID.
   * @param $eid error id pinned onto an exception, defaults to "0000"
   * @return string containing error message for the corresponding id
   *                on failures a predefined error message is returned
   */
  private function get_error_message() {

    global $filelogger, $session;

    // get and check the exception's error id
    $eid = $this->get_eid($this->exception);
    if(!Validator::matches($eid,"/[0-9]{4}/")) $eid = "0000";

    // try to get the error message configured
    try {

      // determine session and default language
      $ls = (!Validator::isa($session,"null") && $session->has("language") 
             ? $session->get("language") : "");
      $ld = (!Validator::isa(@constant("LANG_DEFAULT"),"null") 
             ? LANG_DEFAULT : "en");
      
      // get the error message
      $x = new XMLDocument(PATH_CONF."/data/errors.xml",
                            PATH_DTD."/data/errors.dtd",true);
      $xp = "string(//error[@eid=\"".$eid."\"]/".
            (!Validator::isempty($ls) ? $ls : $ld).")";
      if(!Validator::isa($filelogger,"null")) 
        $filelogger->debug("sesslang=%, deflang=%, xp=%",array($ls,$ld,$xp));
      
      return $x->xpath($xp);

    // in case something went wrong getting the message from xml e.g. due to
    // validation errors or similar
    } catch(XMLNotValidException $e) {
    } catch(XMLNoValidDTDException $e) {
    } catch(InvalidXPathExpressionException $e) {
    } catch(UnresolvedXPathException $e) {
    }

    if(!Validator::isa($filelogger,"null")) 
      $filelogger->err("Getting error message for id=% failed.",array($eid));

    // if getting the error message failed use a default one
    return "Sorry. This should not have happened.&lt;br/&gt;\n";

  }

  /**
   * Get the trace message.
   * @param $e an exception
   * @return string containing the exception trace message.
   */
  private function get_trace_message() {

    global $language, $filelogger, $session;

    $msg = "";

    // get the exception's error id 
    $eid = $this->get_eid();
    if(!Validator::isa($language,"null"))
      $filelogger->debug("ise=%, ls=%, eid=%", 
                         array(Validator::isexception($this->exception),
                               intval(isset($language)), $eid));

    // if the exception is an object/class of the desired type
    if(Validator::isexception($this->exception) 
       && !Validator::isa($language,"null")) {

      $msg = "\n".$language->get("error")." (".$eid."): ".
               get_class($this->exception)."&lt;br/&gt;". 
             (!Validator::isa($session,null) 
              ? "\n".$language->get("sessionid").": ".$session->get_sid()."&lt;br/&gt;" 
              : "").
             "\n".$language->get("trace").": ".
               preg_replace("/#/", "&lt;br/&gt;&nbsp;&nbsp;&nbsp;#", 
                            $this->exception->getTraceAsString());

    } else if(Validator::isexception($this->exception) 
              && Validator::isa($language,"null")) {

      $msg = "\nError (".$eid."): ".get_class($this->exception)."&lt;br/&gt;". 
             (!Validator::isa($session,null) 
              ? "\nSession-ID: ".$session->get_sid()."&lt;br/&gt;" : "").
             "\nStack-Trace: ".
               preg_replace("/#/", "&lt;br/&gt;&nbsp;&nbsp;&nbsp;#", 
                            $this->exception->getTraceAsString());

    // if the exception is not an object/class avoid appending a stack trace
    } else if(!Validator::isexception($this->exception) 
              && !Validator::isa($language,"null")) {

      $msg = "\n".$language->get("error")." (".$eid."): ".
               gettype($this->exception)."&lt;br/&gt;". 
             (!Validator::isa($session,null) 
              ? "\n".$language->get("sessionid").": ".$session->get_sid()."&lt;br/&gt;"
              : "");

    } else if(!Validator::isexception($this->exception) 
              && Validator::isa($language,"null")) {

      $msg = "\nError (".$eid."): ".gettype($this->exception)."&lt;br/&gt;". 
             (!Validator::isa($session,null) 
              ? "\nSession-ID: ".$session->get_sid()."&lt;br/&gt;" : "");
    }

    // black some (critical( values traces
    $msg = $this->black_trace($msg);

    return $msg;

  }

  /**
   * Black some critical traces. E.g. a trace of a PDOException would contain
   * database credentials if constructing fails when the database service is 
   * not available. Extend this function if there is need to black some more
   * traces or messages.
   * @param $msg string representing the current messege
   * @return string representing the blacked message
   */
  private function black_trace($msg = "") {

    // TODO: access $_SERVER through filter/sanitizer/validator
 
    // docroot setup
    $docroot = substr($_SERVER["DOCUMENT_ROOT"],0,
                      strrpos($_SERVER["DOCUMENT_ROOT"],"/"));
    $docroot = preg_replace("/\//","\\/", $docroot);

    $rs = array(
            array("/".$docroot."\\//",""),
            array("/PDO->__construct\\(.*\\)/","PDO->__construct()"),
            array("/include\\(.*\\)/","include()")
          );

    foreach($rs as $r) $msg = preg_replace($r[0],$r[1],$msg);

    return $msg; 
  }

}

?>
