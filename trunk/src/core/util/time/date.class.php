<?php

namespace core\util\time;
use core\util\param\Validator as Validator;
use modules\calendar\GregorianCalendar as GregorianCalendar;

class Date {

  private $ctype = "gregorian";

  private $date = null;

  public function __construct($date = null, $ctype = "gregorian") {

    switch($type) {

      case "gregorian" : 

        if(Validator::matches("/[0-9]{4}-[0-9]{2}-[0-9]{2}/",$date)
           && GregorianCalendar::covers($date)
           && GregorianCalendar::valid($date)) 
          $this->date = $date;
        else 
          $this->date = date("Y-m-d");
        break;

      case "julian" : break;

      case "roman" : break;

      case "jewish" : break;

      default : break;

    }

  }

}

?>
