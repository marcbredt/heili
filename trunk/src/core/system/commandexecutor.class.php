<?php

namespace core\system;
use core\object\Executable as Executable;
use core\util\param\Validator as Validator;

class CommandExecutor implements Executable {

  private $command = null;
 
  private $escaped = true;

  private $line = "";

  private $output = null;

  private $return = 0;

  /**
   * Execute a command. Escape every shell command by default.
   * @param $command command string to execute
   * @param $escaped 
   * @return true if $command was executed without any errors 
   */
  public function execute($command = null, $escaped = true) {

    global $filelogger;

    if(Validator::isclass($command,"core\system\Command") 
       && Validator::isa($escaped,"boolean")) {

      $this->command = $command;
      $this->escaped = $escaped;

      $l = exec($command->get($escaped), $o, $r);

      $this->line = $l;
      $this->output = $o;
      $this->return = $r;

      $filelogger->log("line=%, output=%, return=%",
                       array($this->line, $this->output, $this->return),"DEBUG");

      return (Validator::equals($r,0) ? true : false);

    } else {

      $filelogger->log("command=%, escaped=%",
                       array($command,$escaped),"WARNING");

    }
   
    return false;

  }

  public function batch() {}

  public function get_command() { return $this->command; }

  public function get_escaped() { return $this->escaped; }

  public function get_line() { return $this->line; }

  public function get_output() { return $this->output; }

  public function get_return() { return $this->return; }
 
}


?>
