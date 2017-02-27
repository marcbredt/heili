<?php

namespace module\langchange;
use core\object\handler\Handable as Handable;

class LangChangeHandler implements Handable {

  public function handle() {

    switch($this->action) {
   
      case "changelang":

          // check the action token
          if($session->get("tokens",$this->action) != $_POST["core:token"]
             && Validator::isa($this->action,"string")) { 
            $filelogger->log("invalid token=%, ".
                               "expected=%", 
                             array($_POST["core:toke"], 
                                   $session->get("tokens",$this->action)));
            return false;
          }
 
          // store the language in the session
          $lang = $_POST["core:language"];
          $session->set("language",null,$lang);
 
        break;
  
      default: break;

    }   

    // remove the action token
    $session->uset("tokens",$this->action);
 
    return true;

  }

}

?>
