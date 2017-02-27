<?php

namespace core\control;
use core\util\param\Validator as Validator;

/**
 * This class can be used to create/access/manage a System V semaphore.
 * It extends PHP's functionality provided by the sysvsem extension.
 * Note that this extension is only available for *nix operating systems.
 *
 * You can also use the PECL extension Sync which implements native
 * locking mechanisms which will blow up any bundle and makes this
 * software depending on additional third party software. 
 *
 * NOTE: Further work could include extending this class to support
 *       other operating systems.
 *
 * @author Marc Bredt
 * @see <a href="http://php.net/manual/en/intro.sem.php">Semaphore Introduction</a>
 * @see <a href="http://php.net/manual/en/book.sync.php">PECL Sync</a>
 */
class Semaphore {

  /**
   * Default key.
   */
  private $sem_sysv_ipc_key = null;
 
  /**
   * Resource pointing to the semaphore.
   */ 
  private $sem_res = null;
  
  /**
   * Maximum number of processes thatcan acquire the semaphore
   * simultaniously.
   */
  private $sem_max_acquire = 1;

  /**
   * Default semaphore rights. Octal integer.
   */
  private $sem_perms = 0600;

  /**
   * Release the semaphore on shutdown requests.
   * Should be 0 to avoid possible reacquiring issues.
   * @see http://php.net/manual/en/function.sem-get.php
   * @see https://bugs.php.net/bug.php?id=70753
   */
  private $sem_auto_release = 0;

  /**
   * Acquisition timeout set. Defaults to 60 seconds.
   */
  private $sem_acquire_timeout = 60;

  /** 
   * Constant of valid access types. Currently
   * 0: read only (0400)
   * 1: write only (0200)
   * 2: read and write (0600)
   */
  private $sem_valid_access_types = array(0,1,2);

  /** 
   * Access type. Changes the octal rights passed. Defaults to 2.
   * 0: read only (0400), flag "a" for shmop_open
   * 1: write only (0200), flag "w" for shmop_open + 0200 upon creation
   * 2: read and write (0600), flag "w" for shmop_open
   */
  private $sem_access_type = 2;

  /**
   * Initiate the semaphore.
   */
  public function create() {
    $this->set_sem_key();
    if(Validator::isa($this->sem_acquire_timeout,"null")) 
      $this->set_sem_acquire_timeout();
    $this->set_sem_res();
  }

  /**
   * Set a System V IPC key to obtain a resource via sem_get().
   * @see sem_get()
   * @see SemaphoreHandler
   * @throws SemaphoreException
   */
  public function set_sem_key($key = null) {

    global $filelogger;
    
    // avoid resetting a semaphores key already set on loading a samaphore
    if(Validator::isa($this->sem_sysv_ipc_key,"integer")) {
      $filelogger->log("semaphore key alread set, key=%", 
                 array($this->sem_sysv_ipc_key));

    // if the semaphore key was not set yet set it
    } else if(Validator::isa($key,"integer")) {
      $this->sem_sysv_ipc_key = $key;
      $filelogger->log("setting semaphore key=%", 
                 array($this->sem_sysv_ipc_key));

    // otherwise mark the key as "corrupt"
    } else { $this->sem_sysv_ipc_key = null; }

    // if setting failed throw an exception
    if(Validator::isa($this->sem_sysv_ipc_key,"null")) {
      $filelogger->log("%, key=%", 
                 array(new SemaphoreException("invalid key",3), $key));
      throw(new SemaphoreException("invalid key",3));
    }

  }
   
  /**
   * Get the System V IPC key generated.
   * @return sysvsem key generated.
   */
  public function get_sem_key() {
    return $this->sem_sysv_ipc_key;
  }

  /**
   * Set the access type for this semaphore.
   * @param $at access type
   * @see Semaphore::sem_access_type
   */
  public function set_sem_access_type($at = 0) {
    if(Validator::in($at,$this->sem_valid_access_types))
      $this->sem_access_type = $at;
    else
      $this->sem_access_type = 0;
  }

  /**
   * Get the access type for this semaphore.
   * @return access type
   * @see Semaphore::sem_access_type
   */
  public function get_sem_access_type() {
    return $this->sem_access_type; 
  }

  /**
   * Set a resource bound to the semaphore created.
   */
  private function set_sem_res() {
    // only get a resource identifier for a semaphore, if it is not set yet
    // otherwise a different identifier would be generated pointing to the 
    // same semaphore BUT it seems it resets the current semaphore acquisition
    if(Validator::isa($this->sem_res,"null"))
      $this->sem_res = sem_get($this->sem_sysv_ipc_key,
                               $this->sem_max_acquire,
                               $this->sem_perms,
                               $this->sem_auto_release);
  }

  /**
   * Get the semaphore resource.
   */
  public function get_sem_res() {
    return $this->sem_res;
  }

  /**
   * Set the timeout for trying to acquire a semaphore.
   * @param $timeout semaphore acquiring timeout 
   */
  public function set_sem_acquire_timeout($timeout = null) {
    if(Validator::isa($timeout,"integer") && $timeout >= 0) 
      $this->sem_acquire_timeout = $timeout;
    else
      $this->sem_acquire_timeout = $this->sem_acquire_timeout;
  }

  /**
   * Get the semaphore acquiring timeout.
   * @return acquiring timeout
   */
  public function get_sem_acquire_timeout() {
    return $this->sem_acquire_timeout;
  }

  /**
   * Set the number of processes that can acquire this
   * semaphore simultaniously. Can be used for e.g. reading
   * configurations stored in a shared memory segment.
   * @param $max_acquire maximum of processes allowed to access
   *                     this semaphore simultaniously 
   */
  public function set_sem_max_acquire($max_acquire = 1) {
    if(Validator::isa($max_acquire,"integer") && $max_acquire>0)
      $this->sem_max_acquire = $max_acquire;
  }

  /**
   * Get the maximum number allowed to acquire this semaphore
   * simultaniously.
   * @return the maximum number of processes allowed
   */
  public function get_sem_max_acquire() {
    return $this->sem_max_acquire;
  }

  /**
   * Set the permissions for the semaphore.
   * @param $perms octal integer representing (file) permissions
   */
  public function set_sem_perms($perms = 0600) {
    if(Validator::isa($perms,"integer")==0 && $perms>0)
      $this->sem_perms = $perms;
    else
      $this->sem_perms = 0600;
  }

  /**
   * Get the semaphore permissions set.
   * @return permissions as octal integer
   */
  public function get_sem_perms() {
    return substr(sprintf('%o', $this->sem_perms),-5);
  }

  /**
   * Print information for this semaphore.
   * @return string showing this object.
   */
  public function __toString() {
    return __CLASS__." ("."key=".$this->get_sem_key().
                     ", perms=".$this->get_sem_perms().
                     ", maxac=".$this->get_sem_max_acquire().
                     ", acto=".$this->get_sem_acquire_timeout().
                     ", res=".$this->get_sem_res().
                     ")";
  }

}

?>
