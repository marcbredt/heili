<?php

namespace core\config;

/**
 * This class is used to represent every configuration loaded.
 * @author Marc Bredt
 * @see ConfigurationManager
 * @see ConfigurationRegister
 * @see ConfigurationOperation
 */
class Configuration {

  /**
   * Unique configuration name. Uniqueness provided by the DTD file.
   */
  private $cn = "";

  /**
   * Valid configuration string. Used to build documents to work on.
   */
  private $xs = "";

  /**
   * XML file this Configuration was created from.
   */
  private $xf = ""; 

  /**
   * DTD file used to validate <code>$xf</code>
   */
  private $df = "";

  /*
   * NOTE: references in php are just alias definitions so no need
   *         to store them for the XMLDocument build from
   *       &$var is just the value of the variable passed itself 
   *        not the pointer, e.g. the stack address
   */

  /**
   * Creates a configuration representation.
   * @param $cname unique configuration name
   * @param $cstring current valid configuration string
   * @param $xmlfile string representing the file <code>$xs</code>
   *                 was built from
   * @param $dtdfile DTD file used to validate <code>$xmlfile</code>
   */
  public function __construct($cname = "", $cstring = "",
                              $xmlfile = "", $dtdfile = "") {
    $this->cn = $cname;
    $this->xs = $cstring;
    $this->xf = $xmlfile;
    $this->df = $dtdfile;

  }

}

?>
