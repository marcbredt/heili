<?php

namespace core\shm\handler;
use core\control\Semaphore as Semaphore;
use core\control\handler\SemaphoreHandler as SemaphoreHandler;
use core\shm\SharedMemorySegment as SharedMemorySegment;
use core\shm\SharedMemoryReader as SharedMemoryReader;
use core\shm\SharedMemoryWriter as SharedMemoryWriter;

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
   * @param $access_type access type 
   * @param $read_limit amount of processes that could access the segment 
   *                    simultaniously 
   * @param $write_limit amount of processes that could access the segment 
   *                     simultaniously 
   * @throws 
   */
  public function create($load = true, 
                         $seg_access_type = 2, $sem_access_type = 0, 
                         $seg_size = 1024,  
                         $read_limit = 10, $write_limit = 1,
                         $pid_seg = "0", 
                         $pid_sem_read = "a", $pid_sem_write = "A", 
                         $ac_read_timeout = 60, $ac_write_timeout = 60) {
    
    // setup the read and write semaphores
    $sem_read = $this->create_semaphore($pid_sem_read, $sem_access_type,
                                               $read_limit, $ac_read_timeout);
    $sem_write = $this->create_semaphore($pid_sem_write, $sem_access_type,
                                                $write_limit, $ac_write_timeout);
    
    // setup the segment
    $segment = $this->create_segment($pid_seg, $seg_size, $seg_access_type,  
                                     $sem_read, $sem_write);

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
   * @param $pid project identifier passed onto ftok().
   * @param $size size of the sement 
   * @param $semr semaphore to control read access
   * @param $semw semaphore to control write access
   * @return a SharedMemorySegment
   * @see SharedMemorySegment
   */
  private function create_segment($pid = "0", $size = 1024, $actype = 2,  
                                  $semr = null, $semw = null) {
  
    $segment = new SharedMemorySegment();

    // set project id
    if(strncmp(gettype($pid),"string",6)==0 && strlen($pid)==1)
      $segment->set_shm_seg_proj_id($pid);
    else
      $segment->set_shm_seg_proj_id("0");

    // set the size
    if(strncmp(gettype($size),"integer",7)==0 && $size>0)
      $segment->set_shm_seg_size($size);
    else
      $segment->set_shm_seg_size(1024);

    // set the access type for the shared memory segment  
    $segment->set_shm_seg_access_type($actype);

    // set the semaphores
    $segment->set_shm_seg_sem_read($semr); 
    $segment->set_shm_seg_sem_write($semw); 

    // setup the segment key
    $segment->set_shm_seg_key();

    // setup the default segment layout, override it when needed
    $segment->set_shm_seg_layout();

    // provide the segment
    return $segment;

  }

  /**
   * Create a semaphore. Used to setup a semaphore with specific values.
   * @param $pid project identifier passed ont ftok().
   * @param $actype access type for this semaphore
   * @param $limit maximum number that can access it simultaneously
   * @param $ac_timeout timeout for acquiring the semaphore created
   * @return a semaphore
   * @see Semaphore
   */
  private function create_semaphore($pid = "0", $actype = 0, 
                                    $limit = 1, $ac_timeout = 60) {

    $semaphore = new Semaphore();

    // set the project identifier
    if(strncmp(gettype($pid),"string",6)==0
       && strlen($pid)==1)
      $semaphore->set_sem_proj_id($pid);
    else
      $semaphore->set_sem_proj_id("0");

    // set access type (for restoring)
    $semaphore->set_sem_access_type($actype);

    // set max procs
    if(strncmp(gettype($limit),"integer",7)==0 && $limit>0)
      $semaphore->set_sem_max_acquire($limit);
    else
      $semaphore->set_sem_max_acquire(1);

    // set acquisition timeout for the semaphore
    if(strncmp(gettype($ac_timeout),"integer",7)==0 && $ac_timeout>0)
      $semaphore->set_sem_acquire_timeout($ac_timeout);
    else
      $semaphore->set_sem_acquire_timeout(60);

    // provide the semaphore
    return $semaphore;
  }

   /**
    * Set a specific shared memory segment as the one that could
    * be used using this SharedMemoryHandler.
    * @param mgmtkey entry of the default management segment for a
    *                SharedMemoryManager
    * @return true if loading was successful
    * @see SharedMemoryManager
    */
   public function load($mgmtentry = null) {
     // $this->shm_seg = ...;
     // $this->get_shm_seg()->shm_sem_read = ...;
     // $this->shm_seh = new SemaphoreHandler($this->shm_sem_read);

     // TODO: set necessary segment attributes from manager segment
     //       key, size, type, atype, layout, semr, semw
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

    // create the semaphores
    $this->get_shm_seh()->load(
      $this->get_shm_seg()->get_shm_seg_sem_write());
    $this->get_shm_seh()->load(
      $this->get_shm_seg()->get_shm_seg_sem_read());

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
               && is_null(shmop_close($this->get_shm_seg()->get_shm_seg_id()))
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
               && is_null($this->get_shm_seg())
               && $this->set_shm_seh(null)
               && is_null($this->get_shm_seh())
               && $this->set_shm_sem_read(null)
               && is_null($this->get_shm_sem_read())
               && $this->set_shm_sem_write(null)
               && is_null($this->get_shm_sem_write())
               && $this->set_shm_access_type(null)
               && is_null($this->get_shm_access_type())
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
   * @throws InvalidAccessTypeException
   * @throws AcquisitionFailedException
   * @return true if attaching successed, otherwise false
   *         the shared memory operational id can be gained
   *         from the segment itself
   */
  public function attach() {
   
    // TODO: only attach if all requirements present 
    //       otherwise get_shm_seg_sem_* will fail if the segment 
    //       wasn't created properly

    // acquire both semaphores 
    // TODO: aquire write semaphore only on put calls and avoid locking 
    //       all reading processes
    $sh = $this->get_shm_seh();
    $sr = $this->get_shm_seg()->get_shm_seg_sem_read();
    $sw = $this->get_shm_seg()->get_shm_seg_sem_write();

    // acquire read semaphore
    $sh->load($sr);
    $sr_ac = $sh->acquire(3);
    // acquire write semaphore
    $sh->load($sw);
    $sw_ac = $sh->acquire(3);

    // if acquiring the semaphores was successful, try to create or open
    // the shared memory segment 
    $aid = -1;
    // reset any previously created attachment
    $this->get_shm_seg()->set_shm_seg_id($aid);
    if($sr_ac && $sw_ac) {

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
        echo "I: Attached (ro).\n";
        $aid = shmop_open($this->get_shm_seg()->get_shm_seg_key(),"a",0400,0);
        $this->get_shm_seg()->set_shm_seg_id($aid);
    
      // read and writeable, write only
      } else if($this->get_shm_seg()->get_shm_seg_access_type()===1) {
        echo "I: Attached (wo).\n";
        $aid = shmop_open($this->get_shm_seg()->get_shm_seg_key(),"w",0200,0);
        $this->get_shm_seg()->set_shm_seg_id($aid);

      } else if($this->get_shm_seg()->get_shm_seg_access_type()===2) {
        echo "I: Attached (rw).\n";
        $aid = shmop_open($this->get_shm_seg()->get_shm_seg_key(),"w",0600,0);
        $this->get_shm_seg()->set_shm_seg_id($aid);
    
      // else 
      } else {
        echo "E: InvalidAccessTypeException\n";
        //throw InvalidAccessTypeException
      }

    } else {
      echo "E: Acquisition failed.\n"; 
      //throw AquisitionFailedException

    }
     
    return ($sr_ac && $sw_ac && $aid>-1); 
  }

  /**
   * Detatch from a shared memory segment.
   * @return true if detaching was sucessful, otherwise false
   */
  public function detach() {

    // detach from segment first
    $seg_closed = false;
    if($this->get_shm_seg()->get_shm_seg_id()>-1) {
      shmop_close($this->get_shm_seg()->get_shm_seg_id());
      $seg_closed = true;
    }

    // then release read semaphore first as write semaphore
    // is the ultimate one in read/write scenarios
    $this->get_shm_seh()->load(
      $this->get_shm_seg()->get_shm_seg_sem_read());
    $rel_r = $this->get_shm_seh()->release();
    $this->get_shm_seh()->load(
      $this->get_shm_seg()->get_shm_seg_sem_write());
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
   
    $success = false;

    // attach to the shared memory segment first
    $this->attach();

    // write 
    $shm_writer = null;
    // write to wo,rw segments only
    if($this->get_shm_seg()->get_shm_seg_access_type()===2
       || $this->get_shm_seg()->get_shm_seg_access_type()===3) {
      $shm_writer = new SharedMemoryWriter($this->get_shm_seg());
      echo "I: ".$shm_writer."\n";
      $success = ($shm_writer->write($values,$key)>0);

    } else {
      echo "E: WriteAccessException\n"; 
      //throw(new WriteAccessException()); 

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

    // TODO: only attach if all requirements present 
 
    // attach to the shared memory segment first
    $this->attach();

    // read ro,rw segments
    $found = "";
    if($this->get_shm_seg()->get_shm_seg_access_type()===0
       || $this->get_shm_seg()->get_shm_seg_access_type()===2) {

      // try to read the element 
      $shm_reader = new SharedMemoryReader($this->get_shm_seg());
      echo "I: ".$shm_reader."\n";
      if(strncmp(gettype($index),"integer",7)==0
         && strncmp(gettype($skey),"string",6)==0
         && strncmp(gettype($pos),"integer",7)==0
         && strncmp(gettype($full),"boolean",7)==0) {
 
        $found = $shm_reader->read($index,$skey,$pos,$val,$full);

      } else {
        echo "E: ParamNotValidException\n";
        //throw(new ParamNotValidExcepton());
      }
 
    } else {
        echo "E: ReadAccessException\n";
        //throw(new ReadAccessExcepton());

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
    if(is_null($segment) || 
       (strncmp(gettype($segment),"object",6)==0 
        && strncmp(get_class($segment),"core\shm\SharedMemorySegment",28)==0))
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
    if(is_null($semhandler) ||
       (strncmp(gettype($semhandler),"object",6)==0 
        && strncmp(get_class($seghandler),"core\control\handler\SemaphoreHandler",37)==0))
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
