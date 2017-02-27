<?php

namespace core\util\key;
use core\util\key\UUID as UUID;
use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;
use core\exception\shm\SemaphoreException as SemaphoreException;
use core\exception\param\ParamNotValidException as ParamNotValidException;

/**
 * This class is used to generate keys for multiple purposes like
 * creating resources for semaphores or segments. It addiinally could
 * be used to generate UUIDs.
 * 
 * @author Marc Bredt
 * @see <a href="http://www.ietf.org/rfc/rfc4122.txt" target="_blank">RFC 4122</a> 
 *      A Universally Unique IDentifier (UUID) URN Namespace 
 * @see sem_get()
 * @see shmop_open()
 */
class Key {

  /**
   * Key generated during invocation.
   */
  private $key = null;

  /**
   * Supported key types. Currently "sem", "seg".
   */
  private $types = array("sem","seg","uuid");

  /**
   * Current type bound to this key.
   */
  private $type = null;
 
  /**
   * Integer representing the lower border to start searching unused keys of.
   */
  private $lower = null;

  /**
   * Integer representing the upper border to stop searching unused keys at.
   */
  private $upper = null;

  /**
   * Set up a key for the type.
   * @param $type string characterizing the type to create a key for
   * @param $lower lower border to start searching unused keys of
   * @param $upper upper border to stop searching unused keys at
   * @throws ParamNotValidException
   * @see SemaphoreHandler
   */
  public function __construct($type = null,
                              $lower = null, $upper = null) {
  
    global $filelogger;
 
    // verify $type
    if(Validator::in($type, $this->types)) { $this->type = $type; 
    } else {
      $filelogger->log("%, type=%", 
        array(new ParamNotValidException("\$type"),$type));
      throw(new ParamNotValidException("\$type"));
    }

    // verify $lower
    if(Validator::isa($lower,"integer") 
       || Validator::isa($lower,"null")) { 
      $this->lower = $lower;
    } else {
      $filelogger->log("%, lower=%", 
        array(new ParamNotValidException("\$lower"),
              StringUtil::get_object_value($lower)));
      throw(new ParamNotValidException("\$lower"));
    }

    // verify $upper
    if(Validator::isa($upper,"integer") 
       || Validator::isa($upper,"null")) { 
      $this->upper = $upper;
    } else {
      $filelogger->log("%, upper=%", 
        array(new ParamNotValidException("\$upper"),
              StringUtil::get_object_value($upper)));
      throw(new ParamNotValidException("\$upper"));
    }

  }

