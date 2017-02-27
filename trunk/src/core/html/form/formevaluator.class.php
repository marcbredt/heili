<?php

namespace core\html\form;
use core\util\evaluate\Evaluator as Evaluator;

/**
 * This class evaluates form data submitted corresponding to
 * a mask, its source (tree) definition. If any invalid form
 * parameter is detected an exception is thrown after any 
 * invalid parameter is dropped.
 * @authorMarc Bredt
 */
class FormEvaluator implements Evaluator {

  /**
   * Evaluate form data passed corresponding to the mask
   * and its source and target values defined.
   * @return void
   */
  public static function evaluate() {

    // get mask from $_GET/POST which should have already been verified

    // get stree from $_GET/POST which should have already been verified
 
    // get source and target type from mask configuration

    // unset the token on errors to disable further action execution

  }

}

?>
