<?php

namespace core\util\io;

/**
 * Reader interface to declare some functions any reader should implement.
 * @author Marc Bredt
 */
interface Reader {

  /**
   * Function that must be implemented by the reader to read the
   * $element.
   */
  public function read();

}
