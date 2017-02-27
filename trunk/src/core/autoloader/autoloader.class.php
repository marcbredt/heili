<?php

namespace core\autoloader;
use core\autoloader\Devisor as Devisor;

/**
 * This class is used to autoload class files from (sub)directories
 * passed following the namespace convention. The spl_autoload_register 
 * function is overridden to avoid the forced lowercase classname lookup.
 * This class can additionally be used to load files in a given namespace 
 * if the namespace definition follows the directory structure.
 * @author Marc Bredt
 */
class AutoLoader { 

  /**
   * Root directory where classes should be loaded from.
   * Should be the root directory of the aplication to fit
   * namespaces.
   */
  private $rdir = null;
  
  /**
   * Stores (sub)directories containing class files.
   */
  private $cdirs = array();

  /**
   * Stores the old include path.
   */
  private $old_include_path = null;

  /**
   * Stores the modified include path.
   */
  private $include_path = null;

  /**
   * Stores old extension for loadable classes.
   */
  private $old_extensions = "";

  /**
   * Default extension for loadable classes.
   */
  private $extensions = null;

  /**
   * Saves previously set spl autoloading functions.
   */
  private $osaf = null;

  /**
   * Shows if the autoloader was already initialized.
   */
  private $load = false;

  /**
   * Makes $this->rdir available to a callable scope
   * @see AutoLoader::register_autoload
   */
  private $rd = null; 

  /**
   * Makes $this->extension available to a callable scope
   * @see AutoLoader::register_autoload
   */
  private $xt = null;

  /**
   * Makes $this->testing available to a callable scope
   * @see AutoLoader::register_autoload
   */
  private $ts = null;

  /**
   * Loads autoloadable classes and modifies pathes.
   * @param $load set to false if the AutoLoader should not be 
   *              initialized on creation, default is true
   * @param $dir directory to search for classes.
   * @param $extensions default class filename extension.
   */
  public function __construct($load = true, $dir = ".", $extensions = "") {

    // extensions
    if ( strncmp(gettype($extensions),"string",6)==0 
         && strncmp($extensions, "", 1)!=0 ) {
      $this->extensions = $extensions;
    } else {
      $this->extensions = ".class.php";
    } 

    // root directory
    $this->rdir = $dir;

    // initializing just once
    if(strncmp(gettype($load),"boolean",7)==0) $this->load = $load;
    if($this->load) { $this->load = false; $this->load(); }

  }

  /**
   * Loads and registers the main autoload functions.
   * @see http://www.php.net/manual/en/function.spl-autoload-register.php#96804
   */
  public function load() {

    // check if initilization was done on creation, if not initialize it
    if(!$this->load) {

      // save the old include path
      $this->old_include_path = get_include_path();
    
      // set the include path with dirs from $this->cdirs
      set_include_path(get_include_path().PATH_SEPARATOR.$this->rdir);
   
      // register extensions
      $this->old_extensions = spl_autoload_extensions();
      spl_autoload_extensions($this->extensions);

      // store registered autoloading functions
      $this->osaf = spl_autoload_functions(); 

      // register the autoload function
      $this->register_autoload();

      // update the current include path
      $this->include_path = get_include_path();
   
      // avoid reinitialization
      $this->load = true;

    }

  }

  /**
   * Registers an autoload function.
   * @return true on success else false 
   */
  private function register_autoload() {

    // get some necessary variables to be passed as references to the closure
    // via "use" construct
    $rd = $this->rdir;
    $xt = $this->extensions;

    return spl_autoload_register(
             function($class) use (&$rd, &$xt) {
               call_user_func(array(new Devisor($rd,$xt),'load'), $class);
             }
           );
  }

  /**
   * Register additonal extensions.
   * @param $extension extension that should be registered too
   */
  public function expand($extension = "") {
    if(strncmp(gettype($extension),"string",6)!=0) $extension = "";
    if(strncmp($extension,"",1)!=0) {
      $this->extensions = preg_replace("/^,+|,+$/","",
                                        $this->extensions.",".$extension);
      spl_autoload_extensions($this->extensions);
    }
  }

  /**
   * Unload the autoloader.
   * @see Devisor
   */
  public function unload() {

    // just unload if the autoloader has already been initialized
    if($this->load) {

      set_include_path($this->getop());
      spl_autoload_extensions($this->getoe());
      $this->old_include_path = null;
      $this->include_path = null;
      $this->old_extensions = null;
      $this->extensions = null;
      $this->load = false;
    }

  }

  /**
   * Get current include path.
   * @return string modified include path.
   */
  public function getp() {
    return $this->include_path;
  }

  /**
   * Get current old include path.
   * @return string containing old include path.
   */
  public function getop() {
    return $this->old_include_path;
  }

  /**
   * Get the (spl) classes currently loaded.
   * @return array containing all available classes.
   */
  public function getc() {
    return get_declared_classes();
  }

  /**
   * Get the extensions currently registered.
   * @return string containing extension currently registered through
   *                spl_autoload_extensions.
   */
  public function gete() {
    return $this->extensions;
  }

  /**
   * Get the old extensions registered.
   * @return string containing old extension registered
   */
  public function getoe() {
    return $this->old_extensions;
  }
 
  /**
   * Get the old spl autoloading functions stored.
   * @return array with previously stored spl autoloading functions
   */
  public function getosaf() {
    return $this->osaf;
  }

  /**
   * Dump instace info.
   * @return string containing instance representation
   */
  public function __toString() {
    return get_class($this)."-".spl_object_hash($this).": ".
             preg_replace("/ +/", " ",
               preg_replace("/[\r\n]/", "", var_export($this,true)));
  }

}

?>
