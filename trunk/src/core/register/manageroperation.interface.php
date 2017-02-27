<?php

namespace core\register;

/**
 * Declare some operations needed to implement a register.
 * @author Marc Bredt
 * @see Register 
 * @see <a style="font-weight: bold;" 
 *         href="classcore_1_1config_1_1Configuration.html">Configuration</a>
 */
interface ManagerOperation {

  /**
   * Load a configuration by name. Loads the configurtion string
   * registered into a document, e.g. DOMDocument, XMLDocument
   * @param $name unique name for the configuration to be loaded.
   * @param $type way in
   * @return should return a valid Configuration 
   * @see <a style="font-weight: bold;" 
   *         href="classcore_1_1config_1_1Configuration.html">Configuration</a>
   * @see <a style="font-weight:bold;"
   *         href="http://php.net/manual/en/class.domdocument.php">DOMDocument</a>
   * @see <a style="font-weight: bold;" 
   *         href="classcore_1_1util_1_1xml_1_1XMLDocument.html">XMLDocument</a>
   */
  public function load($name = null, $type = null);

  /**
   * Unload a configuration by name. 
   * @param $name unique name for the configuration to be loaded.
   * @param $type way in
   * @return should return a valid Configuration 
   * @see <a style="font-weight: bold;" 
   *         href="classcore_1_1config_1_1Configuration.html">Configuration</a>
   * @see <a style="font-weight:bold;"
   *         href="http://php.net/manual/en/class.domdocument.php">DOMDocument</a>
   * @see <a style="font-weight: bold;" 
   *         href="classcore_1_1util_1_1xml_1_1XMLDocument.html">XMLDocument</a>
   */
  public function unload($name = null, $type = null);

  /**
   * List configuration names loaded or informations for a specific 
   * configuration if $gets is true.
   * @param $name unique configuration name
   * @param $gets if $name is specified and valid get a serialized
   *              version of the configuration
   * @return serialized representation of the configuration specified
   *         or a string of all 
   */
  public function info($name = null, $gets = false);

  /**
   * Register/adds a specific configuration.
   * @param $name unique configuration name
   * @param $conf Configuration object
   * @return true if registration was successful otherwise false
   */
  public function register($name = null, $conf = null);

  /**
   * Unregister/removes a specific configuration.
   * @param $name unique configuration name
   * @param $conf Configuration object
   * @return true if registration was successful otherwise false
   */
  public function unregister($name = null, $conf = null);

}

?>
