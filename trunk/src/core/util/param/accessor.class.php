<?php

namespace core\util\param;
use core\util\param\Validator as Validator;
use core\util\param\filter\Filter as Filter;
use core\util\param\filter\FilterUnit as FilterUnit;
use core\exception\html\form\io\filter\FilterException as FilterException;

/**
 * This class helps to access input variables passed via any submissions of 
 * forms or java script requests. Every input is accessed through validation
 * filtering and sanitization instances.
 * @author Marc Bredt
 */
class Accessor {

  /**
   * Get a filtered and sanitized input variable. 
   * @param $unit a filter unit
   * @return the unit value determined after invocation of filters set, 
   *         otherwise null
   * @throws FilterException
   * @see core\util\param\filter\FilterUnit
   */
  public static function get(FilterUnit $unit = null) {

    global $filelogger;

    // setup the filter and filter the input
    if(Validator::isclass($unit,"core\util\param\\filter\FilterUnit")) {
      $f = new Filter($unit); 
      $filtered = ($f->has($unit->get_target(),$unit->get_variables()) 
                   ? $f->get() : null);
    } else {
      $filtered = null;
    }

    // return the filtered and sanitized value or log an encoded string
    if(!Validator::isa($filtered,"null")) {
      return $filtered;
    } else {
      $filelogger->alert("invalid input. unit=%",array($unit));
      return null;
    }

  }

}

?>
