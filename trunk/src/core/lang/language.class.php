<?php

namespace core\lang;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\param\Validator as Validator;
use core\util\map\StringMap as StringMap;
use core\exception\LanguageException as LanguageException;

class Language {

  private $lxfile = null;

  private $ldfile = null;

  private $lang = null;

  private $langset = null;
 
  public function __construct($lang = null, 
                              $lxroot = "../conf/lang", 
                              $ldroot = "../conf/dtd/lang", 
                              $xml = "../conf/lang/languages.xml", 
                              $dtd = "../conf/dtd/lang/languages.dtd") {

    // TODO: load lang extension from module directory

    global $session, $filelogger;

    // load default/available/active languages from core config
    $filelogger->log("l=%,lxroot=%,ldroot=%,langsx=%,langsd=%",
                     array($lang,$lxroot,$ldroot,$xml,$dtd),"DEBUG");
    $x = new XMLDocument($xml,$dtd);
    $default = $x->xpath("string(//group[@name=\"languages\"]/@default)");
    // sets up an preg_match disjunction string
    $languages = preg_replace("/name=|\"/", "", 
                   preg_replace("/ /", "|",
                     $x->xpath("//group[@name=\"languages\"]/".
                                 "element[@active='y']/@name")));
    $filelogger->log("languages = [ % ]",array($languages),"INFO");

    // if an invalid lang was passed set the configured default
    if(Validator::isa($lang,"null") || !Validator::in($lang,$languages)) 
      $this->lang = $default;
    else 
      $this->lang = $lang;

    // if an invalid language string was passed or 
    if(!Validator::matches($this->lang,"/[a-z]{2}/") 
       || !Validator::matches($this->lang,"/(".$languages.")/")
       || !file_exists($lxroot."/".$this->lang.".xml")) {

      $filelogger->warn("%, lang '%' not found in langs=(%), defaulting to '%'", 
                 array(new LanguageException("language not available"),
                       $this->lang, $languages, $default));

      // if a wrong default language was configured choose the first from lang
      if(!Validator::matches($default,"/(".$languages.")/")) 
        $this->lang = substr($languages,0,2);
      else 
        $this->lang = $default;

    }

    // log language information
    $this->lxfile = $lxroot."/".$this->lang.".xml";
    $this->ldfile = $ldroot."/lang.dtd";
    $filelogger->log("languages=(%), ".
                 "lang set to '%', lxfile=%, ldfile=%", 
               array($languages, $this->lang, $this->lxfile, $this->ldfile));

    // store the language into the session for further processing
    if(isset($session)) $session->set("language",null,$this->lang);
  }

  /**
   * Load language elements from language file addressable by the language
   * determined during construction.
   * @see StringMap
   */ 
  public function load() {
  
    global $filelogger; 

    $this->langset = new StringMap();
    $x = new XMLDocument($this->lxfile,$this->ldfile);
    $ed = $x->xpath("//language[@id='".$this->lang."']",true)->get_doc();

    foreach($ed->documentElement->childNodes[0]->childNodes as $node) {

      $nname = trim($node->getAttribute("name"));
      $ncont = trim($node->textContent);
      $filelogger->debug("lang element, name=%, value=%",array($nname,$ncont));

      if(!$this->langset->set($nname,$ncont)) {
        $filelogger->error("%",
          array(new LanguageException("loading '".$key."'='".$value."' failed",2)));
        //throw(new LanguageException("loading '".$key."'='".$value."' failed",2));
      }

    }

  }

  /**
   * Get the language element by name. 
   * @param $element the element name in the string map
   * @return the language element defined by $element name
   */
  public function get($element = "") {

    if(Validator::isa($this->langset,"null")) $this->load();
    return $this->langset->get($element);

  }

  /**
   * Print information on this Language object.
   * @return string containing information on this object
   */
  public function __toString() {
    return get_class($this)."-".spl_object_hash($this)."=( ".
             "langset=".$this->langset.
           " )";
  }

}

?>
