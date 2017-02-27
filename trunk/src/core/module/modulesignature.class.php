<?php

namespace core\module;
use core\object\Signature as Signature;
use core\object\Validatable as Validatable;
use core\util\file\File as File;
use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;
use core\system\Command as Command;
use core\system\CommandExecutor as CommandExecutor;

/**
 * This class is used to represent module signatures and make them validatable.
 * @author Marc Bredt
 */
class ModuleSignature extends Signature implements Validatable {
 
  private $module = "";

  private $type   = "";

  private $pkey   = "";

  private $text   = "";

  private $hash   = ""; 

  private $valid  = false;

  /**
   * Setup the module's signature file.
   * @param $module module name
   */ 
  public function __construct($module = "") {
    $this->module = $module;
    if(Validator::equals($this->module,"core")) {
      $this->file = PATH_CONF.DIRECTORY_SEPARATOR."signature.xml";
    } else {
      $this->file = constant("MOD_".strtoupper($this->module)."_PATH_CONF").
                      DIRECTORY_SEPARATOR."signature.xml";
    }
  }

  /**
   * Inherited from Validatable. Validates the signature file and the signature
   * provided through this file.
   * @return true if the license is valid, otherwise fals
   * @see Validatable
   */
  public function validate() {
    global $filelogger;
    $sd = PATH_DTD.DIRECTORY_SEPARATOR."module".DIRECTORY_SEPARATOR."signature.dtd";
    $fv = XMLDocument::is_valid($this->file,$sd);
    $sv = $this->verify();
    $this->valid = $fv && $sv;
    $filelogger->log("fv?=%, sv?=%, %",array($fv,$sv,$this),"DEBUG");
    return $this->valid;
  }

  /**
   * Verify a module signature.
   *
   * To be able to verify a signature via gpg there are a few conditions need to
   * be met
   * * default user in an apache environment is "www-data"
   * * default ~/.gnupg home directory for this user is located at "/var/www"
   * * gpg writes lock files to the gnupg home directory
   * * the gnupg home directory needs to be writeable for "www-data"
   * * the public keyring file needs to be readable for "www-data"
   * * the trust db file needs to be readable for "www-data"
   *
   * @return boolean indicating if the module's signature is valid
   *         true if the signature is valid, otherwise false
   */
  private function verify() {

    global $filelogger;

    // get the signature values
    $sd = PATH_DTD.DIRECTORY_SEPARATOR."module".DIRECTORY_SEPARATOR."signature.dtd";
    $xs = new XMLDocument($this->file,$sd,false); // already validated
    $this->type = $xs->xpath("string(/signature/@type)");
    $this->pkey = $xs->xpath("string(/signature/@key)");
    $node = $xs->xpath("string(/signature/text())",false,true);
    $this->text = preg_replace("/(\r|\t| )*\n/","\n",
                    preg_replace("/\n(\r|\t| )*/","\n",$node));
    $filelogger->log("sig=%",array($this),"DEBUG");

    // create a temporary signature file
    $sif = new File(GPG_TMPDIR.DIRECTORY_SEPARATOR."module-".$this->module.".sig");
    $sif->open("w");
    $sif->write($this->text);

    // evaluate the module's signature
    if(Validator::equals($this->module,"core")) {
      $hcommand = new Command("find ".PATH_ROOT." -mindepth 1 -maxdepth 1 ".
                               "-type d -not -wholename ".PATH_MODULES." ".
                               "-exec find {} -type f -not -wholename '".PATH_CONF.
                                 DIRECTORY_SEPARATOR."signature.xml' \; | ".
                             "awk '{ system(\"md5sum \"\$1); }' | ".
                             "cut -d' ' -f1 | md5sum | cut -d' ' -f1");
    } else {
      $hcommand = new Command("find ".constant("MOD_".strtoupper($this->module)."_PATH").
                              " -type f -not -wholename ".
                                "'".constant("MOD_".strtoupper($this->module).
                                             "_PATH_CONF").DIRECTORY_SEPARATOR.
                                    "signature.xml' ".
                                "-exec md5sum {} + | ".
                              "cut -d' ' -f1 | md5sum | cut -d' ' -f1");
    }

    $ce = new CommandExecutor();

    // get the hash
    if($ce->execute($hcommand,false)) $this->hash = $ce->get_line();
    $filelogger->log("hcommand=%, hash=%",
                     array($hcommand,$this->hash),"DEBUG");

    // verifying command
    $vcommand = new Command("echo ".$this->hash." | ".
                            "gpg --homedir ".GPG_HOMEDIR." --keyring ".GPG_KEYRING.
                               " --no-tty --verify ".$sif->get_file()." -");

    // get the signature for
    if($ce->execute($vcommand,false)) $this->valid = true;
    $filelogger->log("vcommand=%, valid='%'",
                     array($vcommand,$this->valid),"DEBUG");
    

    return $this->valid;

  }

  /**
   * Get the module's files hash used to verify the signature.
   * @return MD5 hash summarizing the module's files
   */
  public function get_hash() {
    return $this->hash;
  }

  /** 
   * Dump some information.
   * @return string describing this object
   */
  public function __toString() {
    return get_class($this).spl_object_hash($this)."=( ".
           "module=".$this->module.", ".
           "hash=".$this->hash.", ".
           "file=".$this->file.", ".
           "type=".$this->type.", ".
           "pkey=".$this->pkey.", ".
           "text=".$this->text.", ".
           "valid=".($this->valid ? "true" : "false")." ".
           ") ";
  }

}

?>
