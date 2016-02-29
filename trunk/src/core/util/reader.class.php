<?php

namespace core\util;

/**
 * Abstract class to declare some values and functions any
 * reader should implement.
 * @author Marc Bredt
 */
abstract class Reader {

  /**
   * Element which shoud be read, Could be anything like a file, stream,
   * shared memory segment, etc.
   */
  private $element = null;

  /**
   * Function that must be implemented by the reader to read the
   * $element.
   */
  public abstract function read();

}
