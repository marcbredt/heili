<?php

namespace core\session\handler;
use core\util\param\Validator as Validator;
use core\util\file\File as File;

/**
 * This class is used to override the present session handler to be able to
 * e.g. bind the session handling onto databases in further work but mainly
 * to control the any session operation upon invocation with respect to
 * @author Marc Bredt
 * @see session_set_save_handler
 * @see php.net/manual/en/class.sessionhandlerinterface.php
 */
class SessionHandler {

  private $save_path = null;

  public function __construct() {
    session_set_save_handler(
      array($this,"open"),
      array($this,"close"),
      array($this,"read"),
      array($this,"write"),
      array($this,"destroy"),
      array($this,"gc")
    );
  }

  public function open($save_path = "", $name = "") {
    global $filelogger;
    if(!Validator::isa($filelogger,"null"))
      $filelogger->log("p=%, n=%",array($save_path,$name));
    $this->save_path = $save_path;
    return true;
  }

  public function close() {
    global $filelogger;
    if(!Validator::isa($filelogger,"null"))
      $filelogger->log("");
    $this->gc(ini_get("session.gc_maxlifetime"));
    return true;
  }

  public function read($sid = "") {
    global $filelogger;
    if(!Validator::isa($filelogger,"null"))
      $filelogger->log("sid=%",array($sid));
    $sess_file = new File($this->save_path."/sess_".$sid);
    return (string) file_get_contents($sess_file->get_file());
  }

  public function write($sid = "", $data = "") {
    global $filelogger;
    $sess_file = new File($this->save_path."/sess_".$sid);
    $sfo = $sess_file->open("w");
    if($sess_file->open("w")) {
      $written = $sess_file->write($data);
      $sess_file->close();
      return $written;
    } else {
      return false;
    }
  }

  public function destroy($sid = "") {
    global $filelogger;
    if(!Validator::isa($filelogger,"null"))
      $filelogger->log("sid=%",array($sid));
    return @unlink($this->save_path."/sess_".$sid);
  }

  public function gc($maxlifetime = 1200) {
    global $filelogger;
    if(!Validator::isa($filelogger,"null"))
      $filelogger->log("ml=%",array($maxlifetime));
    foreach(glob($this->save_path."/sess_*") as $f) {
      if(time() > filemtime($f)+$maxlifetime) {
        @unlink($f);
      }
    }
    return true;
  }

  public function get_save_path() {
    return $this->save_path;
  }

}

?>
