<?php

/**
 * This class is used to log any relevant application stuff.
 * @author Marc Bredt
 * @see http://stackoverflow.com/questions/32478962
 */
namespace core\util\log;
use core\util\param\Validator as Validator;
use core\util\file\File as File;
use core\util\string\StringUtil as StringUtil;
use core\exception\param\ParamNumberException as ParamNumberException;
use core\exception\param\ParamNotArrayException as ParamNotArrayException;

/**
 * This class wraps the Log class from package php-log
 * @author Marc Bredt
 * @package Logger
 */
class FileLogger {

  /**
   * Stores the name of the class the logger is generated for.
   */ 
  private $class = null;

  /**
   * Stores the file name of the file the logs are written to.
   */ 
  private $file = null;

  /**
   * Log level order.
   */
  private $order = array(
                         "EMERG", "CRIT",    "ALERT",
                         "ERR",   "WARNING", "NOTICE",
                         "INFO",  "DEBUG"
                        );

  /**
   * Terminal color codes wich can will be appended to the corresponding log
   * log command if $colored is set to true. Theese codes will be interpreted
   * running e.g. 
   * <code>
   *   $ less -r +F colored.log
   *   $ tail -f colored.log
   * </code>.
   * The last entry is always used to restore the default color. All other
   * entry indexes in $order refer to the same index in $color.
   */
  private $colors = array(
                          "[1;5;7;31m", "[1;7;31m", "[1;2;31m",
                          "[1;31m",     "[1;2;33m", "[1;36m",
                          "[1;2;32m",   "[1;30m",
                          "[0m"
                         );

  /**
   * Flag to enable raw terminal color codes.
   */
  private $color = false;

  /**
   * Log level. Restrict/increase output in productive/test environments.
   */
  private $level = "INFO";

  /**
   * Contains the configuration for the logger.
   */ 
  private $conf = array(
                    "rights"	=> 0660,
                    "mode"	=> "a+",
                    "dformat"	=> "m/d/Y H:i:s.u"
                  );

  /**
   * Store if caller info was already resolved, e.g. via the other log methods.
   */
  private $cresolved = false;

  /**
   * Construct a file logger for a specified class that is accessable.
   * @param string $class Classname the logger is created for.
   * @param string $sid session id, used to track a specific session in the logs
   * @param string $file file to log too.
   */ 
  function __construct($class = 'Unknown', $file = "../../log/heili.log") {

    $this->class = $class;
    $this->file = new File($file);
    $opened = $this->file->open($this->conf["mode"]);

  }  

  /**
   * Get the file object.
   * @return File
   * @see File
   */
  public function getf() {
    return $this->file;
  }

  /**
   * Get a string representation for a FileLogger instance.
   * @return string FileLogger representation
   */
  public function __toString() {
    return get_class($this).spl_object_hash($this)."=( ".
             "file=".$this->file.", ".
             "class=".$this->class.", ".
             "conf=".preg_replace("/( |\r|\n|\t)+/", 
                                  " ", var_export($this->conf,true))." )";
  }

  /**
   * Function to flat several objects for logging. Arrays will be flattened.
   * For other objects there will be looked after a class method named 
   * __toString(). If no such method exists the object signature will be used.
   * @param object $obj object to flatte for logging.
   * @param boolean $debug indicates to get detailed information for $obj
   * @return string flattened object, its string representation or a unique id
   */
  public function flatten($obj = null, $debug = false) {
    $a = array();
    // NOTE: get_class_methods invokes autoloader for a string value
    if(Validator::isa($obj,"object") 
       && Validator::isa(get_class_methods($obj),"array")) {
      $a = get_class_methods($obj);
    }

    $ret = ""; 
    if(Validator::in("__toString",$a)) {
      $ret = $obj->__toString();
    } else if(Validator::isa($obj,"array",5)) {
      $ret = StringUtil::get_object_string($obj);
    } else if(Validator::isa($obj,"string")) {
      $ret = $obj;
    } else if(Validator::isa($obj,"integer")) {
      $ret = intval($obj);
    } else if(Validator::isa($obj,"boolean")) {
      $ret = (intval($obj)==0 ? "false" : "true");
    } else if(Validator::isa($obj,"null")) {
      $ret = "null";
    } else if(Validator::isa($obj,"object")){
      $ret = get_class($obj).spl_object_hash($obj); 
    }

    if($debug===true && Validator::isa($obj,"object")) 
      $ret = "OBJECT(".StringUtil::get_object_string($obj).")=".$ret;

    // replace multiple whitespaces by a single one 
    return $ret;
  }

