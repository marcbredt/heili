<?php

namespace core\shm\handler;
use core\util\key\Key as Key;
use core\util\param\Validator as Validator;
use core\control\Timer as Timer;
use core\control\Semaphore as Semaphore;
use core\control\handler\SemaphoreHandler as SemaphoreHandler;
use core\shm\SharedMemorySegment as SharedMemorySegment;
use core\shm\SharedMemoryReader as SharedMemoryReader;
use core\shm\SharedMemoryWriter as SharedMemoryWriter;
use core\exception\control\TimerException as TimerException;
use core\exception\param\ParamNotValidException as ParamNotValidException;
use core\exception\shm\InvalidAccessTypeException as InvalidAccessTypeException;
use core\exception\shm\SemaphoreException as SemaphoreException;
use core\exception\shm\SegmentException as SegmentException;
use core\exception\shm\ReadAccessException as ReadAccessException;
use core\exception\shm\WriteAccessException as WriteAccessException;

/**
 * This class is used to create/access/manage shared memory segments
 * via semaphore functions.
 * @author Marc Bredt
 */
class SharedMemoryHandler {

  /**
   * Shared memory segment.
   */
  private $shm_seg = null;
  
  /**
   * Semaphore handler to manage the semaphore.
   * This could always be a fresh one.
   */
  private $shm_seh = null;

  /**
   * Setup a shared memory handler to control access to the 
   * shared memory segment and initialize a shared memory segment.
   * @param $load decide weather this handler should load it
   * @param $seg_size integer characterizing the segment size
   * @param $lower integer representing the lower border currently 
   *                     reached through the usage of segments and semaphores
   *                     marks the beginning to search for free keys to use
   *                     for resource creation of segments and semaphores
   * @param $upper integer representing the upper key border 
   * @param $seg_access_type the segment's access type (ro,wo,rw)
   * @param $sem_access_type the segment's access type (ro,wo,rw)
   * @param $sem_read_limit amount of processes that could access the segment 
   *                        through a semaphore simultaniously while reading
   * @param $sem_write_limit amount of processes that could access the segment 
   *                         through a semaphore simultaniously while writing
   * @throws 
   */
  public function create($load = true, $seg_size = 1024, 
                         $lower = null, $upper = null,
                         $sem_read_limit = 100, $sem_write_limit = 1,
                         $seg_access_type = 2, $sem_access_type = 0, 
                         $ac_read_timeout = 60, $ac_write_timeout = 60) {
 
    // setup the read and write semaphores
    $sem_read = $this->create_semaphore($sem_access_type, $sem_read_limit,
                                        $ac_read_timeout, $lower, $upper);
    // TODO: update lower on success - $sem_read->get_sem_key()
    //       therefor need to connect to the main manager segment
    $sem_write = $this->create_semaphore($sem_access_type, $sem_write_limit,
                                         $ac_write_timeout, $lower+1, $upper);
    
    // setup the segment
    $segment = $this->create_segment($seg_size, $seg_access_type,  
                                     $sem_read, $sem_write, $lower, $upper);

    // if the segment should be loaded the handler attributes will be
    // set to manage access on it
    if($load) {
      $this->shm_seg = $segment;
      $this->shm_seh = new SemaphoreHandler();
      $this->alloc();
    }
        
  }

  /**
   * Removes all shared memory segments and semaphores restricting access
   * to a segment. 
   * @return true if removal was successful, otherwise false
   */
  public function destroy() {
    return $this->free();
  }

