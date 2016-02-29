<?php

namespace core\control;

/**
 * Simple timer class. If the timer is started on can call Timer::get()
 * to check if the timeout already passed.
 * @author Marc Bredt
 */
class Timer {

  /**
   * Default timer timeout
   */
  const timer_default_timeout = 60;

  /**
   * Timeout in seconds set during creation.
   */
  private $timer_timeout = null;
 
  /**
   * Start timestamp in seconds set upon start.
   */
  private $timer_start_ts = null;

  /**
   * End timestamp in seconds set upon start.
   */
  private $timer_end_ts = null;

  /**
   * Default standalone flag.
   */
  const timer_default_standalone = false;
 
  /**
   * Contains the standalone flag to make this timer block
   * after start.
   */
  private $standalone = false;

  /**
   * Indicates if the timer timed out.
   */
  private $timed_out = false;

  /**
   * Create a time and set the timeout.
   * @param $timeout timeout 
   */
  public function __construct($timeout = self::timer_default_timeout, 
                              $standalone = self::timer_default_standalone) {

    // set timeout
    if(strncmp(gettype($timeout),"integer",7)==0)
      $this->timer_timeout = $timeout;
    else
      $this->timer_timeout = self::timer_default_timeout;

    // set standalone
    if(strncmp(gettype($standalone),"boolean",7)==0)
      $this->timer_standalone = $standalone;
    else
      $this->timer_standalone = self::timer_default_standalone;
  
  }

  /**
   * Start the timer. Sets start and end timestamps.
   * If it is a standalone timer it will just wait for 
   * $this->timer_timeout seconds otherwise it just initializes
   * the start and end timestamps.
   */
  public function start() {
    $this->timed_out = false;
    $this->timer_start_ts = time();
    $this->timer_end_ts = time() + $this->timer_timeout;
    if($this->timer_standalone) {
      while($this->get()>0) { sleep(1); }
    }
  }

  /**
   * Check if the timeout already passed
   * @return seconds until the timeout passes.
   */
  public function get() {
    if(time()>=$this->timer_end_ts) { $this->timed_out = true; }
    return ($this->timed_out ? 0 : $this->timer_end_ts - time());
  }

  /**
   * Check if the timer timed out.
   * Functions get() and get_timed_out() should be distinct to
   * avoid modifying the timer state after acquiring a semaphore.
   * @return $this->timed_out
   * @see SharedMemoryHandler
   */
  public function get_timed_out() {
    return $this->timed_out;
  } 
  
  /**
   * Get a string representation for this timer.
   * @return timer as string
   */
  public function __toString() {
    return __CLASS__." (to=".$this->timer_timeout.
                     ", start=".$this->timer_start_ts.
                     ", end=".$this->timer_end_ts.
                     ", standalone=".var_export($this->standalone,true).
                     ", time_left=".$this->get().
                     ", timedout=".var_export($this->get_timed_out(),true).
                     ")";
  }

}

?>