  /**
   * Get a key depending on the type set. Although it is possible to create 
   * multiple semaphores for the same key using sem_get() this function checks
   * if the key used provided the first resource id on a semaphore. When
   * proofing keys already in use for segments the segment will be attached
   * directly. Regarding the bugs below it is necessary to avoid providing 0 
   * as a key as either multiple semaphores or segments are creatable or it
   * is no more possibleto attach to segments created with such a key.  
   *
   * @return integer usable as key for different tasks like creation of
   *         semaphores or segments 
   * @req PHP >= 5.6.1, sem_acquire()'s $nowait parameter
   * @throws SemaphoreException
   * @bug <a href="https://bugs.php.net/bug.php?id=71955" target="_blank">
   *      BUG#71955</a>
   * @bug <a href="https://bugs.php.net/bug.php?id=71956" target="_blank">
   *      BUG#71956</a>
   */
  public function get() {

    global $filelogger;

    switch($this->type) {

      // as sem_get always returns a resource, also if it already exists
      // there is need to take care of the keys already used
      case "sem":

          return $this->get_lower()+1; // dummy implementation

          $key = $this->get_lower(); 
          $res = null;

          // check the resource id
          // to check if there was only one resource created there is probably
          // need to track the semaphore ids generated so far
          while(!Validator::isa($res,"resource")) {
 
            $filelogger->log("checking key=%", array($key));

            // abort if key is 0 considering the bugs above 
            if($key==0) { $key += 1; continue; }

            // throw an exception if we hit the upper semaphore limit
            if($key>$this->get_upper()) {
              $this->filelogger("%, key=%", 
                         array(new SemaphoreException("no more keys",3),$this));
              throw(new SemaphoreException("no more keys",3)); 
            }

            // close any previously opened resource 
            // NOTE: removing any semaphore here is critical as valid ones could
            //       be removed as well
            //if(Validator::isa($res,"resource")) sem_remove($res);

            // increase the lower key border locally
            $key += 1;

            // get a semaphore id pointing to a resource which is not
            //   acquireable by default to avoid attachments via this key
            //   means $max_acquire must be 0 
            // this implies that any acquisition of semaphores should not be
            //   blocked as faulty connections would block any process then
            $res = sem_get($key,0); 
          }
 
          // close the semaphore as the key is going to be used in another 
          // context
          // NOTE: removing any semaphore here is critical as valid ones could
          //       be removed as well
          sem_remove($res);
 
          // set the key
          $this->set_key($key);

        break;

      // checking if a key is already in use for a segment 
      case "seg":
       
          $key = $this->get_lower(); 
          $res = null;

          // as long as a segment is attachable it exists and therefor the key
          // is in use
          while(!Validator::isa($res,"boolean")) {

            $filelogger->log("checking segment key=%", array($key));

            // abort if key is 0 considering the bugs above 
            if($key==0) { 
              $filelogger->log("skipping segment key=%", array($key));
              $key += 1; continue; }

            // throw an exception if we hit the upper semaphore limit
            if($key>$this->get_upper()) {
              $filelogger->log("%, segment key=%", 
                         array(new SemaphoreException("no more keys",3),$key));
              throw(new SemaphoreException("no more keys",3)); 
            }

            // close any previously attachment 
            if(Validator::isa($res,"integer")) shmop_close($res);

            // get a segment id, 3rd and 4th parameter doesn't matter as 
            // they were set during creation of the segment and are not 
            // changable anymore
            $res = @shmop_open($key,"a",0000,0);
            if(Validator::equals($res,false)) { 
              $filelogger->log("setting segment key=%",array($key));
              $this->set_key($key);
            } else {
              $filelogger->log("segment key=% already exists",array($key));
              // increase the lower key border locally
              $key += 1;
            }

          }

        break;

      // get universal unique identifier
      case "uuid": 

          $uuid = new UUID("1");
          $this->set_key($uuid->get());

        break;

      default: break;

    }
    
    return $this->get_key();

  }

  /**
   * Get the current lower border to start searching keys of. This border
   * should always equal the last key used to keep track of exclusiveness
   * for semaphore keys as sem_get() returns resource id for semaphores
   * used if they already exist as well. 
   * @return lower key border
   */
  private function get_lower() {
    return $this->lower;
  }

  /**
   * Get the current upper border to stop searching keys at.
   * @return upper key border
   */
  private function get_upper() {
    return $this->upper;
  }

  /**
   * Get the current type set.
   * @return string representinte current type set.
   */
  private function get_type() {
    return $this->type;
  }

  /**
   * Get the current key generated.
   * @return generated key
   */
  public function get_key() {
    return $this->key;
  }

  /**
   * Set the generated key. This function currently does not check any key
   * provided as it is just used by this class.
   * @param $key generated key
   */
  private function set_key($key = null) {
    $this->key = $key;
  }

  /**
   * Dump information for this key instance.
   * @return string representing key instance
   */
  public function __toString() {
    return __CLASS__." (key=".$this->get_key().
                     ", type=".$this->get_type().
                     ", lower=".(Validator::isa($this->get_lower(),"null") ? "null" 
                                   : strval($this->get_lower())).
                     ", upper=".(Validator::isa($this->get_upper(),"null") ? "null" 
                                   : strval($this->get_upper())).
                     ")";
  }

}

?>
