<?php

namespace core\control\handler;
use core\util\string\StringUtil as StringUtil;
use core\util\param\Validator as Validator;
use core\control\Timer as Timer;
use core\control\Semaphore as Semaphore;
use core\exception\shm\SemaphoreException as SemaphoreException;
use core\exception\param\ParamNotValidException as ParamNotValidException;

/**
 * Handle a semaphore. Acquiring/releasing/removing.
 * @author Marc Bredt
 * @see FileLogger
 */
class SemaphoreHandler {

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
   * @param $sem semaphore to load
   * @throws ParamNotValidException
   * @return true if loading the semaphore was successful
   */ 
  public function load($sem = null) {

    global $filelogger;

    if(Validator::isa($sem,"object") 
       && Validator::isclass($sem,"core\control\Semaphore")) {

      $this->semaphore = $sem;
      $this->semaphore->create();
      return true;

    } else {
      $filelogger->log("%",
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

    global $filelogger;

    // set and start the timer
    $timer = null;
    if(Validator::isa($timeout,"integer") && $timeout>0)
      $timer = new Timer($timeout);
    if($timer===null) {
      $filelogger->log("%.", 
                       array(new TimerException("creation failed")));
      throw(new TimerException("creation failed"));
    }
    $timer->start();

    // now try to acquire a semaphore
    $acquired = false;
    $try = 1;
    while(!Validator::isa($this->semaphore->get_sem_res(),"null") 
          && $acquired===false && $timer!==null 
          && $try <= $this->semaphore_max_try) {

      // use sem_acquire with $nowait flag to append a timer
      // NOTE: warnings should be suppressed here any error should throw an 
      //       exception
      $acquired = @sem_acquire($this->semaphore->get_sem_res(), true);
  
      // retart the timer
      if($timer->get()==0 && $timer->get_timed_out()) { 
        $filelogger->log("Acquiring. Try #%. Timer timed out.",
                   array($try));
        $try++;
        $timer->start();
      }
    }

    if($acquired===false) {
      $filelogger->log("%.",
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
   
    global $filelogger;

    // set and start the timer
    $timer = null;
    if(Validator::isa($timeout,"integer") && $timeout>0) 
      $timer = new Timer($timeout);
    if($timer===null) {
      $filelogger->log("%.", 
                       array(new TimerException("creation failed")));
      throw(new TimerException("creation failed"));
    }
    $timer->start();

    // now try to acquire a semaphore
    $released = false;
    $try = 1;
    while(!Validator::isa($this->semaphore->get_sem_res(),"null") 
          && $released===false && $timer!==null 
          && $try <= $this->semaphore_max_try) {

      // use sem_acquire with $nowait flag to append a timer
      $released = @sem_release($this->semaphore->get_sem_res());
  
      // retart the timer
      if($timer->get()==0 && $timer->get_timed_out()) { 
        $filelogger->log("Releasing. Try #%. Timer timed out.",
                   array($try));
        $try++;
        $timer->start();
      }
    }
    
    // if releasing failed 
    if($released===false) {
      $filelogger->log("%.",
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
    return (!Validator::isa($this->semaphore->get_sem_res(),"null")
            && Validator::isa($this->semaphore->get_sem_res(),"resource") ?
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
