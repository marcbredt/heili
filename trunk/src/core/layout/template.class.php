<?php

namespace core\layout;

use \DOMImplementation as DOMImplementation;
use \DOMDocument as DOMDocument;
use \DOMXpath as DOMXpath;
use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;
use core\util\file\File as File;
use core\layout\TemplateMap as TemplateMap;
use core\mask\Mask as Mask;
use core\mask\MaskLoader as MaskLoader;
use core\exception\layout\TemplateException as TemplateException;

/**
 * This class is used to load and build HTML pages from pure HTML template
 * or main layout files adding module chunks via a module map.
 * TODO: HTML 5 conformity
 * @author Marc Bredt
 * @see https://www.w3.org/TR/html5/
 */
class Template {

  private $tid = "html";
  //private $tid = "html";
 
  private $pid = "-//W3C//DTD XHTML 1.0 Strict//EN";
  //private $pid = "";

  private $sid = "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd";
  //private $sid = "";

  private $dtd = null;

  private $tname = null;

  private $tfile = null;

  private $tmfile = null;

  private $tmap  = null;

  private $tcss  = null;
  
  /**
   * Document storing the template being to be modified.
   */ 
  private $template = null;

  /**
   * Initialize the template. Creates currently only an XHTML document.
   * TODO: Think about caching built templates as every request would invoke
   *       the template build process causing probably overhead to the 
   *       application nodes.
   * TODO: Merge templates.xml with, e.g. a mod template
   * @param $tname template name
   * @param $tfname template file name, a specific template html "$tfname.html"
   * @return void
   * @see https://www.w3.org/2010/04/xhtml10-strict.html
   */
  public function __construct($tname = "heili", $tfname = "heili") {
    
    global $filelogger;

    // set the template files
    $tfile  = PATH_TEMPLATES.DIRECTORY_SEPARATOR.$tname.
                             DIRECTORY_SEPARATOR.$tfname.".html";
    $tmfile = PATH_TEMPLATES.DIRECTORY_SEPARATOR.$tname.
                             DIRECTORY_SEPARATOR."map.xml";
    $tcss   = PATH_TEMPLATES.DIRECTORY_SEPARATOR.$tname.
                             DIRECTORY_SEPARATOR.$tname.".css";

    // check if valid template files exists
    if(file_exists($tfile) && file_exists($tmfile) && file_exists($tcss) 
       && Validator::equals(mime_content_type($tfile),"text/html")
       && Validator::equals(mime_content_type($tmfile),"application/xml")) {

      $filelogger->info("creating template % from %",array($tname,$tfile));

      // set up the template
      $this->tname = $tname;
      $this->tfile = $tfile;
      $this->tmfile = $tmfile;
      $this->tcss  = $tcss;


      // setup the template document
      $this->setup_doc();

      // setup then template's main css reference
      $this->setup_css();

      // setup the template map
      $this->setup_map();

      // modify the template, adding modules defined by the template map
      $this->build();

    } else {

      $filelogger->crit("%, tfile?=(%,%), tmfile?=(%,%), tcss?=(%,%), ".
                          "mt_tfile?=(%,%), mt_tmfile?=(%,%)",
                        array(new TemplateException("invalid template",0),
                              $tfile, file_exists($tfile), 
                              $tmfile, file_exists($tmfile),
                              $tcss, file_exists($tcss),
                              mime_content_type($tfile),
                              Validator::equals(mime_content_type($tfile),
                                "text/html"),
                              mime_content_type($tmfile),
                              Validator::equals(mime_content_type($tmfile),
                                "application/xml")
                             ));
      throw new TemplateException("invalid template files",0);

    }

  }

  /**
   * Setup the initial $this->template document.
   */
  private function setup_doc() {

    // set up an empty DOMDocument addressing the DOMDocumentType set
    $di = new DOMImplementation();
    $this->dtd = $di->createDocumentType($this->tid,$this->pid,$this->sid);
    $this->template = $di->createDocument("",$this->tid,$this->dtd); 
  
    // temporary document to load the template into as DOMDocument does not
    // provide a way to set the document's type
    $tmp = new DOMDocument();
    $tmp->loadHTMLFile($this->tfile);
    $tmpx = new DOMXpath($tmp);

    // merge the main template with html, especially attributes in root tags
    // otherwise doctype will e.g. default to HTML if not set
    $this->merge($tmpx->evaluate("/html")[0]);

  }

