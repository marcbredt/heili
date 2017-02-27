<?php

namespace modules\calendar;
use core\exception\time\calendar\CalendarException as CalendarException;

class Calendar {

  /**
   * A short name e.g. gregorian, julian, roman, islamic, darian, egyptian, etc.
   */
  private $name = null;

  /**
   * A bunch of short names for any calendar allowed to be created currently.
   * Append e.g. julian, roman, islamic, etc. when there exists a concrete 
   * configuration
   */
  private $names = array("gregorian","darian");

  /**
   * Calendar classification.
   * lunar - lunar dependent calendar like e.g. islamic calendar
   * solar - solar dependent calendar like e.g. gregorian calendar
   * lunisolar - lunar and solar dependent like e.g. jewish calendar
   * fixed - for calendars with a fixed set of days like e.g. egyptian calendar
   * other - for calendars which are not classifiable currently like e.g.
   *         egyptian calendar which regards sirius
   */
  private $type = null;

  /**
   * Current valid types.
   */
  private $types = array("lunar","solar","lunisolar","fixed","other");

  /**
   * Sections alias month' which characterize a year.
   */
  private $sections = array(
                        "name" => array(), // name of the section/month
                        "abbr" => array(), // abbreviation for each section/month
                        "nodm" => array(), // number days in each section/month
                        "nodw" => array(), // number of days in a subsection/week
                        "ndwk" => array(), // name for each day of a subsection/week
                        "ndwa" => array()  // abbreviation for each day of a 
                                           //   subsection/week
                      );
 
  /**
   * Day adjustments for month to correct deviation from a solar year, e.g.
   * <pre>array("feb" => array(28 => array("%4","!%100","%400")))</pre>
   */
  private $rules = array();
  
  /**
   * Create a calendar instance.
   */
  public function __construct($xmldoc = null) {

    global $filelogger;

    if(Validator::isclass($xmldoc,"core\util\xml\XMLDocument")
       && !Validator::isa($xmldoc->doc,"null")) {

      // TODO: initialize name,type,sections,subsections,rules   

    } else {
      $filelogger->log("%, type=%",
                       array((new CalendarException("invalid type",1)),$type));
      throw(new CalendarException("invalid type",1));
    }
  }
 
  /**
   * Checks if a date matches a specific calendar's range.
   * - gregorian calendar was used since 1582-10-15
   * - julian calendar was used since -0045-01-01 (also before)
   * - roman calendar was perhaps used since -0713-01-01 (Numa Pompilius)
   * - jewish calendar was used since -3761-01-01
   * @return true if a date is placed in the range the calendar was used
   */
  private function covers(){}

  /**
   * Validate a specific date for a calendar type.
   * @return true, if the date passed is valid respecting the calendar type
   */
  private function isvalid(){}

  /**
   * Get the current name for the calendar set.
   * @return name for the calendar type
   */
  public function get_name(){
    return $this->name;
  }

  /**
   * Set the name for the current calendar.
   * @throws 
   */
  public function set_name($name = "gregorian"){
    global $filelogger;
    if(Validator::in($name,$this->names())) { $this->name = $name;
    } else {
      $filelogger->log("%, name=%",
                       array((new CalendarException("invalid name",0)),$name));
      throw(new CalendarException("invalid name",0));
    }
  }

  /**
   * Get the names currently available
   */
  public function get_names() {
    return $this->names;
  }

  /**
   * Get the current calendar type set.
   * @return calendar type
   */
  public function get_type() {
    return $this->type;
  } 

  /**
   * Set the calendar's type.
   * @throws CalendarException
   */
  public function set_type($type = "solar") {
    global $filelogger;
    if(Validator::in($type,$this->get_types())) { $this->type = $type;
    } else {
      $filelogger->log("%, type=%",
                       array((new CalendarException("invalid type",1)),$type));
      throw(new CalendarException("invalid type",1));
    }
  } 

  /**
   * Get the calendars valid types.
   * @return array containing current valid calendar types.
   */
  public function get_types() {
    return $this->types;
  }

  /**
   * Get the calendar sections alias month'.
   * @return calendar sections
   */ 
  public function get_sections(){
    return $this->sections;
  }

}

?>
