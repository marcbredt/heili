<?php

namespace core\util\param\filter;
use core\util\string\StringUtil as StringUtil;
use core\util\param\filter\FilterUnit as FilterUnit;

/**
 * This class provides static methods to filter parameters passed onto any
 * object or method. 
 * @author Marc Bredt
 */
class Filter {

  /**
   * Predefined filter types.
   */
  private $types   = array(INPUT_GET, INPUT_POST, INPUT_SERVER, 
                           INPUT_SESSION, INPUT_COOKIE, INPUT_ENV,
                           INPUT_REQUEST);
 
  private $unit    = null;

  private $filter  = null;

  private $options = null;

  /**
   * Setup a validation or sanitization input. 
   * @param $type INPUT_{GET,POST,SERVER,SESSION,COOKIE,ENV,REQUEST}, if $type 
   *              is an array it is assumed multiple variables are passed, 
   *              filter_var{,_array} is then invoked
   * @param $var variable in $type going to be checked, if null all variables
   *             currently in $type will be checked via filter_input_array()
   * @param $fops filter options, need to be an array if $args is null
   *              indicating all variables of $type are going to be checked
   * @return filtered values, input values that differ from filtered values are
   *         invalid input values
   * @see filter_list()
   * @see http://php.net/manual/de/ref.filter.php
   * @see http://php.net/manual/de/filter.filters.validate.php
   */
  public function __construct(FilterUnit $unit = null) {

    global $filelogger;
    $this->unit = $unit;
    $this->setup_options();

    if(Validator::isa($this->options,"null")) {
      $filelogger->error("invalid filter options. unit=%, options=%",
                         array(FilterException("invalid filter options",1),
                               $this->unit, $this->options));
      throw(new FilterException("invalid filter options",1));
    }
  
  }
 
  /**
   * Get filtered input.
   * @return the filtered and sanitized input, otherwise null
   * <pre>
   *   $_POST = array( "contact:email" => "foo@bar.com");
   *   $data = array( "contact:email" => "foo@bar.com"); 
   *   $filter = array( "contact:email" => FILTER_VALIDATE_EMAIL);
   * 
   *   self::filter(INPUT_POST,"contact:email","email") #invokes
   *     filter_input(INPUT_POST,"contact:email",filter_id($fops))
   *
   *   self::filter(INPUT_POST,null,"email") #invokes
   *     filter_input_array(INPUT_POST,filter_id($fops))
   *
   *   self::filter(INPUT_POST,"contact:email",$filter) #invokes
   *     filter_input(INPUT_POST,"contact:email",$fops)
   *   
   *   self::filter(INPUT_POST,null,$filter) #invokes
   *     filter_input_array(INPUT_POST,$fops);
   *
   *   self::filter($data,"contact:email","email") #invokes
   *     filter_var($type,"contact:email",filter_id($fops))
   *
   *   self::filter($data,null,"email") #invokes
   *     filter_var_array($type,"contact:email",filter_id($fops))
   *
   *   self::filter($data,"contact:email",$filter) #invokes
   *     filter_var($type,"contact:email",$fops)
   *
   *   self::filter($data,null,$filter) #invokes
   *     filter_var_array($type,"contact:email",$fops)
   *
   * </pre>
   */ 
  public function get() {

    // check type INPUT_{GET,POST,SERVER,SESSION,COOKIE,ENV}
    $filtered = null;

    // if an invalid input type was passed
    if(Validator::isa($this->unit->get_target(),"null") 
       || !Validator::in($this->unit->get_target(),$this->types)) {

      if(Validator::isa($this->unit->get_variables(),"array")) 
        $filtered = filter_var_array($this->unit->get_variables(),$this->options);
      else 
        $filtered = filter_var($this->unit->get_variables(),
                               $this->unit->get_variables(),
                               $this->options);

    // if a valid input type was passed
    } else if(!Validator::isa($type,"null")
              && Validator::in($type,$this->types)) { 

      if(Validator::isa($vars,"null")) 
        $filtered = filter_input_array($type,$filter);
      else if(Validator::isa($vars,"string")) 
        $filtered = filter_input($type,$vars,$filter);

    }
 
    return $filtered;
 
  }

  /**
   * Run all filters bound to the filter unit.
   * @return the filtered and sanitized value, otherwise null
   */
  private function apply() {
    return null;
  }

  /** 
   * Check if Unit::source[Unit::variable] or Unit::variable exists.
   * @param $type one of $this->types
   * @param $vars var(s) that has to be present in $type
   * @return true if an input variable exists
   * @see core\util\param\access\Unit
   */
  public function has($type = null, $vars = array()) {
    $res = true;
    if(!validator::isa($vars,"array")) $vars = array($vars);
    foreach($vars as $v) $res = $res && filter_has_var($type,$v);
    return $res;
  }

  /** 
   * Setup the filter(s). Especially set its options. Every filter options 
   * should set $this->options["options"]["default"] to <code>null</code> as
   * filter exceptions will only be thrown if the filtered value is null. This
   * will always be the case if the input passed is invalid and therefor 
   * returns the fallback of the option mentioned above set.
   * @return an array with filter options
   * @see core\util\param\access\Accessor
   * @see http://php.net/manual/de/filter.filters.flags.php
   * @see https://tools.ietf.org/html/rfc2396
   * @see https://whois.arin.net/rest/net/NET-192-0-2-0-1
   * @see http://stackoverflow.com/questions/967738
   */
  private function setup_options() {

    $this->options = null;

    if(Validator::isa($this->unit->get_variables(),"array")) {

      //foreach()
 
    } else {

    }

  }