  /**
   * This function wraps some checks around the main logging function to be able
   * to log e.g. arrays without throwing conversion errors.
   * <pre>
   *  $l->log("eins=%, zwei=%, drei=%, vier=%", 
   *            array("dsgsg",array(2,44),new Set(),array(array(1,2),3)), "DEBUG");
   * </pre>
   * @param string $msg Message string like 'Convert % to loggable string.'
   * @param string $type the type of the message, like e.g. 
   *                     debug, info, notice, warning, err, crit, alert, emerg
   * @param array $replaces contains replacement (any type f object) 
   *                        for each % placeholder in $msg
   * @return string flattened/detailes object information for $obj
   * @throws ParamNotArrayException
   */
  public function log($msg = "", $replaces = array(), $type = null) {

    $toreps = preg_match_all("/%/", $msg);
 
    // replaces have to be an array 
    if(!Validator::isa($replaces,"array")) { 

      $this->write(get_class($this)." line ".__LINE__.
                         ": Message replacements are not provided as an array.",
                         "WARNING");
      throw(new ParamNotArrayException());

    // number of replacement elements should not differ 
    // in msg string and replacement array
    } else if($toreps != count($replaces)) {

      $this->write(get_class($this)." line ".__LINE__.
                         ": Number of message replacements do not fit.", 
                         "WARNING");
      throw(new ParamNumberException());

    } else if($toreps == count($replaces)) {

      // replace % with flattened replacements
      foreach($replaces as $r) {
        if($type === "DEBUG") 
          $msg = preg_replace("/%/", $this->flatten($r,true), $msg, 1);
        else 
          $msg = preg_replace("/%/", $this->flatten($r), $msg, 1);
      }      
    } 
   
    // prefix message with file, object, function and line information 
    if(!$this->cresolved) $cinfo = $this->get_caller_info(debug_backtrace());
    else $cinfo = "";
    $this->write($cinfo.$msg, ($type===null ? $this->level : $type));
    $this->cresolved = false;

  }

  /**
   * Get information on the caller which is the one the ::log() function is
   * called from directly. 
   *
   * There are three different types of calls
   * - direct calls to the logger from scripts right after initiating the logger
   * - function calls from functions defined inside scripts
   * - direct and extended object calls
   * 
   * For direct calls the number of elements in debug_backtrace() is 1.
   * In this case the value of key "file", "function" and "line" of element 0 
   * is returned.
   * <pre>basename($a[0]["file"])::$a[0]["function"]($a[0]["line"]): </pre>
   *
   * For function calls the number of elements in debug_backtrace() is at least 
   * 2. In this case the value of key "file", "function" and "line" of element 1 
   * is returned.
   * <pre>basename($a[1]["file"])::$a[1]["function"]($a[1]["line"]): </pre>
   *
   * For object calls the number of elements in debug_backtrace() is at least 
   * 2. In this case always the value of key "class", "function" and "line" of
   * element 1 is returned as this is the place where the invocation took
   * place. The key "object" of this element describes the child class along
   * extension trees. 
   * <pre>basename($a[1]["class"])::$a[1]["function"]($a[1]["line"]): </pre>
   *
   * NOTE: $a[X]["line"] does not match __LINE__ for the object at position 
   *       X in the debug backtrace. It stores the line number in the child
   *       calling the "class" or "function" mentioned at position X in the
   *       trace. Additionally if there are line breaks in the log instruction
   *       the line number is increase by the number of line breaks. In
   *       other words the line number of $a[0] represents the line the
   *       instruction ends.
   *
   * @return a string representing information on the position and name of
   *         the calling instance, method, file and its corresponding line.
   */
  private function get_caller_info($dbt = array()) {

    $this->cresolved = true;

    // object call
    if(count($dbt)>=2 && array_key_exists("class",$dbt[1])) {
      return basename($dbt[1]["class"])."::".$dbt[1]["function"].
                        "(".$dbt[0]["line"]."): ";

    // function call
    } else if(count($dbt)>=2 && !array_key_exists("class",$dbt[1])) {
      return basename($dbt[0]["file"])."::".$dbt[1]["function"].
               "(".$dbt[0]["line"]."): ";

    // direct call
    } else if(count($dbt)===1) {
      return basename($dbt[0]["file"])."(".$dbt[0]["line"].")Y: ";

    // otherwise be able to recognize non regarded caller scenarios
    } else {
      return "Unknown::function(-1): ";

    } 

  }

