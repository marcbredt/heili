<?php

namespace core;

/**
 * This class implements the autoloading functionality which can be
 * registered through spl_autoload_register. As it is not possible 
 * to pass multiple parameters, especially for objects already 
 * instanziated, onto spl_autoload_register the original functionality 
 * of AutoLoader is divided in a way a callback can be made to those 
 * functions by passing additional parameters running call_user_func.
 * <br />
 * <pre>
 *   spl_autoload_register( function($className) { 
 *     call_user_func(array(new AutoLoadingDevisor(), 'alf'), 
 *       $className, $addParam); } );
 * </pre>
 * <br />
 * This structure leads to modifiable autoloading for e.g. testing
 * purposes publishing the state inside the autoloading function
 * itself. Additionally this division is useful when furthermore
 * operations need to be done before registering the autoloading
 * function. If one wants to run or create a simple autoloader it
 * may simply extends this class to get a autoloading functionality.
 * 
 * @author Marc Bredt
 */
class AutoLoadingDevisor {

  /**
   * Extensions to check for class definitions.
   */
  private $extensions = null;
  
  /**
   * Root directory of the project. Prepended when requiring/
   * checking for class files.
   */
  private $rdir = null;

  /**
   * Initialize the root directory and the extensions to search for.
   * For reasons mentioned above this is a standalone class and it
   * should not be heritated from when you try to register this
   * autoloading function by passing a modified callable to
   * spl_autoload_register.
   * @see AutoLoader::register_autoload()
   */
  public function __construct($rdir = ".", $extensions = ".class.php") {
    if(is_dir($rdir)) $this->rdir = $rdir;
    $this->extensions = $extensions;
  }

  /**
   * Default autoloading function. Use this callable when running
   * spl_autoload_register to avoid lowering classfile path bug.
   * The second argument should just be set to true when running
   * tests on this function to cover all pathes, using e.g. phpunit.
   * @param $class classname passed
   * @see https://bugs.php.net/bug.php?id=51991.
   */
  public function autoloadingfunction($class = "", $testing = false) {

    //echo "class=".$class.", testing=".var_export($testing,true)."\n";

    // try to include files with any of those extensions set
    $notfound = true;
 
    foreach(explode(",",$this->extensions) as $ext) {

      // check if there was a classname provided
      if ( strncmp($class, "", 1)!=0 ) {
   
        // check for classname for the case provided first
        if($this->isininc($class, false, false, $ext, $testing)) {
          require_once($class.$ext);
          $notfound = false;

        // check for lower case class file names second
        } else if ($this->isininc($class, true, false, $ext, $testing)) {
          require_once(strtolower($class).$ext);
          $notfound = false;

        // check for namespace stripped case provided file
        } else if ($this->isininc($class, false, true, $ext, $testing)) {
          if (strncmp($this->rdir,"",1)==0)
            require_once($this->rdir.DIRECTORY_SEPARATOR.
                           str_replace("\\",DIRECTORY_SEPARATOR,$class).
                             $ext);
          $notfound = false;

        // check for namespace stripped lowercase class name
        } else if ($this->isininc($class, true, true, $ext, $testing)) {
          require_once($this->rdir.DIRECTORY_SEPARATOR.
                         str_replace("\\",DIRECTORY_SEPARATOR,strtolower($class)).
                           $ext);
          $notfound = false;

        // check for path representing class file name
        // e.g. PHPUnit_Framework_TestCase exists in include path in
        //      PHPUnit/Framework/TestCase.php
        } else if ($this->isininc($class, NULL, NULL, $ext, $testing) 
                   && strpos($class,"_")) {
          require_once(str_replace("_",DIRECTORY_SEPARATOR,$class).$ext);
          $notfound = false;

        // include lowercase
        //} else if ($this->isininc($class, true, NULL, $ext, $testing) 
        //           && !strpos("_",$class)) {
        //  require_once($class.$ext);
        //  $notfound = false;

        // or capitalized class file names directly
        //} else if ($this->isininc($class, false, NULL, $ext, $testing)
        //           && !strpos("_",$class)) {
        //  require_once(strtolower($class).$ext);

        }

      }

    }

    if ($notfound) {
      // NOTE: using the FileLogger by requiring it in the preamble
      //       the Log class (from php-log) it extends will not be
      //       found because the autoload extensions/functions are 
      //       not registered at this point causing phpunit to fail
      //       on any invocation so the error message for classes
      //       which could not be found will be printed on stdout.
      // TODO: check how running the autoloader works when accessed
      //       on a server node through a client
      echo "The holy cow needs water for '".$class."'\n";
    }

  }

