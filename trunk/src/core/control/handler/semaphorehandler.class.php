<?php

namespace core\control\handler;
use core\util\string\StringUtil as StringUtil;
use core\object\LoggableObject as LoggableObject;
use core\control\Timer as Timer;
use core\control\Semaphore as Semaphore;
use core\exception\ParamNotValidException as ParamNotValidException;
use core\exception\shm\SemaphoreException as SemaphoreException;

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
   * Semaphore which will be acquired.
   */
  private $semaphore = null;

  /** 
   * Default number of retries for acquiring a semaphore.
   */
  private $semaphore_max_try = 3;

  /**
   * Initialize a semaphore that can be handled by the
   * functions provided by this class.
   * @throws ParamNotValidException
   * @return true if loading the semaphore was successful
   */ 
  public function load($sem = null) {
    if(strncmp(gettype($sem),"object",6)==0
       && strncmp(get_class($sem),"core\control\Semaphore",
                    strlen(get_class($sem)))==0) {
      $this->semaphore = $sem;
      $this->semaphore->create();
      return true;

    } else {
      $this->log(__METHOD__.": %",
                 array(new ParamNotValidException(
                         "sem(core\control\Semaphore)=".
                           StringUtil::get_object_value($sem))));
      throw(new ParamNotValidException(
              "sem(core\control\Semaphore)=".
                StringUtil::get_object_value($sem)));

    }
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
   * @throws TimerException
   * @throws SemaphoreException
   * @see Timer
   */
  public function acquire($timeout = 30, $tries = 3) {

    // set and start the timer
    $timer = null;
    if(strncmp(gettype($timeout),"integer",7)==0 && $timeout>0)
      $timer = new Timer($timeout);
    if($timer===null) {
      $this->log(__METHOD__.": %.", array(new TimerException("creation failed")));
      throw(new TimerException("creation failed"));
    }
    $timer->start();

    // now try to acquire a semaphore
    $acquired = false;
    $try = 1;
    while(!is_null($this->semaphore->get_sem_res()) && $acquired===false
          && $timer!==null && $try <= $this->semaphore_max_try) {

      // use sem_acquire with $nowait flag to append a timer
      // NOTE: warnings should be suppressed here any error should throw an 
      //       exception
      $acquired = @sem_acquire($this->semaphore->get_sem_res(), true);
  
      // retart the timer
      if($timer->get()==0 && $timer->get_timed_out()) { 
        $this->log(__METHOD__.": Acquiring. Try #%. Timer timed out.",
                   array($try));
        $try++;
        $timer->start();
      }
    }

    if($acquired===false) {
      $this->log(__METHOD__.": %.",
                 array(new SemaphoreException("acquisition failed",0)));
      throw(new SemaphoreException("acquisition failed",0));
    }

    return $acquired;
  }

  /**
   * Release a semaphore when th work is done.
   * DO NOT forget to call this when running sem_acquire without
   * $nowait flag set to true.
   * @throws TimerException
   * @throws SemaphoreException
   * @return true if the semaphore was release otherwise false
   */
  public function release($timeout = 30, $tries = 3) {

    // set and start the timer
    $timer = null;
    if(strncmp(gettype($timeout),"integer",7)==0 && $timeout>0) 
      $timer = new Timer($timeout);
    if($timer===null) {
      $this->log(__METHOD__.": %.", array(new TimerException("creation failed")));
      throw(new TimerException("creation failed"));
    }
    $timer->start();

    // now try to acquire a semaphore
    $released = false;
    $try = 1;
    while(!is_null($this->semaphore->get_sem_res()) && $released===false
          && $timer!==null && $try <= $this->semaphore_max_try) {

      // use sem_acquire with $nowait flag to append a timer
      $released = @sem_release($this->semaphore->get_sem_res());
  
      // retart the timer
      if($timer->get()==0 && $timer->get_timed_out()) { 
        $this->log(__METHOD__.": Releasing. Try #%. Timer timed out.",
                   array($try));
        $try++;
        $timer->start();
      }
    }
    
    // if releasing failed 
    if($released===false) {
      $this->log(__METHOD__.": %.",
                 array(new SemaphoreException("release failed",1)));
      throw(new SemaphoreException("release failed",1));
    }

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