  /**
   * Create the shared memory segment.
   * @param $size size of the sement 
   * @param $actype access type for the segment (ro,wo,rw)
   * @param $semr semaphore to control read access
   * @param $semw semaphore to control write access
   * @param $lower lower border to start searching unused keys of 
   * @param $upper upper border to stop searching unused keys at 
   * @return a SharedMemorySegment
   * @see SharedMemorySegment
   * @throws ParamNotValidException
   */
  private function create_segment($size = 1024, $actype = 2,  
                                  $semr = null, $semw = null,
                                  $lower = null, $upper = null) {
  
    // create a segment
    $segment = new SharedMemorySegment();

    // get a key for the segment
    $segment_key = null;
    try {
      $segment_key = new Key("seg",$lower,$upper);
      $segment->set_shm_seg_key($segment_key->get());
    } catch(ParamNotValidException $pnve) {
      throw($pnve);
    }

    // set the size
    if(Validator::isa($size,"integer") && $size>0)
      $segment->set_shm_seg_size($size);
    else
      $segment->set_shm_seg_size(1024);

    // set the access type for the shared memory segment  
    $segment->set_shm_seg_access_type($actype);

    // set the semaphores
    $segment->set_shm_seg_sem_read($semr); 
    $segment->set_shm_seg_sem_write($semw); 

    // setup the default segment layout, override it when needed
    $segment->set_shm_seg_layout();

    // provide the segment
    return $segment;

  }

  /**
   * Create a semaphore. Used to setup a semaphore with specific values.
   * @param $actype access type for this semaphore
   * @param $limit maximum number that can access it simultaneously
   * @param $ac_timeout timeout for acquiring the semaphore created
   * @param $lower lower border to start key search of
   * @param $upper upper border to stop searching unused keys at 
   * @return a semaphore
   * @see Semaphore
   * @throws ParamNotValidException
   */
  private function create_semaphore($actype = 0, $limit = 1, $ac_timeout = 60, 
                                    $lower = null, $upper = null) {

    $semaphore = new Semaphore();

    // get a key for the semaphore
    try {
      $semaphore_key = new Key("sem",$lower,$upper);
      $semaphore->set_sem_key($semaphore_key->get());
    } catch(ParamNotValidException $pnve) {
      throw($pnve);
    }

    // set access type (for restoring)
    $semaphore->set_sem_access_type($actype);

    // set max procs
    if(Validator::isa($limit,"integer") && $limit>0)
      $semaphore->set_sem_max_acquire($limit);
    else
      $semaphore->set_sem_max_acquire(1);

    // set acquisition timeout for the semaphore
    if(Validator::isa($ac_timeout,"integer") && $ac_timeout>0)
      $semaphore->set_sem_acquire_timeout($ac_timeout);
    else
      $semaphore->set_sem_acquire_timeout(60);

    // provide the semaphore
    return $semaphore;
  }

  /**
   * Set a specific shared memory segment as the one that could
   * be used using this SharedMemoryHandler.
   * @param $seg shared memory segment to load
   * @return true if loading was successful, otherwise false
   * @throws SegmentException
   * @see SharedMemoryManager
   */
  public function load($seg = null) {

     // verify its a real shared memory segment
     if(self::validate($seg)) $this->shm_seg = $seg;
    
     // setup a semaphore handler if it does not exist yet
     if(Validator::isa($this->shm_seh,"null") ||
        !Validator::isclass($this->shm_seh,"core\control\handler\SemaphoreHandler")) 
       $this->shm_seh = new SemaphoreHandler();

   }

  /**
   * Validate a segment. This is necessary when loading segments.
   * Especially the segment's attributes need to be validated like its
   * key, size, type, atype, layout, semr, semw 
   * @param $seg shared memory segment to load
   * @return true if all segment parameters are valid, otherwise false
   */ 
  public static function validate($seg = null) {

     global $filelogger;

     $filelogger->log("Validating segment %", array($seg));

     // verify its a real shared memory segment
     if(!Validator::isclass($seg,"core\shm\SharedMemorySegment")) {
       return false;

     // verify te segment's key
     } else if(!Validator::isa($seg->get_shm_seg_key(),"integer")) {
       return false;

     // verify te segment's id
     } else if(!Validator::isa($seg->get_shm_seg_id(),"integer")
               || $seg->get_shm_seg_id()<0) {
       return false;

     // verify te segment's size
     } else if(!Validator::isa($seg->get_shm_seg_size(),"integer")
               || $seg->get_shm_seg_size()<0) {
       return false;

     // verify te segment's type
     } else if(!Validator::in($seg->get_shm_seg_type(),
                              $seg->get_shm_seg_supported_types())) {
       return false;

     // verify te segment's access type
     } else if(!Validator::in($seg->get_shm_seg_access_type(),
                              $seg->get_shm_seg_valid_access_types())) {
       return false;

     // verify te segment's layout
     } else if(Validator::isempty($seg->get_shm_seg_layout())) {
       return false;

     // TODO: better check for semaphore values, especially the resource and key
     //         need to be valid which means the semaphores need to be accessible
     //       until then checks are restricted to type checks for key and resource

     // verify te segment's read semaphore
     } else if(!Validator::isclass($seg->get_shm_seg_sem_read(),"core\control\Semaphore")
               || !Validator::isa($seg->get_shm_seg_sem_read()->get_sem_res(),
                                  "resource")
               || Validator::isa($seg->get_shm_seg_sem_read()->get_sem_key(),
                                 "null")) {
       return false;

     // verify te segment's write semaphore
     } else if(!Validator::isclass($seg->get_shm_seg_sem_write(),"core\control\Semaphore")
               || !Validator::isa($seg->get_shm_seg_sem_write()->get_sem_res(),
                                  "resource")
               || Validator::isa($seg->get_shm_seg_sem_write()->get_sem_key(),
                                 "null")) {
       return false;

     }

    // if all checks passed it is a valid shared memory segment
    return true;
    
  }

