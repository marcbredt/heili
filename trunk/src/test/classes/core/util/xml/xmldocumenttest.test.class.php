<?php

namespace test\core\util\xml;
use core\util\log\FileLogger as FileLogger;
use core\util\xml\XMLDocument as XMLDocument;
use core\exception\xml\XMLNotValidException as XMLNotValidException;
use core\exception\xml\XMLNoValidDTDException as XMLNoValidDTDException;
use core\exception\xml\xpath\UnresolvedXPathException as UnresolvedXPathException;
use core\exception\xml\xpath\InvalidXPathExpressionException as InvalidXPathExpressionException;

use \PHPUnit_Framework_TestCase as PHPUnit_Framework_TestCase;

/*!
 *
 * \page testoverview Testcase Overview
 * \section testutil Utilities
 * \subsection testutilset Testcases for class core\util\xml\XMLDocument
 *   PHPUnit_framework_TestCase from package phpunit in /usr/share/php/
 * 
 */
class XMLDocumentTest extends PHPUnit_Framework_TestCase {

  /**
   * XMLDocument that will be used.
   */
  private $xd = null;

 
  /** HELPERS **/

 
  private function init_global_logger() {
    global $filelogger; 
    $filelogger = new FileLogger("XMLDocumentTest","../../log/test/xmldocumenttest.log");
  }


  /** TESTS **/


  /**
   * Simple function to check if a XMLDocument can be
   * created using namespaces.
   * @small
   */
  public function testNamespace() {
    $this->init_global_logger();
    $this->xd = new \core\util\xml\XMLDocument(
                      "test/core/util/xml/files/xml/module-register.xml",
                      "test/core/util/xml/files/dtd/module-register.dtd");
    $this->assertNotNull($this->xd);
    $this->xd = NULL;
  }

  /**
   * Test access to the internal document created. 
   */
  public function testDocument() {
    $this->xd = new XMLDocument(
                      "test/core/util/xml/files/xml/module-register.xml",
                      "test/core/util/xml/files/dtd/module-register.dtd");
    $this->assertNotNull($this->xd);
    $this->assertNotNull($this->xd->get_doc());
    $this->assertInstanceOf('DOMDocument',$this->xd->get_doc());
    $this->xd = NULL;
  }

  /**
   * Test string representation for a xmldocument.
   */
  public function testStringRepresentation() {
    $this->xd = new XMLDocument(
                      "test/core/util/xml/files/xml/module-register.xml",
                      "test/core/util/xml/files/dtd/module-register.dtd");
    $this->assertNotEquals("",$this->xd->__toString());
    $this->assertStringStartsWith("<?xml ",$this->xd->__toString());
    $this->xd = NULL;
  }

  /**
   * Test gathering string representation for a DOMDocument
   * @req PHP >= 5.0.2, PHP_EOL
   */
  public function testDOMDocumentString() {
    $this->xd = new XMLDocument(
                      "test/core/util/xml/files/xml/module-register.xml",
                      "test/core/util/xml/files/dtd/module-register.dtd");
    $this->assertEquals("",$this->xd->get_doc_string(null));
    $this->assertEquals("",$this->xd->get_doc_string(""));
    $this->assertEquals("",$this->xd->get_doc_string(array()));
    $this->assertEquals("",$this->xd->get_doc_string(new \Exception()));
    $this->assertEquals("<?xml version=\"1.0\""."?".">".PHP_EOL,
                        $this->xd->get_doc_string(new \DOMDocument()));
    $this->assertNotEquals("",$this->xd->get_doc_string(
                                $this->xd->get_doc()));
    $this->assertStringStartsWith("<?xml ", $this->xd->get_doc_string(
                                              $this->xd->get_doc()));
    $this->xd = NULL;
  }

  /**
   * Test xpath evaluation.
   */
  public function testXPathEvaluations() {
    $this->xd = new XMLDocument(
                      "test/core/util/xml/files/xml/module-register.xml",
                      "test/core/util/xml/files/dtd/module-register.dtd");

    // DOMElement
    $this->assertStringStartsWith("<module",$this->xd->xpath("//module"));
    // DOMAttr
    $this->assertEquals("type=\"class\"",$this->xd->xpath("//module/@type"));
    // string
    $this->assertEquals("class",$this->xd->xpath("string(//module/@type)"));
    // DOMAttr, multiple attributes
    $this->assertEquals("type=\"class\" class=\"AccessibleObjectImpl\" ".
                          "context=\"rcontext\"",
                        $this->xd->xpath("//module/@*"));
    // DOMText
    $this->assertEquals("Some DOMText",$this->xd->xpath("//module/class/text()"));
    // double
    $this->assertEquals(1,$this->xd->xpath('count(//module[@type="class"])'));
    // double
    $this->assertEquals(2,
                        $this->xd->xpath('count(//module[@type="class"]/methods/*)'));
    // DOMDocument
    $this->assertStringStartsWith("<!DOCTYPE ",$this->xd->xpath()); 
    $this->assertStringStartsWith("<!DOCTYPE ",$this->xd->xpath('/')); 
    // boolean
    $this->assertEquals("true", $this->xd->xpath('true()'));
    // NOTE: evalutions to false are evil as the return value of evaluate
    //       returns 'false' too if the query is malformed
    //       https://bugs.php.net/bug.php?id=70523
    //       in this context it would throw an exception which is not 
    //         needed here :) therefor disable this assertion
    //$this->assertEquals("false", $this->xd->xpath('false()'));

    // TODO: other checks/tests
    //echo $this->xd->xpath('round(6.74363)');
    //echo $this->xd->xpath('number(count(//module[@type="class"]/methods/*))');

    $this->xd = NULL;
  } 

