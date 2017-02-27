<?php

namespace core\layout;
use core\util\param\Validator as Validator;
use core\session\token\Tokenizer as Tokenizer;

/**
 * This class is used to do some template evaluation stuff like setting values
 * while evaluationg class functions. This is useful for e.g. setting tokens in
 * form configurations using Tokenizer::get().
 * @author Marc Bredt
 */
class Evaluator {


  /**
   * Get the evaluated value from the value instruction configured.
   * @param $s string used for evaluation
   * @return string from the instrunctions allowed
   */
  public static function get($s = "") {

    if(Validator::matches($s,"/Tokenizer::get()/"))
      return Tokenizer::get();
    else
      return $s;

  }

}

?>