  /**
   * Allocate a shared memory segment.
   * It should always be opened with write access for the owner
   * otherwise nothing can be stored in it nor can be attached
   * with write permissions if needed.
   * Additionally it should be created with flag "n" to detect
   * $this->shm_seg_key's already used.   
   * Close it after allocation to decrease 'nattch' attribute
   * and free space for (exclusive) attachments.
   * @return true if allocation was successful
   */
  public function alloc() {

    global $filelogger;

    // if the segment is not available try to create it but show errors too
    // just suppressing it here would not show failures when creating it
    if(!$this->is_segment_present()) {

      // NOTE: it is necessary to create a segment with flags "n" and flags 
      //         0600 otherwise the segment information cannot be read and
      //         upcoming shmop_open commands will fail
      //       therefor the handler needs to implement wo, which provides
      //         a top secret area to the software but not to the system
      $soid = shmop_open($this->get_shm_seg()->get_shm_seg_key(),
                         "n",0600,$this->get_shm_seg()->get_shm_seg_size());

      $this->get_shm_seg()->set_shm_seg_id($soid);

    }

    // create/load the semaphores
    try {
      $this->get_shm_seh()->load(
        $this->get_shm_seg()->get_shm_seg_sem_write());
      $this->get_shm_seh()->load(
        $this->get_shm_seg()->get_shm_seg_sem_read());
    } catch(ParamNotValidException $pnve) {
      $filelogger->log("Loading semaphores (r=%, w=%) failed.",
                 array($this->get_shm_seg()->get_shm_seg_sem_read(),
                       $this->get_shm_seg()->get_shm_seg_sem_write()));
    }

    // check if we got a positive integer id for the segment
    $return = false;
    if($this->get_shm_seg()->get_shm_seg_id() >= 0)
      $return = true;

    // TODO: write nattch attribute to shm_seg for
    //       other contexts to get to know the amount
    //       of attached processes to the segment 
    //       necessary during, e.g. SharedMemoryHandler::free()
    // NOTE: see explanation at function nattached.
    //       just implement this functionality if is is really 
    //       necessary.
    //
    // create/load the read/write semaphore
    //if(!$this->is_avail("nattch")) {
    //  $this->put("nattch",0);
    //}

    // reset shm_seg_id as it is not accessible after closing
    return ($return
              ? (shmop_close($this->get_shm_seg()->get_shm_seg_id()) 
                 && $this->get_shm_seg()->set_shm_seg_id(-1))
              : false);
  }

