<?php

namespace core\util\xml;

use \DOMDocument as DOMDocument;
use \DOMXpath as DOMXpath;
use \DOMImplementation as DOMImplementation;

use core\util\param\Validator as Validator;

use core\exception\xml\XMLMergeException as XMLMergeException;
use core\exception\xml\XMLNotValidException as XMLNotValidException;
use core\exception\xml\XMLNoValidDTDException as XMLNoValidDTDException;
use core\exception\xml\xpath\UnresolvedXPathException as UnresolvedXPathException;
use core\exception\xml\xpath\InvalidXPathExpressionException  
    as InvalidXPathExpressionException;

/**
 * This class is used to read XML documents. 
 * It belongs to the core. As it is used by AccessibleObject
 * AccessChecker respectively it could not extend AccessibleObject
 * otherwise there are probably loops generated.
 * TODO: merging all core with any module files depending on the module tre
 * @author Marc Bredt
 * @see <a href="http://php.net/manual/en/class.domdocument.php">DOMDocument</a>
 */ 
class XMLDocument {

  /**
   * Stores the XML file path/name.
   */
  private $xfile = null;

  /**
   * Stores the XML file path/name.
   */
  private $dfile = null;

  /**
   * Store the XML document as DOMDocument.
   */
  private $doc = null;
  
  /**
   * Load the XML file into a DOMDocument.
   * @param $xmlfile path to the XML file to be loaded.
   * @param $dtdfile path to dtd file to validate the XML
   * @param $validate flag to enable validation against a dtd, defaults to true
   * @throws XMLNotValidException
   */
  public function __construct($xmlfile = null, $dtdfile = null, $validate = true) {

    // get the global filelogger
    global $filelogger;

    // initialize the document
    if(!Validator::isa($xmlfile,"null") && file_exists($xmlfile)
       && Validator::equals(mime_content_type($xmlfile),"application/xml")) {

      // create a validatable DOMDocument first and
      // run validation on the xmlfile using the dtd provided
      if(!Validator::isa($dtdfile,"null") && file_exists($dtdfile)
         && Validator::equals(mime_content_type($dtdfile),"text/plain")
         && Validator::equals($validate,true)) { 
        
        if(!Validator::isa($filelogger,"null")) 
          $filelogger->info("creating validatable XMLDocument.");
        $this->create_doc($xmlfile,$dtdfile);

        if(!Validator::isa($this->doc,"null") && @$this->doc->validate()) { 
          $this->xfile = $xmlfile;
          $this->dfile = $dtdfile;

        } else {
          $this->doc = null;
          $filelogger->err("%, xml=%, dtd=%",
                           array(
                             new XMLNotValidException("XML not valid",2),
                             $xmlfile, $dtdfile));
          throw(new XMLNotValidException("XML not valid",2));
        }
      
      // skip validation
      } else if(Validator::equals($validate,false)) {

        $filelogger->log("creating invalidatable XMLDocument.",
          array(),"WARNING");

        $this->xfile = $xmlfile;
        $this->dfile = null;
        $this->create_doc($xmlfile,null,false);

      // if the dtd file passed is not valid
      } else {
        $filelogger->log("%, xml=%, dtd=%",
                         array(
                           new XMLNoValidDTDException(),
                           $xmlfile, $dtdfile
                         ),
                         "ERR");
        throw(new XMLNoValidDTDException());

      }
  
    // if the xmlfile is null at least an instance should be created to be able
    // to construct an XMLDocument from string using ::create_doc()
    } else if(Validator::isa($xmlfile,"null")) {
      $filelogger->log("creating empty XMLDocument",array(),"WARNING");

    // if an invalid xml file was passed
    } else {
      $filelogger->log("% (xfile=%, dfile=%, xnull?=%, validate?=%, xex?=%, mtv?=%)",
          array(new XMLNotValidException(),
                $xmlfile, $dtdfile, Validator::isa($xmlfile,"null"), $validate,
                @file_exists($xmlfile),
                Validator::equals(@mime_content_type($xmlfile),"application/xml")
          ),
        "ERR");
      throw(new XMLNotValidException());
    
    }

  }