  /**
   * Setup the css rerefence in the template.
   */
  private function setup_css(){
   
    global $filelogger;

    $filelogger->info("setting up css link");

    // create a symbolic link to the main css file depending on css_main 
    // link's "@href" amount of sublevels
    $cssflnk = PATH_CSS.DIRECTORY_SEPARATOR.$this->tname.".css";
    if(!file_exists($cssflnk) && !is_link($cssflnk))
      symlink("..".DIRECTORY_SEPARATOR.$this->tcss,$cssflnk);
    
    $tx = new DOMXpath($this->template);
    $csslnxp = "/html/head/link[@id=\"css_main\" and @rel=\"stylesheet\" and ".
                                 "@type=\"text/css\"]/@href";
   
    $csslink = $tx->evaluate($csslnxp)[0];
    if(!Validator::isa($csslink,"null")) $csslink->value = $cssflnk;
    else $filelogger->err("required link (%) not available",array($csslnxp));

  }

  /**
   * Setup the template map defining where to load masks into.
   */
  private function setup_map() {

    // build the template map and import each mask into the template
    // set up the layout map
    $this->tmap = new TemplateMap($this->tmfile);
    $this->tmap->load();

  }

  /**
   * Redefines the template document. Without redefinig included masks will
   * not be detected as nodes properly.
   */
  private function redefine(){

    global $filelogger;

    $tmp = new DOMDocument();
    if(($ct=$this->get()) && $tmp->loadHTML($ct)) {
      $this->template = $tmp;
      $filelogger->debug("modified template=\n%",array($ct));
    } else {
      $filelogger->err("creating modified template failed.");
    }

  }

  /**
   * Add additional HTML code or nodes respectively into template's document.
   * @param $html HTML string going to be inserted
   * @param $target xpath query $html nodes going to be inserted to
   * @param $index position all the nodes going to be inserted at
   * @param $type target type to insert, e.g. "node" or "attr"
   * @return void 
   */
  public function add($html = "", $target = "/html/body", $index = 0, 
                       $type = "node") {

    global $filelogger;

    switch($type) {
    
      case "node" : 

        // nodes that will be inserted
        $d = new DOMDocument(); 
        if(!Validator::isempty($html)) @$d->loadHTML($html); 
        $dx = new DOMXpath($d);
        $filelogger->debug("target=%,index=%,html=\n%,doc=\n%",
                           array($target,$index,$html,$d->saveXML()));
       
        // simple text will be converted to a <p>-node, therefor get the text()
        if(!StringUtil::contains($html,"<p>") && !StringUtil::contains($html,"</p>")
           && $dx->evaluate("count(/html/body/p)>0"))
          $nodes = $dx->evaluate("/html/body/p/text()");
        else 
          $nodes = $dx->evaluate("/html/body/*");

        // insert nodes at $target if the target node is available
        $tx = new DOMXpath($this->template);
        $navail = $tx->evaluate("count(".$target.")=0");
        if(Validator::equals($navail,true)) {
          $filelogger->log("node '%' not found",array($target), "ERR");

        } else if(Validator::equals($navail,false)) {

          $ref = $tx->evaluate($target."/*")[$index+1];

          foreach($nodes as $n) {

            // NOTE: nodes added with its contents seem to not create the
            //       subnodes because querying xpathes on the modified template
            //       will not return any subnodes just appended
            //       therefor reinitialize the template from string
            $ni = $this->template->importNode($n,true); 
            $tx->evaluate($target)[0]->insertBefore($ni,$ref);
            $filelogger->log("added node | ni=%, ref=%",array($ni,$ref),"DEBUG");

          }
       
        }

        break;

      case "attr" : 
        break;

    }

    // validate the modified template against the doctype set
    /*
       NOTE: As validation need to invoke a stream request which is probably 
             blocked by any firewall and the contents being validated probably
             contains HTML special chars to avoid UTF-8 codes for letters in 
             the final template echo'd it is better to not validate the 
             modified template after any inclusion and leave the doctype
             correctness to the developer(s).
    */
    /*
    $filelogger->log("valid?=%",
                     array($this->validate()),"DEBUG");
    if(Validator::equals($this->validate(),false)) {
      $filelogger->log("%, doctype=%, html=%",
                       array(new TemplateException("validation failed",1),
                             $this->template->doctype,$this->get()),
                       "ERROR");
      throw(new TemplateException("validation failed",1));
    }
    */

  }