  /**
   * Free the allocation of the shared memory segment if no more
   * processes are bound to it.
   * @return true if removal was successful otherwise false
   */
  public function free() {

    if(!$this->attached() && $this->is_segment_present()) {

      // set the id first as it does not return a boolean
      $this->get_shm_seg()->set_shm_seg_id(
        shmop_open($this->get_shm_seg()->get_shm_seg_key(),
                   "a",0400,$this->get_shm_seg()->get_shm_seg_size()));
      return (
               // destroy and close the attachement to the segment
               $this->get_shm_seg()->get_shm_seg_id()>-1
               && shmop_delete($this->get_shm_seg()->get_shm_seg_id())
               && Validator::isa(
                    shmop_close($this->get_shm_seg()->get_shm_seg_id()),"null")
               && $this->get_shm_seg()->get_shm_seg_id(
                    $this->get_shm_seg()->set_shm_seg_id(-1))==-1

               // unset the semaphores controlling the segment too
               && $this->get_shm_seh()->load(
                    $this->get_shm_seg()->get_shm_seg_sem_read())
               && $this->get_shm_seh()->remove()
               && $this->get_shm_seh()->load(
                    $this->get_shm_seg()->get_shm_seg_sem_write())
               && $this->get_shm_seh()->remove()

               // unset handler variables
               && $this->set_shm_seg(null) 
               && Validator::isa($this->get_shm_seg(),"null")
               && $this->set_shm_seh(null)
               && Validator::isa($this->get_shm_seh(),"null")
               && $this->set_shm_sem_read(null)
               && Validator::isa($this->get_shm_sem_read(),"null")
               && $this->set_shm_sem_write(null)
               && Validator::isa($this->get_shm_sem_write(),"null")
               && $this->set_shm_access_type(null)
               && Validator::isa($this->get_shm_access_type(),"null")
             );
    }
   
    return false;
 
  }

  /**
   * Print this object.
   * @return a string representation for this handler
   */
  public function __toString() {
    return __CLASS__." { segment=".$this->get_shm_seg().
                     ", semhandler=".$this->get_shm_seh().
                     "}";
  }

  /** 
   * Check if any pocess is currently accessing the shared memory
   * segment. Used to grant exclusive access. The value for 'nattch'
   * is stored in the shared memory segment itself to make it accessible
   * for any other local instance/context.
   * @return true if a process has access to the shared memory
   *         segment currently otherwise false
   */
  public function attached() {
    return ($this->nattached()>0);
  }

  /** 
   * Get the number of processes currently attached to the shared 
   * memory segment. Used to grant access up to $this->shm_seg_max_procs
   * processes on this segment. This value should be read from 
   * a shared memory segment to provide the real number of processes
   * attached including those from different contextes.
   * 
   * NOTE: Not really necessary bacause avoidance of inconsistencies
   *       during reads require exclusive write semaphores to be acquired
   *       too. This results in a maximum number of one process being 
   *       attached to a segment simultaneously.
   *       Obviously the max_acquire attribute for a semaphore protecting
   *       read access could be greater 1 but the the attribute for 
   *       semaphores must equal 1 to avoid inconsitencies or corrupt
   *       data during reads. 
   *       If one can make/is sure a shared memory segment will only be read 
   *       during lifetime, e.g. config initialization before any application
   *       access the max_acquire attribute for reading and writing 
   *       semaphores could be the same and greater 1 to allow multiple
   *       processes to read simultaneuosly but acquiring both semaphores
   *       first.
   *       But as any shared memory segment should be writeable at least
   *       for configuration reloads from inside an application instance
   *       the preferred configurtion should be greater 1 for the semaphore
   *       protecting read access and 1 for the semaphore protecting 
   *       write access. 
   *       In heavy read environments this seems to be a bottleneck but
   *       it simply implements a fifo handler/queue where the access time
   *       is just restricted by the underlying cpu scheduler.
   * 
   * @return the number of processes currently attached to this 
   *         shared memory segment.
   */
  public function nattached() {
    //return $this->get("nattch");
    // dummy implementation
    return 0; 
  }

  /**
   * Check if the shared memory segment is still present.
   * @return true if the segment is still present
   */
  public function is_segment_present() {

    // if already allocated avoid creation warning 
    // therefor check if the segment can be attached and suppress warnings
    $already_open = false;

    if($shmop_tmp_id = @shmop_open($this->get_shm_seg()->get_shm_seg_key(),"a",0400,
                                     $this->get_shm_seg()->get_shm_seg_size())) {
      shmop_close($shmop_tmp_id);
      $already_open = true;
    }     

    return $already_open;
  }