  /**
   * Log a message.
   * @param $message the complete message
   * @param $type marker for the message's type
   */
  private function write($message = "", $type = null) {

    global $session;

    // adjust the log prefix
    $ms  = "\n    ".date($this->conf["dformat"]);
    $ms .= " | ".(Validator::isempty(
                   (!Validator::isa($session,"null") ? $session->get_sid() : "")) 
                  ? "-" : $session->get_sid());
    $ms .= " | ".$this->class;

    // set the "type" to the default log level if it is invalid
    if($type===null 
       || !Validator::in($type,$this->order)) $type = $this->level;

    // append "type" marker to the log message
    $cix = array_search($type,$this->order);
    switch($type) {
      case "DEBUG":   $ms .= $this->get_colored(" [ debug ] ",$cix);     break;
      case "INFO":    $ms .= $this->get_colored(" [ info ] ",$cix);      break;
      case "NOTICE":  $ms .= $this->get_colored(" [ notice ] ",$cix);    break;
      case "WARNING": $ms .= $this->get_colored(" [ warning ] ",$cix);   break;
      case "ERR":     $ms .= $this->get_colored(" [ error ] ",$cix);     break;
      case "CRIT":    $ms .= $this->get_colored(" [ critical ] ",$cix);  break;
      case "ALERT":   $ms .= $this->get_colored(" [ alert ] ",$cix);     break;
      case "EMERG":   $ms .= $this->get_colored(" [ emergency ] ",$cix); break;
      default: 	      $ms .= $this->get_colored(" [ info ]",$cix);       break;
    }

    // get order indexes for "type" and the current "log level" set   
    $ixt = array_search($type,$this->order);
    $ixl = array_search($this->level,$this->order);

    // only write a log message if the "type" fits into the logging order 
    if($ixt <= $ixl) $written = $this->file->write($ms.$message);

  }

  /**
   * Append $color sequences if $colored is set to true.
   * @param $string string to color
   * @param $cix color index in $colors
   * @return color encoded terminal string
   */
  private function get_colored($string = " [ info ] ", $cix = 6) {
    return (Validator::equals($this->color,true) && Validator::isa($cix,"integer")
            ? hex2bin("1b").$this->colors[$cix].$string.
              hex2bin("1b").$this->colors[count($this->colors)-1]
            : $string);
  }

  /**
   * Set the default log level. Defaults to "INFO".
   * @param $level log level going to be set
   */
  public function set_level($level = "INFO") {
    if(Validator::isa($level,"string") 
       && ($k=array_search($level,$this->order))!==false) 
      $this->level = $this->order[$k];
    else 
      $this->level = "INFO";
  }

  /**
   * Decides wether to use raw color codes when writing log messages.
   * @param $color call with true when coloring should be enabled.
   */
  public function set_color($color = false) {
    $this->color = (Validator::isa($color,"boolean") ? $color : false);
  }

  public function emerg($message = "", $replaces = array()) {
    $cinfo = $this->get_caller_info(debug_backtrace());
    $this->log($cinfo.$message, $replaces, "EMERG");
  } 

  public function crit($message = "", $replaces = array()) {
    $cinfo = $this->get_caller_info(debug_backtrace());
    $this->log($cinfo.$message, $replaces, "CRIT");
  } 

  public function alert($message = "", $replaces = array()) {
    $cinfo = $this->get_caller_info(debug_backtrace());
    $this->log($cinfo.$message, $replaces, "ALERT");
  } 

  public function err($message = "", $replaces = array()) {
    $cinfo = $this->get_caller_info(debug_backtrace());
    $this->log($cinfo.$message, $replaces, "ERR");
  } 

  public function warn($message = "", $replaces = array()) {
    $cinfo = $this->get_caller_info(debug_backtrace());
    $this->log($cinfo.$message, $replaces, "WARNING");
  } 

  public function notice($message = "", $replaces = array()) {
    $cinfo = $this->get_caller_info(debug_backtrace());
    $this->log($cinfo.$message, $replaces, "NOTICE");
  } 

  public function info($message = "", $replaces = array()) {
    $cinfo = $this->get_caller_info(debug_backtrace());
    $this->log($cinfo.$message, $replaces, "INFO");
  } 

  public function debug($message = "", $replaces = array()) {
    $cinfo = $this->get_caller_info(debug_backtrace());
    $this->log($cinfo.$message, $replaces, "DEBUG");
  } 

}