  /**
   * Load all module masks from module map.
   * @return void
   */ 
  private function build() {

    global $filelogger;

    $filelogger->info("loading configured (%) template masks ...", 
                     array(count($this->tmap)));

    foreach($this->tmap as $key => $tme) {

      // load the mask only if the template contains the target node
      $tx = new DOMXpath($this->template);
      $tnl = $tx->evaluate($tme->get_target());
      $filelogger->debug("key=%, tnl=%, tme=%",array($key,$tnl,$tme));

      if($tnl->length>0) {  
 
        $m = new Mask($tme->get_mask(),$tme->get_template(),$tme->get_type(),
                      $tme->get_module());
        $filelogger->debug("mask = %", array($m));
        $this->add(MaskLoader::load($m),$tme->get_target(), 
                   $tme->get_index(),$tme->get_ttype());

      } else {
        $filelogger->warn("xpath node '%' not found in current template=\n%",
                          array($tme->get_target(),$this->get()));
        $tnl = $tx->evaluate("//div[@id=\"header\"]");
        $filelogger->debug("template node list = [ % ]",array($tnl));
        foreach($tnl as $n) $filelogger->debug("node=%",array($n));
 
      }

      // redefine the modified template
      $this->redefine();
  
    }
    
  }

  /**
   * Merge DOMDocument nodes. As this class provides the default document type
   * to be set it is necessary to merge the main document's frame with the 
   * template. Especially the root node needs to be kept in a way any attribute 
   * provided in the html template is included while keeping the document's 
   * type forced by this class.
   * @return void
   */
  private function merge($node = null) {

    global $filelogger;

    // only join templates if a DOMElement is provided with the tag id set
    if(Validator::isclass($node,"DOMElement") 
       && Validator::equals($node->tagName,$this->tid)) {

      $tx = new DOMXpath($this->template);
      $ni = $this->template->importNode($node,true);    
      $no  = $tx->evaluate("/html")[0];
      $this->template->replaceChild($ni,$no);

      $filelogger->log("t='%'",
                       array($this->get()),"DEBUG");

    }

  }

  /**
   * Validate the adjusted template against the DOMDocumentType.
   * NOTE: As validation need to invoke a stream request which is probably 
   *       blocked by any firewall and the contents being validated probably
   *       contains HTML special chars to avoid UTF-8 codes for letters in 
   *       the final template echo'd it is better to not validate the 
   *       modified template after any inclusion and leave task of checking
   *       the doctype correctness to the developer(s).
   *       Anyways this function stays as dead code to demonstrate how remote
   *       DTD validation could be realized
   * @return true if the current template is is following the document's type
   *         definition, otherwise false
   * @see DOMDocumentType
   * @see http://php.net/manual/en/function.libxml-set-streams-context.php
   */
  private function validate() {

    $opts = array(
              "http" => array(
                          "user_agent" => "PHP libxml agent",
                        )
            );

    $context = stream_context_create($opts);
    libxml_set_streams_context($context);

    return $this->template->validate();
  }

  /**
   * Get the current formatted HTML template.
   * @return formatted html template
   */
  public function get() {

    global $filelogger;

    $this->template->formatOutput = true;

    if(Validator::matches($this->template->doctype->publicId,"/XHTML/")) {
      return htmlspecialchars_decode($this->saveXHTML());

    } else if(Validator::matches($this->template->doctype->publicId,"/HTML/")) {
      return htmlspecialchars_decode($this->saveHTML());

    } else {
   
      $filelogger->log("unknow document type, ".
                         "tid=%, pid=%, sid=%",
                       array(
                         $this->template->doctype->name,
                         $this->template->doctype->publicId,
                         $this->template->doctype->systemId
                       ),
                       "WARNING");
      return false;

    }
 
  }

  /**
   * For XHTML documents DOMDocument::saveXML() need to be used to create valid
   * tags especially self closing ones. This function additionally removes any 
   * "<?xml.*?>" tag prepended by DOMDocument::saveXML().
   * DOMDocument::saveXML() additionally creates another xmlns attribute. To get
   * a valid XHTML document string strip one of those attributes from the html
   * tag.
   * @return modified DOMDocument::saveXML()
   */
  private function saveXHTML() {
    return preg_replace("/xmlns=\".*\" xmlns=/", "xmlns=",
             preg_replace("/<\?xml.*\n/","",$this->template->saveXML()));
  }

  /**
   * Get a valid HTML representation for this template.
   * Can be used whenever the hardcoded DOMDocumentType is changed into HTML.
   * @return DOMDocument::saveHTML()
   */
  private function saveHTML() {
    return $this->template->saveHTML();
  }

  public function get_map() {
    return $this->tmap;
  }

}

?>
