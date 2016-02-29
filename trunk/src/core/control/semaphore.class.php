<?php

namespace core\control;

/**
 * This class can be used to create/access/manage a System V semaphore.
 * It extends PHP's functionality provided by the sysvsem extension.
 * Note that this extension is only available for *nix operating systems.
 * You can also use the PECL extension Sync which implements native
 * locking mechanisms. 
 * It will create the same System V IPC key for each php process so each
 * try of acquiring a semaphore is bound to the same semaphore if
 * sem_proj_id is not changed before acquiring a semaphore.
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
   * File to create a System V IPC key for.
   * NOTE: If __FILE__ is used different keys are created
   *       from different contexts (e.g. two active shells).
   *       If "." is used as it is the local directory it 
   *       will create the same one .
   */
  private $sem_file = ".";

  /**
   * Default project identifier passed to ftok().
   */ 
  private $sem_proj_id = "0";

  /**
   * Default key value on failures of ftok().
   */
  private $sem_sysv_ipc_key = -1;
 
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
   * Default timeout for acquiring a semaphore.
   */
  const sem_acquire_default_timeout = 60;

  /**
   * Timeout set.
   */
  private $sem_acquire_timeout = null;

  /** 
   * Constant of valid access types. Currently
   * 0: read only (0400)
   * 1: write only (0200)
   * 2: read and write (0600)
   */
  const sem_valid_access_types = array(0,1,2);

  /** 
   * Access type. Changes the octal rights passed.
   * 0: read only (0400), flag "a" for shmop_open
   * 1: write only (0200), flag "w" for shmop_open + 0200 upon creation
   * 2: read and write (0600), flag "w" for shmop_open
   */
  private $sem_access_type = null;

  /**
   * Initiate the semaphore.
   */
  public function create() {
    // TODO: semaphore keys should be derived from segment keys 
    //       e.g. 1st free key after seg key is read sem key
    //            2nd free key after seg key is write sem key
    //       this leads to a maximum of about 1.431.655.765 createable 
    //         segments with a read and write semaphore attached
    $this->set_sem_key();
    if(is_null($this->sem_acquire_timeout)) $this->set_sem_acquire_timeout();
    $this->set_sem_res();
  }

  /**
   * Get a System V IPC key.
   * @throws
   */
  private function set_sem_key() {
    $this->sem_sysv_ipc_key = @ftok($this->sem_file, $this->sem_proj_id);
    if($this->sem_sysv_ipc_key < 0) {
      // throw exception
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
    if(in_array($at, self::sem_valid_access_types, true))
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
    if(is_null($this->sem_res))
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
  public function set_sem_acquire_timeout($timeout = self::sem_acquire_default_timeout) {
    if(strncmp(gettype($timeout),"integer",7)==0
       && $timeout >= 0) $this->sem_acquire_timeout = $timeout;
    else
       $this->sem_acquire_timeout = self::sem_acquire_default_timeout;
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
    if(strncmp(gettype($max_acquire),"integer",7)==0 && $max_acquire>0)
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
   * Set $this->sem_proj_id. Should be a single character.
   * @param $proj_id preject identifier passed on to ftok().
   * @throws 
   */
  public function set_sem_proj_id($proj_id = "0") {
    if(strncmp(gettype($proj_id),"string",6)==0 
       && strlen($proj_id)==1) 
      $this->sem_proj_id = $proj_id;
    else
      $this->sem_proj_id = "0";
      //throw ParamNotValidException
  }

  /**
   * Get the project identifier for this semaphore.
   * @return project identifier
   */
  public function get_sem_proj_id() {
    return $this->sem_proj_id;
  }

  /**
   * Set the permissions for the semaphore.
   * @param $perms octal integer representing (file) permissions
   */
  public function set_sem_perms($perms = 0600) {
    if(strncmp(gettype($perms),"integer",7)==0 && $perms>0)
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
    return __CLASS__." (pid=".$this->get_sem_proj_id().
                     ", key=".$this->get_sem_key().
                     ", perms=".$this->get_sem_perms().
                     ", maxac=".$this->get_sem_max_acquire().
                     ", acto=".$this->get_sem_acquire_timeout().
                     ", res=".$this->get_sem_res().
                     ")";
  }

}

?>
