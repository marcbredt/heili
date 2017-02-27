<?php

namespace core\util\file;
use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;

class File {

  private $file = null;

  private $fres = null;
 
  private $mode = null;

  private $perm = 0660;

  /**
   * Create or access a file. Set permissions if the file does not exist.
   * @param $file string representing the path to the file
   */
  public function __construct($file = "") {

    global $filelogger;

    if(!Validator::isa($filelogger,"null")) 
      $filelogger->log("file=%", array($file));

    if(Validator::isa($file,"string") && !Validator::isempty($file)) {

      if(file_exists($file)) {
        $this->file = $file;

      } else if(!file_exists($file)) {
        $this->file = $file;
        @mkdir(dirname($file),0770,true);
        touch($this->file);
        chmod($this->file,$this->perm);
      } 

    }

  }

  /**
   * Open the file with the mode specified.
   * @param $mode file mode considering regex "/(r|w|a|x|c)[+]/"
   * @return true if opening was successful, otherwise false
   */
  public function open($mode = "r") {

    global $filelogger;

    if(Validator::isa($mode,"string") 
       && Validator::matches($mode,"/^(r|w|a|x|c)\+{0,1}$/"))
      $this->fres = fopen($this->file, $mode);  

    if(!Validator::isa($filelogger,"null")) 
      $filelogger->log("mode=%, fres=%, isres=%, match=%", 
                       array($mode,$this->fres,is_resource($this->fres),
                             Validator::matches($mode,"/(r|w|a|x|c)[+]/")));

    return (is_resource($this->fres) ? true : false);

  }

  /**
   * Close the File's resource handle.
   */
  public function close() {
    fclose($this->fres);
  }

  /**
   * Read the file by returning the contents between the file's beginning and
   * its current end determined by fseek().
   * @return string returned by fread()
   */ 
  public function read() {
    $this->open();
    $fse = fseek($this->fres,0,SEEK_END);
    $oend = ftell($this->fres);
    $fsb = fseek($this->fres,0,SEEK_SET);
    return ($oend>0 ? fread($this->fres,$oend) : "");
  }

  /**
   * Write data to the file set using the resource handle initiated during 
   * construction.
   * @param $data string going to be written
   * @return false if $data is no string, otherwise it returns bytes written
   *         using fwrite()
   */
  public function write($data = "") {
    return (Validator::isa($data,"string") 
              ? fwrite($this->fres, $data, strlen($data))
              : false);
  }

  /**
   * Prints information on this File object when trying to echo this object.
   * @return string representing this File.
   */
  public function __toString() {
    return get_class($this).spl_object_hash($this)."=(".
           "file=".$this->file.", ".
           "fres=".StringUtil::get_object_string($this->fres).", ".
           "mode=".$this->mode.", ".
           "perm=".$this->perm.
           ")";
  }

  /**
   * Get the file path.
   * @return file path
   */
  public function get_file() {
    return $this->file;
  }

  /**
   * Clean the file set. A helper for testing.
   * @return number of truncated bytes
   */
  public function clean() {
    $fh = fopen($this->file,"w");
    $tb = ftruncate($fh,0); fclose($fh);
    return $tb;
  }

  /** 
   * Get the first line from file set. A helper for testing.
   * @return first line from logfile with CR and NL trimmed.
   * @see FileLoggerTest
   * @see XMLDocumentTest
   */
  public function getfirst() {
    $fh = fopen($this->file,"r"); 
    $fline = fgets($fh); fclose($fh);
    return preg_replace("/[\r\n]/","",$fline);
  }

  /** 
   * Get last line from file set. A helper for testing.
   * @return the last line from logfile set
   * @see FileLoggerTest
   * @see XMLDocumentTest
   */
  public function getlast() {

    $fh = fopen($this->file,"r");
        
    // get the newline offset from back, skips newlines at the end
    $fline = ""; $c = ""; $fpos = 0;
    do { $fpos--; $fs = fseek($fh, $fpos, SEEK_END); $c = fgetc($fh);
    } while(Validator::equals($c,PHP_EOL) && ftell($fh)>1); 

    // get the last line character/byte-wise
    $fline = ""; $c = ""; 
    do {
      $fline = $c.$fline;
      $fs = fseek($fh, $fpos--, SEEK_END);
      $c = fgetc($fh); 
    } while(!Validator::equals($c,PHP_EOL) && ftell($fh)>1);

    fclose($fh);
    return $fline;

  }

}

?>