  private function get_options() {

    switch($this->filter = $unit->get_filter()) {

      case "int": 
          $this->options = array();
          $this->options["options"] = array(
            "regexp" => "/^[0-9]+$/",
            "default" => null,
            "min_range" => $unit->get_min(),
            "max_range" => $unit->get_max()
          );
        break;

      case "boolean": 
          $this->options = array();
          $this->options["options"] = array( 
            "regexp" => "/^(1|true|on|yes|0|false|off|no)$/",
            "default" => null
          );  
          $this->options["flags"] = FILTER_NULL_ON_FAILURE;
        break;

      case "float":
          $this->options = array();
          $this->options["options"] = array(
            "regexp" => "/^([0-9]+){0,1}(\.[0-9]*){0,1}([eE][+-][0-9]+){0,1}$/",
            "decimal" => ".", // specifies the char separating int and float part
            "default" => null
            // ranges not available for float, see additional filter setups
          );  
        break;

      case "validate_regexp": 
          $this->options = array();
          $this->options["options"] = array( 
            "regexp" => $unit->get_regex(),
            "default" => null
          );  
        break;

      case "validate_url": 
          $this->options = array( "default" => null );
        break; 

      case "validate_email":
          $this->options = array( "default" => null );
        break; 

      // TODO: probably separate filters for RES_RANGE and PRIV_RANGE
      case "validate_ip": 
          $this->options = array( "default" => null );
          $this->options["flags"] = 
            FILTER_FLAG_IPV4
            | FILTER_FLAG_IPV6
            | FILTER_FLAG_NO_RES_RANGE
            | FILTER_FLAG_NO_PRIV_RANGE;
        break; 

      case "validate_mac": 
          $this->options = array( "default" => null );
        break; 

      // NOTE: better encode input as (evil) input will not be lost and can
      //       inspected after recording
      case "string": 
          $this->options = array();
          $this->options["flags"] = 
            FILTER_FLAG_ENCODE_LOW
            | FILTER_FLAG_ENCODE_HIGH
            | FILTER_FLAG_ENCODE_AMP; 
        break;

      // NOTE: try to not use this filter as (evil) input will be lost
      case "stripped": 
          $this->options = array();
          $this->options["flags"] = 
            FILTER_FLAG_STRIP_LOW
            | FILTER_FLAG_STRIP_HIGH
            | FILTER_FLAG_ENCODE_LOW 
            | FILTER_FLAG_ENCODE_HIGH
            | FILTER_FLAG_ENCODE_AMP; 
        break;

      case "encoded": 
          $this->options = array();
          $this->options["flags"] = 
            FILTER_FLAG_ENCODE_LOW
            | FILTER_FLAG_ENCODE_HIGH;
        break;

      case "special_chars": 
          $this->options = array();
          $this->options["flags"] = 
            FILTER_FLAG_ENCODE_HIGH
            | FILTER_FLAG_STRIP_LOW;
        break;

      case "full_special_chars": 
          $this->options = array();
        break;

      case "unsafe_raw": 
          $this->options = array();
          $this->options["flags"] = 
            FILTER_FLAG_ENCODE_LOW
            | FILTER_FLAG_ENCODE_HIGH
            | FILTER_FLAG_ENCODE_AMP; 
        break;

      // removes all chars except a-zA-Z0-9!#$%&'*+-/=?^_`{|}~@.[]
      case "email":
          $this->options = array();
        break;

      // removes all chars except a-zA-Z0-9$-_.+!*'(),{}|\^~[]`<>#%";/?:@&=
      case "url": 
          $this->options = array();
        break;

      // removes al chars except +-0-9
      case "number_int": 
          $this->options = array();
        break;

      case "number_float": 
          $this->options = array();
          $this->options["flags"] = 
            FILTER_FLAG_ALLOW_FRACTION
            | FILTER_FLAG_ALLOW_SCIENTIFIC;
        break;

      case "magic_quotes": 
          $this->options = array();
        break;

      case "callback": 
          $this->options = array();
        break;
 
      // TODO: the following filters are probably useful and usable in callbacks

      case "int_oct":
          $this->options = array();
          $this->options["options"] = array(
            "regexp" => "/^[0-9]+$/",
            "min_range" => $unit->get_min(),
            "max_range" => $unit->get_max()
          );
          // allows octal encoding with 0, oct(0137) = int(95)
          $this->options["flags"] = FILTER_FLAG_ALLOW_OCTAL; 
        break;

      case "int_hex":
          $this->options = array();
          $this->options["options"] = array(
            "regexp" => "/^[0-9]+$/",
            "min_range" => $unit->get_min(),
            "max_range" => $unit->get_max()
          );
          // allows hex encoding with 0x, hex(0x1a) = int(26)
          $this->options["flags"] = FILTER_FLAG_ALLOW_HEX;
        break;

      case "float_range": 
          $this->options = array();
          $this->options["options"] = array(
            "regexp" => "/^([0-9]+){0,1}(\.[0-9]*){0,1}([eE][+-][0-9]+){0,1}$/",
            "decimal" => ".",
            // those range values need to be evaluated in a callback
            "min_range" => $unit->get_min(), 
            "max_range" => $unit->get_max()
          );  
        break;

      case "validate_url_pq":
          $this->options = array();
            // validates only if path or query are defined in the url
            $this->options["flags"] = 
              FILTER_FLAG_PATH_REQUIRED 
              | FILTER_FLAG_QUERY_REQUIRED;
          break;

      default: break;

    } // end switch

  } // end get_options 

}

?>