   /**
    * Checks if a file with extension <code>$this->extension</code> exists
    * in the include path. It checks for literal 
    * @param $class class name, probably contains namespace notation
    * @param $lowercase set to true when checking for lowercased class file names
    * @param $namespace set to true when checking for class file names regarding
    *                   their namespace provided, namespace delimiter will be
    *                   replaced with directory delimiter, that makes class file  
    *                   namespaces need to follow the directory structure to be
    *                   found/includable
    * @uses stream_resolve_include_path available since PHP >= 5.3.2 if local version fits
    *         otherwise this fuction simply iterates over <code>get_include_path</code> 
    */
  private function isininc($class = "", 
                           $lowercase = NULL, $namespace = NULL, 
                           $ext = "", $testing = false) {

    if(strncmp($ext,"",1)==0)
      $ext = substr($this->extensions,0,strpos($this->extensions,","));

    // check for PHP version and stream_resolve_include_path presence first
    if (function_exists("stream_resolve_include_path") && !$testing) {

      // check for classname for the case provided first
      if( !is_null($lowercase) && !is_null($namespace) &&
          !$lowercase && !$namespace) {
        //echo "0: class=".$class.", lc=".$lowercase.", nsp=".$namespace.", cunder=".strpos($class,"_").", ext=".$ext.", testing=".var_export($testing,true)."\n";
        return (strncmp(stream_resolve_include_path(
                 $class.$ext),"","1")!=0);

      // then check for lower case class file names second
      } else if( !is_null($lowercase) && !is_null($namespace) &&
               $lowercase && !$namespace) {
        //echo "1: class=".$class.", lc=".$lowercase.", nsp=".$namespace.", cunder=".strpos($class,"_").", ext=".$ext.", testing=".var_export($testing,true)."\n";
        return (strncmp(stream_resolve_include_path(
                 strtolower($class).$ext),"","1")!=0 );

      // additionally check for namespaces provided for class file names
      } else if( !is_null($lowercase) && !is_null($namespace) &&
               !$lowercase && $namespace) {
        //echo "2: class=".$class.", lc=".$lowercase.", nsp=".$namespace.", cunder=".strpos($class,"_").", ext=".$ext.", testing=".var_export($testing,true)."\n";
        return (strncmp(stream_resolve_include_path(
                 $this->rdir.DIRECTORY_SEPARATOR.
                   str_replace("\\",DIRECTORY_SEPARATOR,$class).$ext),"","1")!=0);

      // additionally check for namespaced lower case
      } else if( !is_null($lowercase) && !is_null($namespace) &&
               $lowercase && $namespace) {
        //echo "3: class=".$class.", lc=".$lowercase.", nsp=".$namespace.", cunder=".strpos($class,"_").", ext=".$ext.", testing=".var_export($testing,true)."\n";
        return (strncmp(stream_resolve_include_path(
                 $this->rdir.DIRECTORY_SEPARATOR.
                   str_replace("\\",DIRECTORY_SEPARATOR,
                     strtolower($class)).$ext),"","1")!=0);

      // check for class names that names represent pathes in an include path directory
      // e.g. for PHPUnit_Framework_TestCase -> PHPUnit/Framework/TestCase.php
      //      exists in /usr/share/php
      } else if (is_null($lowercase) && is_null($namespace) && strpos($class,"_")) {
        //echo "4: class=".$class.", lc=".$lowercase.", nsp=".$namespace.", cunder=".strpos($class,"_").", ext=".$ext.", testing=".var_export($testing,true)."\n";
        return (strncmp(stream_resolve_include_path(
                  str_replace("_",DIRECTORY_SEPARATOR,$class).$ext),"","1")!=0);

      }

    // if php version does not fit or stream_ function not avalable
    } else {

      // iterate over include pathes set
      foreach(explode(PATH_SEPARATOR, get_include_path()) as $p) {

        $classfile = "";

        // check for classname for the case provided first
        if( !is_null($lowercase) && !is_null($namespace) &&
            !$lowercase && !$namespace ) {
          //echo "0: class=".$class.", lc=".$lowercase.", nsp=".$namespace.", cunder=".strpos($class,"_").", ext=".$ext.", testing=".var_export($testing,true)."\n";
          $classfile = $p.DIRECTORY_SEPARATOR.$class.$ext;

        // then check for lower case class file names second
        } else if( !is_null($lowercase) && !is_null($namespace) &&
                 $lowercase && !$namespace ) {
          //echo "1: class=".$class.", lc=".$lowercase.", nsp=".$namespace.", cunder=".strpos($class,"_").", ext=".$ext.", testing=".var_export($testing,true)."\n";
          $classfile = $p.DIRECTORY_SEPARATOR.strtolower($class).$ext;

        // additionally check for namespaces provided for class file names
        } else if( !is_null($lowercase) && !is_null($namespace) &&
                 !$lowercase && $namespace) {
          //echo "2: class=".$class.", lc=".$lowercase.", nsp=".$namespace.", cunder=".strpos($class,"_").", ext=".$ext.", testing=".var_export($testing,true)."\n";
          $classfile = $p.DIRECTORY_SEPARATOR.
                         str_replace("\\",DIRECTORY_SEPARATOR,$class).$ext;

        // additionally check for namespaced lower case
        } else if( !is_null($lowercase) && !is_null($namespace) &&
                 $lowercase && $namespace) {
          //echo "3: class=".$class.", lc=".$lowercase.", nsp=".$namespace.", cunder=".strpos($class,"_").", ext=".$ext.", testing=".var_export($testing,true)."\n";
          $classfile = $p.DIRECTORY_SEPARATOR.
                         str_replace("\\",DIRECTORY_SEPARATOR,strtolower($class)).$ext;

        // check for class names that names represent pathes in an include path directory
        // e.g. for PHPUnit_Framework_TestCase -> PHPUnit/Framework/TestCase.php
        //      exists in /usr/share/php
        } else if (is_null($lowercase) && is_null($namespace) && strpos($class,"_")) {
          //echo "4: class=".$class.", lc=".$lowercase.", nsp=".$namespace.", cunder=".strpos($class,"_").", ext=".$ext.", testing=".var_export($testing,true)."\n";
          $classfile = $p.DIRECTORY_SEPARATOR.
                         str_replace("_",DIRECTORY_SEPARATOR,$class).$ext;

        }

        // if php version does not fit or stream_ function not avalable
        if(strncmp($classfile,"",1)!=0 && file_exists($classfile)) {
          //echo "cf=".$classfile.", fe=".file_exists($classfile)."\n";
          return true;
        }
      }

    }

    return false;

  }

}

?>
