<?php

namespace core;
use \core\AutoLoadingDevisor as AutoLoadingDevisor;
use \ReflectionFunction as ReflectionFunction;

/**
 * This class is used to autoload class files from (sub)directories
 * passed. The spl_autoload_register function is overridden to avoid
 * the forced lowercase classname lookup. This class can be additionally
 * used to load files in a given namespace if the namespace definition
 * follows the directory structure.
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
   * Shows if the autoloader was already initialized.
   */
  private $load = false;

  /**
   * Defines the AutoLoader state. Can be modified using
   * <code>$this->sett(true);</code>.
   */
  private $testing = false;

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
   * @param $extension default class filename extension.
   */
  public function __construct($load = true, $dir = ".", $extensions = "") {

    // required to register the autoloading function as it contains it.
    require_once("autoloadingdevisor.class.php");

    //extensions
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
   * Unloads autoloadable classes and restores pathes.
   * NOTE: The garbage collector will invoke this function on a scripts end.
   *       This leads to strange behaviourin testing scenarios, e.g. 
   *       resetting object variables, in a way further tests probably fail.
   *       Therefor the unloading should be done explicitly and not by the GC.
   */
  public function __destruct() {
    //$this->unload();
  }

  /**
   * Loads and registers the main autoload functions.
   * @see http://www.php.net/manual/en/function.spl-autoload-register.php#96804
   */
  public function load() {

    // check if initilization was done on creation, if not initialize it
    if(!$this->load) {

      // save the old include path
      //echo "oip=".get_include_path()."\n\n";
      $this->old_include_path = get_include_path();
    
      // get root and subdirectories to store into include path
      array_push($this->cdirs,$this->rdir);
      $this->getd($this->rdir);

      // set the include path with dirs from $this->cdirs
      foreach($this->cdirs as $cd)
        set_include_path(get_include_path().PATH_SEPARATOR.$cd);
   
      // register extensions and autoloader function
      $this->old_extensions = spl_autoload_extensions();
      spl_autoload_extensions($this->extensions);

      // register the autoload function
      $this->register_autoload();

      // update the class' include path
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

    // NOTE: see AutoLoadingDevisor for explanations on this 
    //       registering setup

    // define some variables to be used as globals to be able to 
    // pass $this->rdir/extensions onto the callback function
    // or the AutoLoadingDevisor respectively
    global $rd, $xt, $ts;
    $rd = $this->rdir;
    $xt = $this->extensions;
    $ts = $this->testing;

    return spl_autoload_register(function($class){
        global $rd, $xt, $ts;
        call_user_func(array(new AutoLoadingDevisor($rd,$xt),
                              'autoloadingfunction'), 
                        $class, $ts);
             }
           );
  }

  /**
   * Unregister all autoload functions registered through this class.
   * To access Closure instances the class ReflectionFunction is used 
   * which allows determination of class objects bound to this object.
   * @return true on success else false
   * @see ReflectionFunction
   */
  private function unregister_autoload() {

    // first check if we need to check closures as there are some
    if(spl_autoload_functions()) {

      $rf = null;
      foreach(spl_autoload_functions() as $k => $f) {

        // then access closures via ReflectionFunction
        $rf = new ReflectionFunction($f);

        if($rf->isClosure()) { 
          $t = $rf->getClosureThis();

          if(!is_null($t) && strncmp(gettype($t),"object",6)==0 
             && strncmp(get_class($t),
                  get_class($this),strlen(get_class($this)))==0) {
            if(!spl_autoload_unregister($f)) return false;
          }

        }

      }

    }

    return true;

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
   * Unload the autoloaer.
   * @see AutoLoader::unregister_autoload()
   * @see AutoLoadingDevisor
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
      $this->unregister_autoload();
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
   * Scans a directory for subdirectories to load 
   * @param $dir directory to search for subdirectories
   */
  private function getd($dir = ".") {
    if(!$dir || strncmp($dir,"",1)==0 || !is_dir($dir)) $dir = ".";
    foreach(scandir($dir) as $f) {
      if( $f==='.' || $f==='..' ) 
        continue; 
      if(is_dir($dir.DIRECTORY_SEPARATOR.$f)) {
        array_push($this->cdirs,$dir.DIRECTORY_SEPARATOR.$f);
        $this->getd($dir.DIRECTORY_SEPARATOR.$f);
      }
    }
  }

  /**
   * Get the testing state.
   * @return testing state for this class
   */
  public function gett() {
    return $this->testing;
  }

  /**
   * Set the testing flag which is used to test the
   * auolading function. 
   * @param boolean sets the testing state for this class
   */
  public function sett($testing = false) {
    if(strncmp(gettype($testing),"boolean",7)==0)
      $this->testing = $testing;
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
