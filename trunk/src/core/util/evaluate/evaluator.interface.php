<?php

namespace core\util\evaluate;

/**
 * This interface describes any necessary functions for
 * any evalautor going to be build.
 * @author Marc Bredt
 */
interface Evaluator {

  /**
   * Default evaluation function.
   * It could be used to unset any variable passed but not registered through
   * any configuration e.g. request.xml.
   * @return void
   */
  public static function evaluate();

}

?>
