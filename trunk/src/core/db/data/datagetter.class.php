<?php

namespace core\db\data;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\param\Validator as Validator;
use core\db\config\DatabaseConfig as DatabaseConfig;
use core\db\statement\Statement as Statement;
use core\db\statement\StatementBatch as StatementBatch;
use core\db\execute\Executor as Executor;

/**
 * Get data.
 * @author Marc Bredt 
 * TODO: initial db config and driver setup somewhere else in a way DataGetter
 *       only needs to submit the sql statement tree name
 * TODO: return XMLDocument as result to be able to build arrays for e.g. form
 *       elements
 */
class DataGetter {

  private $executor = null;

  private $batch = null;

  /**
   * Get data for a defined statement.
   * @param $sname statement name defined in $xml
   * @param $xml xml configuration containing $sname statement
   */
  public function __construct($sname = "", $xml = "") {

    global $filelogger,$database;
    $this->executor = new Executor($database->get_driver());

    // get statement config for $sname
    $xstmts  = new XMLDocument((Validator::isempty($xml) 
                               ? PATH_CONF."/db/statements.xml" 
                               : $xml), PATH_DTD."/db/statements.dtd");
    $object = $xstmts->xpath("//statement[@name='".$sname."']",true);

    // create a statement batch for statement trees, $object is an XMLDocument
    if(Validator::isclass($object, "core\util\xml\XMLDocument")) {
      $this->batch = new StatementBatch();
      $filelogger->log("xd=%", array($object));
      $nodes = $object->get_doc()->documentElement;
      $this->build_batch($nodes);
      $filelogger->log("batch=%", array($this->batch));
    }

  }

  /**
   * Build a batch from SQL statement tree.
   */
  private function build_batch($nodes = null) {
  
   global $filelogger;

   foreach($nodes->childNodes as $n) {

     if(Validator::isclass($n,"DOMText") 
        && !Validator::isempty(trim($n->wholeText))) { 

       $filelogger->info("processing statement=%, query=%, provides=%",
                  array($n->parentNode->getAttribute("name"), 
                        trim($n->wholeText),
                        ($n->parentNode->hasAttribute("provides") ? 
                           $n->parentNode->getAttribute("provides") : "")));
       
       // create a statement with empty parameters for the moment
       $s = new Statement(trim($n->wholeText),array(),
                          explode(",", $n->parentNode->getAttribute("provides")));

       $this->batch->add($s);
     }

     if($n->hasChildNodes()) $this->build_batch($n);
     
    }

  }

  public function get($params = array(), $transact = true) {

    // setup initial dummy parameters to invoke the batch execution for empty
    // arrays as batch itertion iterates over parmeter arrays
    if(!Validator::isa($params,"array") || count($params)==0) 
      $this->batch->get()[0]->set_params(array(":dummy"=>":dummy"));
    else $this->batch->get()[0]->set_params($params);

    // execute the (cumber)batch :)
    return $this->executor->batch($this->batch, $transact);  

  }

}

?>
