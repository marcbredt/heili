<?php

namespace core\data;
use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\string\StringUtil as StringUtil;
use core\html\form\FormEvaluator as FormEvaluator;
use core\exception\http\RequestException as RequestException;

/**
 * This class models the request sent to the application.
 * It is primarly used to validate request and form data.
 * @author Marc Bredt
 */
class SuperGlobals {

  /**
   * Validatate superglobal variables against the definitions mentioned in 
   * the main http request configuration.
   * @return false if any keys in any superglobals were discarded, otherwise true
   * @throws RequestException
   */
  public static function validate() {
   
    global $filelogger, $session;

    // init
    $xmldoc = new XMLDocument(PATH_CONF."/data/superglobals.xml",
                               PATH_DTD."/data/superglobals.dtd", true);
    $valid = true;

    // get targets
    $targets = $xmldoc->xpath("//superglobal[@active=\"y\"]",true);
    $filelogger->log("%", array($targets->get_doc()->saveXML()));

    foreach($targets->get_doc()->childNodes[0]->childNodes as $t) {

      $filelogger->log("target=%, ",
                       array(StringUtil::get_object_string($t)),"DEBUG");

      $tname = $t->getAttribute("name");
      $tpolicy = $t->getAttribute("policy"); // policies = ( drop, keep )
      $tfile = $t->getAttribute("file");

      // verify the superglobal values passed against $tfile
      if(file_exists($tfile) 
         && Validator::equals(mime_content_type($tfile),"application/xml")) {

        // verify each query key againsts the regexps/files defined --> request.xml
        // verify stree in conjunction with mask --> request.xml/masks.xml
        // additionally run an evaluator for dynamic form data passed --> request.xml
        //FormEvaluator::evaluate();
   
        $sgc =  new XMLDocument(PATH_CONF."/".$tfile,
                                PATH_DTD."/data/superglobal.dtd",true);
        $sgc_name = trim($sgc->xpath("string(//superglobal/@name)"));
        $sgc_source = trim($sgc->xpath("string(//superglobal/@source)"));
 
        // variable variables does not work for the first execution
        //$sg = ${$sgc_source}
        eval("\$sg = \$".$sgc_source.";");
        $filelogger->log("original superglobal(\$%)=[ % ]",
                         array($sgc_source,StringUtil::get_object_string($sg)),
                         "DEBUG");

        foreach($sg as $k => $v) {

          // is the key name present
          $kpresent = trim($sgc->xpath("string(//superglobal/key[@name=\"".
                                         $k."\"]/@name)"));

          // validate if the key is listed and it exists in the source
          if(!Validator::isempty($kpresent) && array_key_exists($k,$sg)) {
  
            $type = $sgc->xpath("string(//superglobal/key[@name=\"".$k."\"]/@type)"); 
            $value = $sgc->xpath("string(//superglobal/key[@name=\"".$k."\"]/@value)"); 
            $check = $this->check($sg[$kpresent], $type, 
                                  (!Validator::isempty($value) ? $value : null));
            $valid = $valid && $check;
            
            $filelogger->log("key='%', source='%', ".
                               "type='%', value='%', validated='%', rstate='%'",
                             array(
                               StringUtil::get_object_string($k),
                               "\$".$sgc_source, $type, 
                               StringUtil::get_object_string($value),
                               $check, $valid
                             ),
                             "INFO"
                            );

            if($valid===false) {
              $filelogger->log("session=%",array($session));
              throw(new RequestException()); 
            }


          // drop key value if it is not listed in the configuration
          } else if(Validator::isempty($kpresent)
                    && Validator::equals($tpolicy,"drop")) {

            $filelogger->log("dropping key='%',".
                               " source='%'",
                             array($k,"\$".$sgc_source),
                             "WARNING");
            unset($sg[$k]);

          // or something weird happened
          } else {

            $filelogger->log("source=%, key=%",
                             array("\$".$sgc_source,$k),
                             "ERR");
          }

        }
   
        $filelogger->log("modified superglobal(\$%)=[ % ]",
                         array($sgc_source,StringUtil::get_object_string($sg)),
                         "DEBUG");

      }

    }

    return $valid;

  }

  /**
   * Validate type and value defined for superglobals key.
   * @param $element element to check
   * @param $type string describing the desired type for $element to be
   * @param $value desired value of 
   * @return true if element exists and follows type and value definition,
   *         otherwise false
   */
  private function check($element = null, $type = null, $value = null) {
  
    switch($type) {

      case "array": 
        return (Validator::isa($element,"array")
                && (!Validator::isa($value,"null") ? 
                      Validator::equals($element,$value) : true));

      case "regex": 
        return Validator::matches($element,$value);

      case "class": 
        return (Validator::isa($element,"object")
                && Validator::isclass($element,$value));

    }

  }

}

?>