  /**
   * Create document with corresponding document description.
   * TODO: check creation for dtd being null
   * @param $xml XML file name or XML string to create a document from
   * @param $dtd DTD file to create document from
   * @param $validate flag to create a validatable document using $dtdfile
   * @return DOMDocument with $dtdfile attached
   */
  public function create_doc($xml = null, $dtd = null, $validate = true) {

    global $filelogger;

    $d = null;
    $dx = new DOMDocument();

    // if a valid xml file was passed 
    if(Validator::isa($xml,"string") && file_exists($xml) 
       && Validator::equals(mime_content_type($xml),"application/xml")) {
      $dx->load($xml);

    // otherwise assume it is an xml string
    } else if (Validator::isa($xml,"string")) {
      // wrap it to definetly make any string a valid xml string
      $x = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"no\"? >\n";
      $dx->loadXML("<root>".trim($xml)."</root>");
 
    // create an xml from dataset
    } else if(Validator::isclass($xml,"core\db\data\DataSet")){
 
      // TODO: create an xml from data set - multidimensional array
 
    // otherwise throw an exception
    } else {
      $filelogger->log("% (%,%,%)",
          array(new XMLNotValidException(),
                Validator::isa($xml,"null"),
                @file_exists($xml),
                Validator::equals(@mime_content_type($xml),"application/xml")
          ),
        "ERR");
      throw(new XMLNotValidException());
   
    }

    $di = new DOMImplementation();
    if($validate === true && file_exists($dtd)
       && Validator::equals(mime_content_type($dtd),"text/plain")) {
      $dtd = $di->createDocumentType($dx->documentElement->tagName,'',$dtd);
      $d = $di->createDocument("",$dx->documentElement->tagName,$dtd);
    } else {
      $d = $di->createDocument("",$dx->documentElement->tagName);
    }

    // set some document markers/flags
    $d->xmlStandalone = false;
    $d->xmlVersion = "1.0";
    $d->formatOutput = true;
 
    // set the main document element 
    $d->removeChild($d->documentElement);
    $d->appendChild($d->importNode($dx->documentElement->cloneNode(true),true));

    $this->doc = $d;
    return $d;

  }
  
  /**
   * Get the currently loaded XML document.
   * @return the currently loaded XML document.
   */
  public function get_doc() {
    return $this->doc;
  }

  /** 
   * Get string representation for a DOMDocument.
   * @param $doc DOMDocument to be transformed into string
   * @return string representation for <code>$doc</code> if it is a valid
   *                DOMDocument otherwise an empty string
   */
  public static function get_doc_string($doc = null) {

    if (!Validator::isa($doc,"null") 
        && Validator::isa($doc,"object") 
        && Validator::isclass($doc,"DOMDocument"))
      return $doc->saveXML(); 

    return ""; 

  }

  /**
   * Set the currently loaded XML document.
   * @param $doc DOMDocument which should be pinned onto this XMLDocument
   */
  protected function set_doc($doc = null) {
 
    if(Validator::isclass($doc,"DOMDocument")) $this->doc = $doc;
 
  }

  /**
   * Check if an XML file passed is valid against a DTD file passed.
   * @param $xml valid XML file 
   * @param $dtd DTD file $xml is checked against 
   * @return true if $xml follows $dtd, otherwise false
   */
  public static function is_valid($xml = "", $dtd = "") {

    global $filelogger;

    $filelogger->log("%, %",array($xml,$dtd),"DEBUG");

    if(Validator::isa($xml,"string") && Validator::isa($xml,"string")
       && file_exists($xml) && file_exists($dtd)
       && Validator::equals(mime_content_type($xml),"application/xml")
       && Validator::equals(mime_content_type($dtd),"text/plain")) {

      $xd = new DOMDocument(); $xd->load($xml);
      $di = new DOMImplementation();
      $root = $xd->documentElement->tagName;
      $dtd = $di->createDocumentType($root,"",$dtd);
      $d = $di->createDocument("",$root,$dtd);
      $n = $d->importNode($xd->documentElement,true);
      $d->replaceChild($n,$d->documentElement);
      $filelogger->log("%",array($d->saveXML()),"DEBUG");

      return @$d->validate(); 

    }

    return false; 

  }

  /**
   * Get the string representation of the loaded XML document.
   * @return string representation of the XML file.
   */  
  public function __toString() {
    return (!Validator::isa($this->doc,"null") ? $this->doc->saveXML() : ""); 
  }