  /**
   * Attach to a shared memory segment accessible through this->shm_sem*.
   *
   * Read only attachments can be realized by using shmop_open() with
   * flag "a", read and write access with flag "w", write only with
   * flag "w" and regarding the access type when trying to read from
   * the shared memor segment. With shm_* it is not posible to create 
   * read only attachments as segments need to be created with octal
   * rights 0600. Therfor use shmop_open() here. Regarding this the 
   * functions ::get() and ::put() need to implement a shared memory
   * strategy for reading and especially writing to a segment. This 
   * function needs to attach through the write semaphore bound to 
   * this handler. 
   * 
   * NOTE: Although this piece of software implements different access
   *       types each shared memory segment needs to be opened with
   *       octal rights 0600 (read- and writeable) to use it. From now
   *       it is just restricted to the user that opened it so it is
   *       still accessible by anyone who can fake the user id (at least
   *       root) or is able to connect with the same user (the application
   *       itself). That could be useful for live debugging.
   * 
   * @param $mode attaching mode, controls neccessary semaphore access
   * @throws InvalidAccessTypeException
   * @throws SemaphoreException
   * @return true if attaching successed, otherwise false
   *         the shared memory operational id can be gained
   *         from the segment itself
   */
  private function attach($mode = "r") {
 
    global $filelogger;
   
    // TODO: only attach if all requirements present 
    //       otherwise get_shm_seg_sem_* will fail if the segment 
    //       wasn't created properly
    
    // get a semaphore handler first, but only if the access mode is valid
    $sh = null;
    if(Validator::in($mode, array("r","w"))) {
      $sh = $this->get_shm_seh();
    } else {
      $filelogger->log("%", 
                 array(new SemaphoreException("invalid mode",2)));
      throw(new SemaphoreException("invalid mode",2));
    }

    // acquire write semaphore(s) only on put calls and avoid locking 
    // all reading processes
    $sr = null;
    if($mode=="r") $sr = $this->get_shm_seg()->get_shm_seg_sem_read();

    $sw = null; 
    if($mode=="w") $sw = $this->get_shm_seg()->get_shm_seg_sem_write();
    // TODO: wait for all outstanding reading processes to keep data consistent

    // load the semaphores and try to acquire them
    $sr_ac = null; $sw_ac = null;
    try {
      $sh->load($sr);
      $sr_ac = $sh->acquire();
      $sh->load($sw);
      $sw_ac = $sh->acquire();
    } catch(ParamNotValidException $pnve) {
      $filelogger->log("Loading semaphores (r=%, w=%) failed.",
                 array($this->get_shm_seg()->get_shm_seg_sem_read(),
                       $this->get_shm_seg()->get_shm_seg_sem_write()));
    }

    // if acquiring the semaphores was successful, try to create or open
    // the shared memory segment 
    $aid = -1;
    // reset any previously created attachment
    $this->get_shm_seg()->set_shm_seg_id($aid);
    if(($mode=="r" && $sr_ac!==false) || ($mode=="w" && $sw_ac!==false)) {

      // NOTE: access segments with different segment access types 
      //         regarding SharedMemorySegment::get_shm_seg_access_type()
      //         ro - 0400,a 
      //         wo - 0200,w 
      //         rw - 0600,w
      //       addtionally any reader and writer needs to check the access
      //         restrictions as shmop_open cannot handle different 
      //         permissions/modes

      // NOTE: NULL==0 is true, NULL===0 checks type too
      if($this->get_shm_seg()->get_shm_seg_access_type()===0) {
        $aid = $this->open($this->get_shm_seg()->get_shm_seg_key(),
                           "a", 0400, $this->get_shm_seg()->get_shm_seg_size(), 3);
        $filelogger->log("Attached (ro) to %.", 
                         array($this->get_shm_seg()));
        $this->get_shm_seg()->set_shm_seg_id($aid);
    
      // read and writeable, write only
      } else if($this->get_shm_seg()->get_shm_seg_access_type()===1) {
        $aid = $this->open($this->get_shm_seg()->get_shm_seg_key(),
                           "w", 0200, $this->get_shm_seg()->get_shm_seg_size(), 3);
        $filelogger->log("Attached (wo) to %.", 
                         array($this->get_shm_seg()));
        $this->get_shm_seg()->set_shm_seg_id($aid);

      } else if($this->get_shm_seg()->get_shm_seg_access_type()===2) {
        $aid = $this->open($this->get_shm_seg()->get_shm_seg_key(),
                           "w", 0600, $this->get_shm_seg()->get_shm_seg_size(), 3);
        $filelogger->log("Attached (rw) to %.",
                         array($this->get_shm_seg()));
        $this->get_shm_seg()->set_shm_seg_id($aid);
    
      } else {
        $filelogger->log("%", 
                         array(new InvalidAccessTypeException(
                           $this->get_shm_seg()->get_shm_seg_access_type())));
        throw(new InvalidAccessTypeException(
                $this->get_shm_seg()->get_shm_seg_access_type()));
      }

    }
     
    return ($sr_ac && $sw_ac && $aid>-1); 
  }

