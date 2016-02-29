<?php

namespace core\control\handler;
use \core\object\LoggableObject as LoggableObject;
use \core\control\Timer as Timer;
use \core\control\Semaphore as Semaphore;

/**
 * Handle a semaphore. Acquiring/releasing/removing.
 * Extending LoggableObject allows this object to use a FileLogger avoiding
 * errors upon serialization as any resource or file handles cannot be
 * serialized.
 * @author Marc Bredt
 * @see LoggableObject
 * @see FileLogger
 */
class SemaphoreHandler extends LoggableObject {

  /**
   * Default timeout for acquiring a semaphore.
   */
  const sem_acquire_default_timeout = 60;

  /**
   * Semaphore which will be acquired.
   */
  private $semaphore = null;

  /**
   * Timeout timer for acquiring a semaphore.
   */
  private $semaphore_ac_timer = null;

  /** 
   * Default number of retries for acquiring a semaphore.
   */
  private $semaphore_max_try = 3;

  /**
   * Initialize a semaphore that can be handled by the
   * functions provided by this class.
   * @throws
   * @return true if loading the semaphore was successful
   */ 
  public function load($sem = null) {
    if(strncmp(gettype($sem),"object",6)==0
       && strncmp(get_class($sem),"core\control\Semaphore",
                    strlen(get_class($sem)))==0) {
      $this->semaphore = $sem;
      $this->semaphore->create();
      return true;
    }
    //else // throw exception if not
    return false;
  }

  /**
   * Set the timeout timer used to acquire a semaphore.
   * @param $timeout timeout
   * @throws
   * @see Timer
   */
  private function set_timer($timeout = self::sem_acquire_default_timeout) {

    // setup a timer to avoid endless locking when trying 
    // to acquire a semaphore
    $this->semaphore_ac_timer = null;
    if(strncmp(gettype($timeout),"integer",7)==0) {
      $this->semaphore->set_sem_acquire_timeout($timeout);
    } else {
      $this->semaphore->set_sem_acquire_timeout(self::sem_acquire_default_timeout); }
    $this->semaphore_ac_timer = 
      new Timer($this->semaphore->get_sem_acquire_timeout());
  }

  /**
   * Try to acquire a semaphore.
   *
   * NOTE: sem_acquire will wait forever if a semaphore is not released
   *       for any reason. Therefor as of PHP version 5.6.1 the $nowait
   *       flag was introduced which could is used here. Further work may
   *       lead to an implementation which runs an interruptable version
   *       of sem_acquire without using the $nowait flag, e.g. a 
   *       TimeoutThread
   *
   * @param $timeout timeout for acquiring a semaphore
   * @throws
   * @see Timer
   */
  public function acquire($timeout = self::sem_acquire_default_timeout) {

    // set and start the timer
    if(strncmp(gettype($timeout),"integer",7)==0 
       && $timeout>0) $this->set_timer($timeout);
    else $this->set_timer($this->semaphore->get_sem_acquire_timeout());
    $this->semaphore_ac_timer->start();

    // now try to acquire a semaphore
    $acquired = false;
    $try = 1;
    while(!is_null($this->semaphore->get_sem_res()) && !$acquired
          && !is_null($this->semaphore_ac_timer)
          && $try <= $this->semaphore_max_try) {

      // use sem_acquire with $nowait flag to append a timer
      $acquired = sem_acquire($this->semaphore->get_sem_res(), true);
  
      // retart the timer
      if($this->semaphore_ac_timer->get()==0
         && $this->semaphore_ac_timer->get_timed_out()) { 
        echo "Try #".$try.". Timer timed out.\n";
        $try++;
        $this->semaphore_ac_timer->start();
      }
    }

    return $acquired;
  }

  /**
   * Release a semaphore when th work is done.
   * DO NOT forget to call this when running sem_acquire without
   * $nowait flag set to true.
   * @return true if the semaphore was release otherwise false
   */
  public function release() {
    $released = false;
    if(!is_null($this->semaphore->get_sem_res()))
      $released = sem_release($this->semaphore->get_sem_res());
    return $released;
  }

  /**
   * Remove the semaphore created.
   * @return true on successful removal otherwise false
   */
  public function remove() {
    return (!is_null($this->semaphore->get_sem_res())
            && strncmp(gettype($this->semaphore->get_sem_res()),"resource",8)==0 ?
              sem_remove($this->semaphore->get_sem_res()) : true);
  }

  /**
   * Print this object.
   * @return string representing this object.
   */
  public function __toString() {
    return __CLASS__." [sem=".$this->semaphore.
                     ", timer=".$this->semaphore_ac_timer. 
                     "]";
  }

  /**
   * Get the timer to check externally if it timed out.
   */
  public function get_timer() {
    return $this->semaphore_ac_timer;
  } 
}

?>