  /**
   * Get string representation for a DOMDocument.
   * @param $doc DOMDocument to be transformed into string
   * @return string representation for <code>$doc</code> if it is a valid
   *                DOMDocument otherwise an empty string
   */
  public static function get($doc = null) {

    if (!Validator::isa($doc,"null") 
        && Validator::isa($doc,"object") 
        && Validator::isclass($doc,"DOMDocument"))
      return $doc->saveXML(); 

    return "";

  }

  /**
   * Send a xpath query to the XML document <code>$doc</code>.
   * @param $query XPath query
   * @param $raxd return nodes found as XMLDocument, useable when evaluating
   *              further xpathes disabled by default 
   * @param $ranl return nodes found as DOMNodeList, useable when further
   *              processing of elements found is needed
   * @return a DOMNodeList when $ranl is set to true, an XMLDocument if only
   *         $raxd is set to true, otherwise a string representing the nodes 
   *         found evaluating $query.
   * @throws InvalidXPathExpressionException
   * @see https://bugs.php.net/bug.php?id=70523
   */
  public function xpath($query = "/", $raxd = false, $ranl = false) {

    global $filelogger;

    $nodeeval = "";
    $domx = new DOMXpath($this->doc);
    $unresolved = false;    

    // catch some invalid xpath expressions before evaluation
    if(Validator::isa($query,"null")) {
      $filelogger->log("%",array( 
        new InvalidXPathExpressionException(
          "invalid xpath expression",1)),"ERR");
      throw(new InvalidXPathExpressionException(
        "invalid xpath expression",1));

    } else if (Validator::equals($query,"")) {
      $filelogger->log("%, query=%",array( 
        new InvalidXPathExpressionException(
          "invalid xpath expression",2)),"ERR");
      throw(new InvalidXPathExpressionException(
        "invalid xpath expression",2));

    }

    // NOTE: $nlist is boolean 'false' if evaluation fails, even for query 'false()'
    $nlist = @$domx->evaluate($query);
    if(Validator::equals($nlist,false)) {
      $filelogger->log("%, x=%, d=%, q=%",array( 
          new InvalidXPathExpressionException(),$this->xfile,$this->dfile,$query),
        "ERR");
      throw(new InvalidXPathExpressionException());
    }

    // return the node list as is when forced to do so
    if(Validator::equals($ranl,true)) return $nlist;

    // if there were some usable values 
    if(Validator::isa($nlist,"object") 
       && Validator::isclass($nlist,"DOMNodeList")) { 
 
      foreach($nlist as $n) {
 
        if (Validator::isclass($n,"DOMDocument")) {
          $nodeeval = $nodeeval." ".preg_replace("/<\?xml.*"."\?".">/","",
                                                 $n->saveXML());

        } else if (Validator::isclass($n,"DOMElement")) {
          $nodedoc = new DOMDocument();
          $nodedoc->appendChild($nodedoc->importNode($n->cloneNode(TRUE),TRUE));
          $nodeeval = $nodeeval." ".preg_replace("/<\?xml.*"."\?".">/","",
                                                 $nodedoc->saveXML());
 
        } else if (Validator::isclass($n,"DOMAttr")) {
          $nodeeval = $nodeeval." ".$n->name."=\"".$n->value."\"";
        
        } else if (Validator::isclass($n,"DOMText")) {
          $nodeeval = $nodeeval." ".$n->wholeText."";

        } else {

          $unresolved = true;
          break;

        }

      }
   
    } else if (Validator::isa($nlist,"string")
               || Validator::isa($nlist,"double")) {
      $nodeeval = $nodeeval."".$nlist;

    } else if (Validator::isa($nlist,"boolean")) {
      $nodeeval = $nodeeval."".($nlist ? "true" : "false");

    } else {

      $unresolved = true;

    }

    // throw an exception if there was an object class or return type unresolved
    // by this function
    if($unresolved === true) {

      $filelogger->log("%",array(new UnresolvedXPathException(
            "Unresolved XPath expression for ".
            (!Validator::isa($nlist,"object") ? "type " : "").gettype($nlist).
            (Validator::isa($nlist,"object") ? " class " : "").
            (Validator::isa($nlist,"object") ? get_class($nlist) : ""))),
          "ERR");

      throw(new UnresolvedXPathException(
            "Unresolved XPath expression for ".
            (!Validator::isa($nlist,"object") ? "type " : "").
            gettype($nlist).
            (Validator::isa($nlist,"object") ? " class " : "").
            (Validator::isa($nlist,"object") ? get_class($nlist) : ""))); 

    }

    // replace (multiple) white spaces and newline characters
    $nodeeval = preg_replace("/> </","><",
                  preg_replace("/^([ \t])+|([ \t])+$/","",
                    preg_replace("/([ \t])+/"," ",
                      preg_replace("/[\r\n]/"," ",$nodeeval))));

    // create another xmldocument for further xpath queries
    if($raxd === true) {
      $nex = new XMLDocument(null,null,false); 
      $nex->create_doc($nodeeval);
      $filelogger->log("created DOMDocument from string, str=%, nex=%", 
                       array($nodeeval, $nex->get_doc()));
      return $nex;
    } 

    return $nodeeval;

  }