  /**
   * This function wraps shmop_open with a Timer and an amount of tries to capture
   * shmop_open/release warnings which then would not provide further access to the
   * shared memory segment. 
   * Additionally using synchronized threads would require PECL pthreads >= 2.0.0,
   * which we will not include to separate accesses from each other.
   * @param $key segment key passed onto shmop_open
   * @param $mode mode the segment should be opened with
   * @param $perms the permissions for the segment
   * @param $size the size of the segment
   * @param $tries number of tries to attach before throwing an exception
   * @param $timeout timeout in seconds for each try
   * @return shared memory segment id
   * @throws TimerException
   * @throws SegmentException
   * @bug 2016-00001 shmop_*, sem_* failures
   * @see Timer
   */
  private function open($key = null, $mode = "w", $perms = 0600, $size = 1024,
                        $timeout = 2, $tries = 3) {

    global $filelogger;

    // set and start the timer
    $timer = null;
    if(Validator::isa($timeout,"integer") && $timeout>0) 
      $timer = new Timer($timeout);
    // run the timer
    if($timer!==null) { 
      $timer->start();
    } else {
      $filelogger->log("%.", 
                       array(new TimerException("creation failed")));
      throw(new TimerException("creation failed"));
    }

    // then try to attach to the segment
    $sid = false;
    $try = 1;
    while($timer!==null && $key!==null && $sid===false && $try<=$tries) {
      // try to create or attach to the segment
      $sid = @shmop_open($key, $mode, $perms, $size);
      if($timer->get()===0 && $timer->get_timed_out()) {
        $filelogger->log("Attaching segment. ".
                          "Try #%. Timer timed out.", array($try));
        $try++;
        $timer->start();
      }
    }

    // throw some exceptions if something failed
    if($sid===false) {
      $filelogger->log("%", 
        array(new SegmentException("creation/attaching failed",5))); 
      throw(new SegmentException("creation/attaching failed",5));
    }

    // otherwise return the shm segment id to access the segment
    return $sid;
  }

  /**
   * Detatch from a shared memory segment.
   * @return true if detaching was sucessful, otherwise false
   */
  private function detach() {

    global $filelogger;

    // detach from segment first
    $seg_closed = false;
    if($this->get_shm_seg()->get_shm_seg_id()>-1) {
      shmop_close($this->get_shm_seg()->get_shm_seg_id());
      $seg_closed = true;
    }

    // then release read semaphore first as write semaphore
    // is the ultimate one in read/write scenarios
    try {
      $this->get_shm_seh()->load(
        $this->get_shm_seg()->get_shm_seg_sem_read());
    } catch(ParamNotValidException $pnve) {
      $filelogger->log("Loading read semaphore failed (%).",
                 array($this->get_shm_seg()->get_shm_seg_sem_read()));
    }
    $rel_r = $this->get_shm_seh()->release();

    try {
      $this->get_shm_seh()->load(
        $this->get_shm_seg()->get_shm_seg_sem_write());
    } catch(ParamNotValidException $pnve) {
      $filelogger->log("Loading write semaphore failed (%).",
                 array($this->get_shm_seg()->get_shm_seg_sem_write()));
    }
    $rel_w = $this->get_shm_seh()->release();

    return ($seg_closed && $rel_r && $rel_w);
  }

