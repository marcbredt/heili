<?php

namespace core\session\handler;
use core\util\string\StringUtil as StringUtil;
use core\util\param\Validator as Validator;
use core\util\param\Accessor as Accessor;
use core\util\param\filter\FilterUnit as FilterUnit;
use core\object\handler\Handable as Handable;
use core\session\User as User;
use core\session\Session as Session;
use core\session\auth\Authenticator as Authenticator;
use core\excception\html\ActionException as ActionException;

/**
 * Handles an action defined in the query.
 * @author Marc Bredt
 */
class ActionHandler implements Handable {

  private $method  = null;
  private $fid     = null;
  private $module  = null;
  private $fname   = null;
  private $fconfig = null;
  private $action  = null;
  private $defines = null;
  private $handler = null;
  private $depends = null;
  private $token   = null;

  /**
   * Handle actions invoked by form submissions.
   * @return true if successfully handling an action completely, otherwise false
   * @throws ActionException
   */
  public static function handle(){
  
    global $filelogger;

    // perform checks on parameters passed based for a valid action
    if(!$this->initialize()){
      $filelogger->error("%",array(new ActionException("checks failed",2)));
      throw(new ActionException("checks failed",2));
    }
 
    // handle the desired action
    $this->handle_action();

  }

  /**
   * Load the required form chain (actions -> forms -> form) for an action and 
   * perform checks on the chain configuration and parameters passed.
   * @return true if the config chain was completly loaded successfully,
   *         otherwise false
   * @see core\html\form\io\InputElement
   */
  private function initialize() {
   
    global $session, $filelogger, $inputprovider;

    // determine the request method 
    $this->method = $this->determine_request_method();

    // determine the form identifier
    $this->fid = $this->determine_form_identifier();
    $this->module = explode(".",$this->fid)[0];
    $this->fname = explode(".",$this->fid)[1];
    $filelogger->debug("fid=%, mid=%, fname=%",
                       array($this->fid,$this->module,$this->fname));

    // determine the form config and check the rights
    $this->fconfig = $this->determine_form_configuration();
    $form = new Form($this->fconfig);
    RightManager::has($form); // result info is produced by the manager
    $filelogger->debug("form = %", array($form));
    
    // determine form action key
    $this->action = $this->determine_form_action_key();

    // determine form key definitions 
    $this->defines = $this->determine_form_definitions();

    // determine the action handler and the depends
    $this->determine_action_handler_and_depends();

    // check if all action dependencies are fulfilled by the definitions
    if($this->determine_action_dependencies_present()) {

      // TODO: add range values from $fconfig elements to the filter
 
      // TODO: validate data passed to the corresponding form
      // if all data is present check the entire parameter set
      // containing only parameters needed against regex in the
      // form config $fconfig 
      // TODO: set the action token passed
      //foreach $var Accessor::get() and store em into a parameter map
      //$unit = new FilterUnit("action","GET","^[a-z]+$","validate_regexp,string");
      //$this->action = Accessor::get($unit); // filter failures are thrown in here
      //$inputprovider->set("action",$this->action);

      // print some information on the filtered input elements
      $filelogger->debug("filtered input = %",array($inputprovider));

      return true;

    }
   
    return false;

  }

  /**
   * Determine the request method.
   * @return string representing the request method, currently a dummy function
   *                returning "POST" as only post requests will be evaluated 
   *                conceptually
   * @see core\html\form\FormBuilder
   */
  private function determine_request_method() {
    return "POST";
  }

  /**
   * Determines the submitted form identifier from (filtered) keys in $_POST.
   * @return string containing the form identifier MID.FID derived from
   *                MID.FID.GID.(NUM|RID.CID)[.(x|y)]
   * @see core\html\form\io\InputElement
   */
  private function determine_form_identifier() {

    // array_map - filter each _POST key
    // array_filter - remove all invalid keys, filtered via false
    // array_count_values - count filtered/sanitized keys with valid prefix
    $regex   = "^[a-z]+\.[a-z]+\.(e\.[0-9]+|g)\.[0-9]+(\.[xy])*$";
    $filters ="validate_regexp,stripped";
    $fkunit  = new FilterUnit(array_keys($_POST),"",$regex,$filters);
    $akunit  = Accessor::get($fkunit);
    $fkeys=array_count_values(array_filter(array_map(
             function($fkey,$fmeth,$regex,$filters){
               $kunit = new FilterUnit($fkey,"",$regex,$filters);
               $aku = Accessor::get($kunit);
               if(Validator::isa($aku,"null")) return false;
               else return substr($aku,0,strpos($aku,".",strpos($aku,".")+1));
             }, 
             array_keys($_POST), // keys to filter from
             array_fill(0,count($_POST),$regex), // regex parameter
             array_fill(0,count($_POST),$filters) // filters parameter
           ))); 

    // sort to get the key with the max value from the last element of the array
    asort($fkeys); end($fkeys);

    // return the main key determined
    return key($fkeys);

  }