  /**
   * Merge this XMLDocument with another one.
   * @param $xmldoc XMLDocument to merge this one with
   * @throws XMLMergeException
   */
  public function merge($xmldoc = null) {

    if(Validator::isclass($xmldoc,"core\util\xml\XMLDocument")
       && !Validator::isa($xmldoc->get_doc(),"null")
       && Validator::isclass($xmldoc->doc,"DOMDocument")) {

      // merge and override
      $nodes = $xmldoc->doc->childNodes; // this should always be the root 
                                         // element, if there are multiple
                                         // root elements the xml is not  
                                         // valid
      $this->join($nodes); 
      $this->doc->formatOutput = true;
      $this->logger->logge("joined document=%",
        array($this->doc->saveXML())); 

    } else {
      $this->logger->logge("%, xmldoc=%",
        array(new XMLMergeException("not a valid xml document",0),$xmldoc));
      throw(new XMLMergeException("not a valid xml document",0));
    }

  }

  /**
   * Helper to traverse an XMLDocument when merging two documents.
   * @param $nodes DOMNodeList of the joining document going to be traversed
   * @param $traversed xpath query defining the parent path traversed
   */
  private function join($nodes = null, $traversed = "") {

    $flushed = false;

    foreach($nodes as $n) {

      // xpathes for joining conditions
      $dxs = new DOMXpath($this->doc);

      // NOTE: childNodes can contain tag contents like DOMText,DOMCdataSection,
      //       DOMComment and other stuff therefor it need to be handled too 
      if(!Validator::isclass($n,"DOMElement")) {

        // if tag contents is seen flush any tag contents in the original, once
        if($flushed === false 
           && (Validator::isclass($n,"DOMText") 
               || Validator::isclass($n,"DOMCdataSection"))) { 

          $value = "";
          switch(@get_class($n)) {
            case "DOMText": $value=trim($n->wholeText); break;
            case "DOMCdataSection": $value=trim($n->data); break;
            default: break;
          }

          // only flush if we got non empty text elements, expecially DOMText
          if(!Validator::isempty($value)) {
            foreach($dxs->evaluate($traversed)[0]->childNodes as $rn){
              if(!Validator::isclass($rn,"DOMElement")) {
                $dxs->evaluate($traversed)[0]->removeChild($rn);
              }
            }
            $flushed = true; 
          }

        // warn about unrecognized/unhandled DOM classes
        } else if($flushed === true) {
          // but do not warn if textual contents was already flushed
        } else {
          $this->logger->logge("unrecognized DOM class=%",
            array(preg_replace("/\n/","",print_r($n,true))),"WARNING");
        } 

        // append any textual tag contents
        $cnode = $this->doc->importNode($n, true);
        $dxs->evaluate($traversed)[0]->appendChild($cnode);

        // skip further node execution for nodes <> DOMElement 
        continue; 

      } // if DOMElement   

      // set the main next xpath going to be traversed
      $next = $traversed."/".$n->tagName;

      // append node attributes to $next to choose the right node in case
      // there are multiple nodes with the same name, but use only those
      // unique node identifiers which are set by default or that were 
      // overridden using the "distinct" attribute in the root tag
      $next = $next."[1=1";
      foreach($this->unids as $unid) {
        if($n->hasAttribute($unid))
          $next = $next." and @".$unid."=\"".$n->getAttribute($unid)."\"";
      }
      $next = $next."]";

      // check if there exist an xpath in the original document
      if($dxs->evaluate($next)->length > 0) {
        // join only attributes as the node exists in the original document
        if($n->hasAttributes()) {
          foreach($n->attributes as $a) {
            $dxs->evaluate($next)[0]->setAttribute($a->name,$a->value);
          }
        }

      // otherwise fully append this node
      } else {
        $inode = $this->doc->importNode($n, true);
        $dxs->evaluate($traversed)[0]->appendChild($inode);
      }
 
      // if the node contains additional nodes, continue traversing
      if($n->hasChildNodes()) {
        $this->join($n->childNodes, $next);
      }

    } // foreach

  } // join