  /** 
   * Store a variabe into a shared memory segment to make it
   * accessible for other instances.
   * 
   * Variables in a shared memory segment could look like
   * <pre>[varname][s:9:"contents"];</pre> where their index
   * equals the the index the elements got after explosion
   * with '];' using the function explode().
   *
   * Regarding this the strategy for storing values is clearly 
   * defined as follows:
   * 1. inserting without modifing former variables simply adds
   * 2. updating in between
   *    2.1 with a shorter variable causes shifting the rest left
   *    2.2 with a longer leads to a length check and shifting
   *        the rest right
   * 3. deletion shifts left and implicates a index rearrangement
   *    regarding the function used to split the shared memory
   *    string
   *
   * Any write attempt should be exclusive which means using a separate
   * semaphore with max_acquire set to 1.
   *
   * Conventions for storing values.
   * An array of values could contain more than two values.
   * The array values should follow the layout defined for the shared
   *   memory segment. See SharedMemorySegment::set_shm_seg_layout().
   * The last value should be the main object/variable as it is going 
   *   to be serialized.
   * Regarding theese conventions it is possible to implement different
   *   usage scenarios for a shared memory segment, e.g.
   *   - uuid generator store
   *   - different config stores
   *   - live debugging 
   *   - group chats
   *   - group instances
   *   - tracking instances
   * 
   * TODO: measurment, probably adjusted strategy by just overriding
   *       with white spaces on update/deletion to avoid overhead by 
   *       shifting
   *       another strategy could be shiftondemand
   * 
   * @param $shmid shared memory identifier to allow access to
   * @param $var variable
   * @throws NoMoreSharedMemoryException
   * @throws WriteAccessException
   * @return true if storing was successful otherwise false
   * @see Semaphore::sem_max_acquire
   * @see SharedMemorySegment::set_shm_seg_layout
   */
  public function put($values = array(), $key = -1) {
  
    global $filelogger;
 
    $success = false;

    // attach to the shared memory segment first
    $this->attach("w");

    // write 
    $shm_writer = null;
    // write to wo,rw segments only
    if($this->get_shm_seg()->get_shm_seg_access_type()===2
       || $this->get_shm_seg()->get_shm_seg_access_type()===3) {
      $shm_writer = new SharedMemoryWriter($this->get_shm_seg());
      $filelogger->log("%", array($shm_writer));
      $success = ($shm_writer->write($values,$key)>0);

    } else {
      $filelogger->log("%", 
                       array(new WriteAccessException($this->get_shm_seg())));
      throw(new WriteAccessException($this->get_shm_seg()));

    }

    // detach from the shared memory segment to allow other write ops
    $this->detach();
 
    // return here to be sure we detached
    return $success;

  }