  /**
   * Determine the form file path relative to html/form/ from forms.xml
   * @return
   */
  private function determine_form_configuration() {

    $xdf = new XMLDocument(PATH_CONF."/html/forms.xml",PATH_DTD."/html/forms.dtd");

    if(Validator::equals($this->module,"core")) 
      $fconfig = 
        PATH_CONF."/html/form/".$xdf->xpath("normalize-space(".
          "//form[@active=\"y\" and @module=\"".$this->module."\"".
          " and @name=\"".$this->fname."\"]/text())");

    else 
      $fconfig = 
        constant("MOD_".strtoupper($module)."_PATH_CONF")."/html/form/".
          $xdf->xpath("normalize-space(".
            "//form[@active=\"y\" and @module=\"".$this->module."\"".
            " and @name=\"".$this->fname."\"]/text())");

    $filelogger->debug("fconfig = [ % ]",array($fconfig));
    return $fconfig;

  }
 
  /**
   * Determine the form action key via key layout and the defines attribute in
   * form configuration.
   * @return string representing the name of the action key which invoked the
   *                form submission. This is possible because only the invacating
   *                submit button is provided.
   * @throws ActionException in case multiple action keys are found which would
   *                         be possible upon query injection
   */
  private function determine_form_action_key() {

    $unit = new FilterUnit($actionkey,"GET","^[a-z]+$","validate_regexp,string");
    $this->action = Accessor::get($unit); // filter failures are thrown in here
    $inputprovider->set("action",$this->action);
    $filelogger->info("evaluated action=%, unit=%",array($this->action,$unit));

  }

  /**
   * Determine the action handler corresponding to the action key and the form
   * identifier definition set in conf/data/actions.xml. Defaults to the class
   * core\session\handler\ActionHandler. Every handler configured need to
   * implement core\object\handler\Handable because the functions defined in 
   * there will be called in ::handle_action().
   * @see core\object\handler\Handable
   * @see core\session\handler\ActionHandler
   * TODO: check if the handler is a valid one
   */
  private function determine_action_handler_and_depends() {

    $xda = new XMLDocument(PATH_CONF."/data/actions.xml",PATH_DTD."/data/actions.dtd");

    $this->handler = $xda->xpath("//action[@name=\"".$this->action."\" ".
                                      "and @active=\"y\" ".
                                      "and @form=\"".$this->fid."\"]/@handler");
    $this->depends = $xda->xpath("//action[@name=\"".$this->action."\" ".
                                      "and @active=\"y\" ".
                                      "and @form=\"".$this->fid."\"]/@depends");
    
    $filelogger->debug("handler for action % = %",array($this->action,$handler));
    $filelogger->debug("depends for action % = %",array($this->action,$depends));

  }

  /**
   * Determine if all action dependencies are present.
   * @return true if all action dependencies defined via form keys are present
   */
  private function determine_action_dependencies_present() {
    // TODO: diff the depends and define maps
    return true;
  }

  /**
   * Handle a specific action defined in conf/data/actions.xml
   * @param $this->action action going to be handled
   * @param $handler action handler going to be used
   * @see call_user_func()
   * @req PHP >= 5.3.0, call_user_func() is able to invoke class functions
   */
  private function handle_action() {

    global $filelogger, $session;

    // check the action token
    $token = $this->token;
    $stoken = $session->get("tokens",$this->action);
    if(!Validator::equals($stoken,$this->token)) {
      $filelogger->error("invalid token=%, expected=%, %",array($token,$stoken,
                         new ActionException("invalid token '".$token."', ".
                                             "expected '".$stoken."'",2)));
      throw(new ActionException("invalid token '".$token."', ". 
                                "expected '".$stoken."'",2));
    }
    
    // execute the handler defined  
    //call_user_func(array($this->handler,"handle"),$param1,...,$paramN);
    call_user_func(array($this->handler,"handle"));
    
    // remove the action token
    $session->uset("tokens",$this->action);
 
  }

}

?>