  /**
   * Concat two documents.
   * @param $xmldoc xml document to concatenate with the current one
   * @param $from xpath defining the location where to get nodes from
   * @param $to xpath defining the location where to concat nodes to
   * @param $sort flag to define if nodes will be inserted regarding $locator
   * @param $locator attribute or element to use as reference when sorting 
   */
  public function concat($xmldoc = null, $from = "", $to = "",
                         $sort = false, $locator = "order") {

    global $filelogger;

    // setup xpathes
    $xthis = new DOMXpath($this->doc);
    $xxdoc = new DOMXpath($xmldoc);

    // nodes to insert
    $xfrom = $xxdoc->evaluate($from);

    foreach($xfrom as $n) {

      // insert at the referencing location
      if(Validator::equals($sort,true)) {

        // attribute value of the current $from element to sort with
        $sattrv = intval($n->getAttribute($locator));

        // last match of $locator in $to, represents reference node in $to
        $lmix = intval($xthis->evaluate("count(".$to."/*[@".$locator."<=".$sattrv."])"));
        $filelogger->log("*****-0 lmix=%, %=%",
                         array($lmix,$locator,$sattrv),"DEBUG");

        // insert node 
        $ni = $this->doc->importNode($n,true);
        $filelogger->log("*****-1 ni=%,",array($ni),"DEBUG");
        $hasnodes = $xthis->evaluate($to)[0]->hasChildNodes();
        $cntnodes = intval($xthis->evaluate("count(".$to."/*)"));
        $filelogger->log("*****-2 hn='%', cn='%'", 
                         array($hasnodes,$cntnodes),"DEBUG");
        $filelogger->log("*****-3 incs=%",array($xthis->evaluate($to)[0]),"DEBUG");

        // if target index is 0 and there are no child nodes present
        //   or if $lmix points to the last node
        if(Validator::equals($lmix,$cntnodes)) {
          $filelogger->log("*****-4 appending node",array(),"DEBUG");
          $xthis->evaluate($to)[0]->appendChild($ni);

        // if target index is 0 but there already exist some child nodes
        } else if(Validator::equals($lmix,0) && $hasnodes) {
          $ref = $xthis->evaluate($to."/*[1]")[0];
          $filelogger->log("*****-5 inserting before root ref=%",array($ref),"DEBUG");
          $xthis->evaluate($to)[0]->insertBefore($ni,$ref);

        // otherwise we can use the nextSibling as reference node
        } else {
          $ref = $xthis->evaluate($to."/*[".$lmix."]")[0]->nextSibling;
          $filelogger->log("*****-6 inserting before sibling ref=%",array($ref),"DEBUG");
          $xthis->evaluate($to)[0]->insertBefore($ni,$ref);
        }

      // simply append under $to
      } else {

        $ni = $this->doc->importNode($n,true);
        $xthis->evaluate($to)[0]->appendChild($ni);

      } 
 

    }

  }
 
  /**
   * Adjust values of an xpath group.
   * @param $xpath xpath pointing to the group going to be updated
   * @param $nodekey key of the corresponding element in the $xpath group that
   *                 should be modified
   * @param $adjustment string going to be appended
   * @param $prefix specifies wheter to prefix the $nodekey or to suffix it
   */
  public function adjust($xpath = "/", $nodekey = "nodeValue", $adjustment = "", 
                         $prefix = true) {

    $xthis = new DOMXPath($this->doc);
    foreach($xthis->evaluate($xpath) as $n) {
      switch($nodekey) {
        case "nodeValue": 
            $n->nodeValue = $adjustment.$n->nodeValue; 
          break;
        default: break;
      }
    } 

  }

} // class

?>
