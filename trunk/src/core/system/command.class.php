<?php

namespace core\system;
use core\util\param\Validator as Validator;

/**
 * This class implements a system command representation.
 * @author Marc Bredt
 */
class Command {

  private $command = "";
 
  private $escaped = true;

  public function __construct($command = "") {
    $this->command = $command;
  }

  // TODO: escapeshellarg in piped commands
  public function get($escaped = true) {

    if(Validator::isa($escaped,"boolean"))
      $this->escaped = $escaped;

    return ($this->escaped ? escapeshellcmd($this->command) : $this->command);

  }

}

?>