  /**
   * Tests the merge of two XMLDocuments.
   * @small
   */
  public function testMerge() {
    // unids replace, "/@distinct='id,name'"
    //$x1 = new XMLDocument();
    // node merge
    // attribute merge
    // node override
    // atrribute override
    // multiple same named nodes with different attributes
    // merge (with) empty docs
  }

  /**
   * Tests the exceptions used by this class.
   * @small
   */
  public function testExceptions() {

    // get a logger instance pointing to the default logging file
    $fl = new FileLogger("XMLDocumentTest");

    // XMLNotValidException
    // xml mimetype error    
    $catched = false;
    $tf = tempnam("/tmp","PHP");
    $tfh = fopen($tf,"w+");
    fwrite($tfh,"<root />"); // without <?xml>-head mimetype is not application/xml
    fseek($tfh,0);
    try {
      $this->xd = new XMLDocument(
                        $tf,"test/core/util/xml/files/dtd/module-register.dtd");
    } catch(XMLNotValidException $xnve) { $catched = true; }
    $this->xd = null;
    fclose($tfh);
    unlink($tf);
    if(!$catched) { // fail after temp file cleanage
      $this->fail('Failed asserting that exception of type '.
                  '"XMLNotValidException [0]" is thrown.'); }
    $line = $fl->getf()->getlast();
    $this->assertRegExp('/.* XMLDocument \[error\] core\\\\util\\\\log\\\\FileLogger core\\\\exception\\\\xml\\\\XMLNotValidException \[0\]/', $line);
    
    // xml file = null
    $catched = false;
    try {
      $this->xd = new XMLDocument(
                        null,
                        "test/core/util/xml/files/dtd/module-register.dtd");
    } catch(XMLNotValidException $xnve) { $catched = true; }
    $this->xd = null;
    if(!$catched) { // fail after temp file cleanage
      $this->fail('Failed asserting that exception of type '.
                  '"XMLNotValidException [0]" is thrown.'); }
    $line = $fl->getLastLine();
    $this->assertRegExp('/.* XMLDocument \[error\] core\\\\util\\\\log\\\\FileLogger core\\\\exception\\\\xml\\\\XMLNotValidException \[0\]/', $line);
 
    // xml file does not exist
    $catched = false;
    try {
      $this->xd = new XMLDocument(
                        "test/core/util/xml/files/xml/".
                        bin2hex(mcrypt_create_iv(32,MCRYPT_DEV_URANDOM)).".xml",
                        "test/core/util/xml/files/dtd/module-register.dtd");
    } catch(XMLNotValidException $xnve) { $catched = true; }
    $this->xd = null;
    if(!$catched) { // fail after temp file cleanage
      $this->fail('Failed asserting that exception of type '.
                  '"XMLNotValidException [0]" is thrown.'); }
    $line = $fl->getLastLine();
    $this->assertRegExp('/.* XMLDocument \[error\] core\\\\util\\\\log\\\\FileLogger core\\\\exception\\\\xml\\\\XMLNotValidException \[0\]/', $line);
  
    // validation failure
    $catched = false;
    $tf = tempnam("/tmp","PHP");
    $tfh = fopen($tf,"w+");
    fwrite($tfh,"<!ELEMENT root EMPTY>");
    fseek($tfh,0);
    try {
      $this->xd = new XMLDocument(
                        "test/core/util/xml/files/xml/module-register.xml",
                        $tf);
    } catch(XMLNotValidException $xnve) { $catched = true; }
    $this->xd = null;
    fclose($tfh);
    unlink($tf);
    // fail after temp file cleanage 
    if(!$catched) { 
      $this->fail('Failed asserting that exception of type '.
                  '"XMLNotValidException [2]" is thrown.'); }
    $line = $fl->getLastLine();
    $this->assertRegExp('/.* XMLDocument \[error\] core\\\\util\\\\log\\\\FileLogger core\\\\exception\\\\xml\\\\XMLNotValidException \[2\]/', $line);

    // XMLNoValidDTDException
    $catched = false;
    try {
      $this->xd = new XMLDocument(
                        "test/core/util/xml/files/xml/module-register.xml",
                        "test/core/util/xml/files/dtd/".
                          bin2hex(mcrypt_create_iv(32,MCRYPT_DEV_URANDOM)).".dtd");
    } catch(XMLNoValidDTDException $xnvde) { $catched = true; }
    $this->xd = null;
    if(!$catched) { 
      $this->fail('Failed asserting that exception of type '.
                  '"XMLNoValidDTDException" is thrown.'); }
    $line = $fl->getLastLine();
    $this->assertRegExp('/.* XMLDocument \[error\] core\\\\util\\\\log\\\\FileLogger core\\\\exception\\\\xml\\\\XMLNoValidDTDException \[0\]/', $line);

    // InvalidXPathExpression
    // query = null
    $catched = false;
    try {
      $this->xd = new XMLDocument(
                        "test/core/util/xml/files/xml/module-register.xml",
                        "test/core/util/xml/files/dtd/module-register.dtd");
      $this->xd->xpath(null);
    } catch (InvalidXPathExpressionException $ixee) { 
      $catched = true; }
    $this->xd = null;
    if(!$catched) { 
      $this->fail('Failed asserting that exception of type '.
                  '"InvalidXPathExpressionException" is thrown.'); }
    $line = $fl->getLastLine();
    $this->assertRegExp('/.* XMLDocument \[error\] core\\\\util\\\\log\\\\FileLogger core\\\\exception\\\\xml\\\\xpath\\\\InvalidXPathExpressionException \[1\]: .*/', $line);

    // query empty
    $catched = false;
    try {
      $this->xd = new XMLDocument(
                        "test/core/util/xml/files/xml/module-register.xml",
                        "test/core/util/xml/files/dtd/module-register.dtd");
      $this->xd->xpath('');
    } catch (InvalidXPathExpressionException $ixee) { 
      $catched = true; }
    $this->xd = null;
    if(!$catched) { 
      $this->fail('Failed asserting that exception of type '.
                  '"InvalidXPathExpressionException" is thrown.'); }
    $line = $fl->getLastLine();
    $this->assertRegExp('/.* XMLDocument \[error\] core\\\\util\\\\log\\\\FileLogger core\\\\exception\\\\xml\\\\xpath\\\\\InvalidXPathExpressionException \[2\]: .*/', $line);

    // a malformed query
    $catched = false;
    try {
      $this->xd = new XMLDocument(
                        "test/core/util/xml/files/xml/module-register.xml",
                        "test/core/util/xml/files/dtd/module-register.dtd");
      $this->xd->xpath(')');
    } catch (InvalidXPathExpressionException $ixee) { 
      $catched = true; }
    $this->xd = null;
    if(!$catched) { 
      $this->fail('Failed asserting that exception of type '.
                  '"InvalidXPathExpressionException" is thrown.'); }
    $line = $fl->getLastLine();
    $this->assertRegExp('/.* XMLDocument \[error\] core\\\\util\\\\log\\\\FileLogger core\\\\exception\\\\xml\\\\xpath\\\\\InvalidXPathExpressionException \[0\]: .*/', $line);

    // UnresolvedXPathException
    // NOTE: as comments can be ignored, use the DOMComment class
    //       to cause such an exception
    $catched = false;
    try {
      $this->xd = new XMLDocument(
                        "test/core/util/xml/files/xml/module-register.xml",
                        "test/core/util/xml/files/dtd/module-register.dtd");
      $this->xd->xpath('//comment()');
    } catch(UnresolvedXPathException $uxe) { $catched = true; }
    $this->xd = NULL;
    if(!$catched) { 
      $this->fail('Failed asserting that exception of type '.
                  '"UnresolvedXPathException" is thrown.'); }
    $line = $fl->getLastLine();
    $this->assertRegExp('/.* XMLDocument \[error\] core\\\\util\\\\log\\\\FileLogger core\\\\exception\\\\xml\\\\xpath\\\\UnresolvedXPathException \[0\]: .*/', $line);
   
    // NOTE: the xpath function now contains a second parameter to
    //       test UnresolvedXPathException to be throuwn for unrecognized
    //       typed results of DOMXPath::evaluate
    //       e.g. null is a type (returned by an xpath expression) that 
    //       is not necessary to capture, like DOMComment to
    //       fulfill tests on all lines (see 218/$unresolved in xpath)
    $catched = false;
    try {
      $this->xd = new XMLDocument(
                        "test/core/util/xml/files/xml/module-register.xml",
                        "test/core/util/xml/files/dtd/module-register.dtd");
      $this->xd->xpath("/",true);
    } catch(UnresolvedXPathException $uxe) { $catched = true; }
    $this->xd = NULL;
    if(!$catched) { 
      $this->fail('Failed asserting that exception of type '.
                  '"UnresolvedXPathException" is thrown.'); }
    $line = $fl->getLastLine();
    $this->assertRegExp('/.* XMLDocument \[error\] core\\\\util\\\\log\\\\FileLogger core\\\\exception\\\\xml\\\\xpath\\\\UnresolvedXPathException \[0\]: .*/', $line);
   

  }

  // concat tests XMLDocument::concat()
  //   nosort, sort(lix=0&nonodes,lix=0&nodes,)
  

  // merge tests XMLDocument::merge()
  //   sorted
 
}