  /**
   * Get an element named $skey stored at $pos with value $val at this
   * position.
   * @param $index index addressing the element that should be gathered.
   *               if the default values for the upcoming parameters are
   *               overridden then additional checks for the element at
   *               $index are performed. if the default index value is
   *               not overridden then the upcoming parameters can be used 
   *               to perom a search.
   * @param $skey string key to lookup for an element in the shared memory 
   *              element
   * @param $pos positional parameter to look up $skey for
   * @param $val value to compare with the last serialized object of the
   *             element adressable by $skey and $pos. if it is a string
   *             then it needs to be the class string the object is an 
   *             serialized is an instance of
   * @param $full set to true if you want to get the complete element
   *              found 
   * @throws ParamNotValidException
   * @throws ReadAccessException
   * @return unserialized object if $full is set to false or the complete
   *         string value for the element adressed by $skey, $pos and $val
   *         stored in the shared memory segment pinned. In case no element
   *         is found or an error occured an empty string is returned.
   */
  public function get($index = -1,
                      $skey = "", $pos = -1, $val = null, $full = true) {

    global $filelogger;

    // TODO: only attach if all requirements present 
 
    // attach to the shared memory segment first
    $this->attach("r");

    // read ro,rw segments
    $found = "";
    if($this->get_shm_seg()->get_shm_seg_access_type()===0
       || $this->get_shm_seg()->get_shm_seg_access_type()===2) {

      // try to read the element 
      $shm_reader = new SharedMemoryReader($this->get_shm_seg());
      $filelogger->log("%", array($shm_reader));
      if(Validator::isa($index,"integer") && Validator::isa($skey,"string")
         && Validator::isa($pos,"integer") && Validator::isa($full,"boolean")) {
 
        $found = $shm_reader->read($index,$skey,$pos,$val,$full);

      } else {
        $filelogger->log("%", 
                   array(new ParamNotValidException(__CLASS__."::get()".
                     ": index(int)=".var_export($index,true).
                     ", skey(string)=".var_export($skey,true).
                     ", pos(int)=".var_export($pos,true).
                     ", val(any)=".var_export($val,true).
                     ", full(bool)=".var_export($full))));
        throw(new ParamNotValidExcepton(__CLASS__."::get()".
                     ": index(int)=".var_export($index,true).
                     ", skey(string)=".var_export($skey,true).
                     ", pos(int)=".var_export($pos,true).
                     ", val(any)=".var_export($val,true).
                     ", full(bool)=".var_export($full)));
      }
 
    } else {
        $filelogger->log("%", 
                         array(new ReadAccessException($this->get_shm_seg())));
        throw(new ReadAccessExcepton($this->get_shm_seg()));

    }

    // detach from the shared memory segment to allow other write ops
    $this->detach();

    // return the element found
    return $found;
 
  }

  /**
   * Checks if a shared memory variable is already set.
   *
   * This function performs a check by trying to match VAR_NAME as follows
   * <pre>"SEP_LEFT" . "VAR_NAME" . "SEP_RIGHT" . 
   *      "SEP_LEFT" . nongreedycontents . "SEP_RIGHT" . "DELIMITER"</pre>,
   * e.g. [nattch][i:32];
   *
   * Therefor the separators/delimiter bound to the SharedMemorySegment 
   * MUST NOT be in any serialization of any type or object. 
   *
   * @param $strkey variable name to search for in shared memory 
   * @throws SharedMemoryReadException
   * @return true if the variable exist
   */
  private function is_avail($strkey = null) {
    // dummy implementaion;
    return false;
  }

  /**
   * Get the shared memory segment curntly bound to this handler.
   * @return shared memory segment currently set
   */
  public function get_shm_seg() {
    return $this->shm_seg;
  }

  /**
   * Set the shared memory segment to handle.
   * @param $segment shm segment
   * @throws 
   * @see SharedMemorySegment
   */
  public function set_shm_seg($segment = null) {
    if(Validator::isa($segment,"null") || 
       (Validator::isa($segment,"object") 
        && Validator::isclass($segment,"core\shm\SharedMemorySegment")))
      return $this->shm_seg = $segment;
    else
      return $this->shm_seg = null;
  }

  /**
   * Get the semaphore handler for instances of this class.
   * @return semaphore handler
   */
  public function get_shm_seh() {
    return $this->shm_seh;
  }
 
  /**
   * Set the semaphore handler used to manage the shared
   * memory segment bound.
   * @param a semaphore handler 
   * @throws 
   * @see SharedMemoryHandler
   */
  public function set_shm_seh($semhandler = null) {
    if(Validator::isa($semhandler,"null") ||
       (Validator::isa($semhandler,"object") 
        && Validator::isaclass($seghandler,"core\control\handler\SemaphoreHandler")))
      return $this->shm_seh = $semhandler;
    else
      return $this->shm_seh = null;
  }

  /**
   * Get the semaphore currently bound to this handler protecting
   * write access to the shared memory segment.
   * @return semaphore protecting write access to the segment
   */
  public function get_shm_sem_write() {
    return $this->get_shm_seg()->get_shm_seg_sem_write();
  }

  /**
   * Get the semaphore currently bound to this handler.
   * Use this semaphore when asking for read access. The difference
   * between the read and write semaphore maybe just the key and
   * the number of processes that are allowed to access t simul-
   * taneously.
   * @return semaphore protecting read access to the current segment
   */
  public function get_shm_sem_read() {
    return $this->get_shm_seg()->get_shm_seg_sem_read();
  }

}
