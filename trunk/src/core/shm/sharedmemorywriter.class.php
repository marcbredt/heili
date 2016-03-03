<?php

namespace core\shm;
use core\util\string\StringUtil as StringUtil;
use core\util\Writer as Writer;
use core\object\LoggableObject as LoggableObject;
use core\shm\SharedMemorySegment as SharedMemorySegment;
use core\shm\SharedMemoryReader as SharedMemoryReader;
use core\exception\shm\SegmentLayoutException as SegmentLayoutException;
use core\exception\shm\SegmentSpaceException as SegmentSpaceException;
use core\exception\shm\SegmentWriteException as SegmentWriteException;

/**
 * This class implements the abstract class Writer which simply
 * declares an $element to write and a write function that need
 * to implement by any writer.
 * @author Marc Bredt
 */
class SharedMemoryWriter extends LoggableObject implements Writer {

  /** 
   * Element to write to. For this writer it should be a shared memory segment.
   * @see SharedMemorySegment
   */
  private $element = null;

  /**
   * Temporary built string for rewriting shared memory segment contents.
   */ 
  private $telement = "";

  /**
   * Set the segment to write to.
   * @param $seg shared memory segment to write to.
   */
  public function __construct($seg = null) {
    if(strncmp(gettype($seg),"object",6)==0
       && strncmp(get_class($seg),"core\shm\SharedMemorySegment",26)==0) {
      $this->element = $seg;
      $this->log(__METHOD__.": Creating writer for segment %.", array($this->element));
    } else {
      $this->element = null;
    }
  }

  /**
   * Write $values to the shared memory segment in the form
   * '[val1][...][valn-1][serialize(valn)];'. Each value need
   * to match the memory layout related which is currently set 
   * for the segment.
   * @param $values array containing values
   * @param $key postion to write to. -1 means simply append.
   * @throws SharedMemorySegmentLayoutException
   * @return number of bytes written.
   */
  public function write($values = array(), $key = -1) {

    $written_data = 0;

    if(!is_null($this->element) 
       && strncmp(gettype($values),"array",5)==0) {

      // build string to put first
      $pstr = "";
      $l = $this->element->get_shm_seg_var_eleft();
      $r = $this->element->get_shm_seg_var_eright();
      $d = $this->element->get_shm_seg_var_delimiter();
      foreach(array_values($values) as $k => $v) {
        if($k==(count($values)-1)) $pstr = $pstr.$l.serialize($v).$r.$d;
        else $pstr = $pstr.$l.$v.$r;
      }
      $this->log(__METHOD__.": pstr=%, key=%", array($pstr, $key));

      // check it against the memory layout first 
      $has_layout = false;
      if(!StringUtil::has_layout($this->element->get_shm_seg_layout(),
                                 $pstr)) {
        $this->log(__METHOD__.": %", array(new SegmentLayoutException(
                     "layout=".$this->element->get_shm_seg_layout().
                     ", data=".$pstr)));
        throw(new SegmentLayoutException(
                "layout=".$this->element->get_shm_seg_layout().
                ", data=".$pstr));

      } else {
        $has_layout = true;
      }
      
      $woffset = -1;
      $has_space = false;
      // NOTE: concatenation of delimiter string needs to be done
      //       for performance reasons when it is necessary
      $smr = new SharedMemoryReader($this->element);
      // get the lenght of the current data stored
      $seglen = $smr->getlen();
      // segment type
      $segtype = $this->element->get_shm_seg_type();
      // get an indexable segment version
      $xseg = $smr->get();
      // unsetting the temporary element string
      $this->telement = "";

      
      $this->log(__METHOD__.": slen=%, len=%, type=%, cws=%", 
                 array($seglen, strlen($smr->read()), gettype($smr->read()),
                       preg_replace("/[\t ]+/", " ", preg_replace("/[\r\n]/", " ", 
                         var_export(count_chars($smr->read()),true)))));

      // get the offset for simply appending
      if($key==-1) {

        // space required 
        $required = StringUtil::get_offset_last($r.$d, $pstr)+strlen($r.$d);
        // offset for next entry to put
        //$woffset = $this->element->get_shm_seg_size() - $seglen;
        $woffset = $seglen;

        // check the space left/needed before writing

        // fifo segment
        if(strncmp($segtype, "fifo", 4)==0) {
           
          // if the new fully entry does not fit into the segment we free some
          // space according to the segment strategy but only if the new entry
          // fits into the segment itself to avoid uneccessary flushing if the entry 
          // is to big for the empty segment
          if( !($woffsef >= 0 && ($woffset+$required<=$this->element->get_shm_seg_size()))
              &&  strlen($pstr)<=$this->element->get_shm_seg_size()) {
 
            // fifo, free oldest entries first
            $this->telement = $smr->read();
            while(strlen($this->telement)>0 
                  && $woffset+$required>$this->element->get_shm_seg_size()){
               // get the next entry to discard
               $discardoffset = StringUtil::get_offset_first($r.$d, $this->telement)
                                  +strlen($r.$d)-1;
               // free
               $this->telement = substr($this->telement, $discardoffset);
               // recalc free space 
               $woffset = $this->element->get_shm_seg_size() - strlen($this->telement);
            }
            $has_space = true;

          // otherwise there is something wrong with the space
          } else {
            $has_space = false;
            $this->log(__METHOD__.": %", 
                       array(new SegmentSpaceException("freeing failed")));
            throw(new SegmentSpaceException("freeing failed"));

          }

        // lifo segment
        } else if(strncmp($segtype, "lifo", 4)==0) {
            
        // stor segment
        } else if(strncmp($segtype, "stor", 4)==0) {

          // simply check if there is still place left to push elements
          if($seglen+strlen($pstr)<=$this->element->get_shm_seg_size()) {
            $has_space = true;
          
          } else {
            $this->log(__METHOD__.": %", 
                       array(new SegmentSpaceException("no more space", 1)));
            throw(new SegmentSpaceException("no more space", 1));

          } 
        }

      // writing to a specific index is a bit more complex
      // get the offset for writing to an index
      } else if(strncmp(gettype($key),"integer",7)==0 && $key > -1) {

        // if its a valid key
        if($key <= count($xseg)-1) {

          // calculate new minimum length for data stored
          $segnlen = $seglen - strlen($xseg[$key].
                                        $this->element->get_shm_seg_var_eright().
                                        $this->element->get_shm_seg_var_delimiter())
                             + strlen($pstr);
          
          // auto free space for fifo/lifo segment types if there is no more 
          // space left

          // fifo segment
          if(strncmp($segtype, "fifo", 4)==0) {
            
            // if the new segment data length is to big
            if($segnlen > $this->element->get_shm_seg_size()) {
 
              $difflen = $segnlen - $this->element->get_shm_seg_size();
              $tmp_key = 0;
              while($difflen > 0) { 
                  $difflen -= strlen(trim($xseg[$tmp_key]).
                                $this->element->get_shm_seg_var_eright().
                                $this->element->get_shm_seg_var_delimiter());
                  unset($xseg[$tmp_key]);
                  $tmp_key += 1; // increase it as unset doesn't modify indexes
              }
            }
            // after cleaning the fifo queue there should be enough space
            $has_space = true;

          // lifo segment
          } else if(strncmp($segtype, "lifo", 4)==0) {

            // if the new segment data length is to big
            if($segnlen > $this->element->get_shm_seg_size()) {
 
              $difflen = $segnlen - $this->element->get_shm_seg_size();
              $tmp_key = count($xseg)-1;
              while($difflen > 0) { 
                  $difflen -= strlen(trim($xseg[$tmp_key]).
                                $this->element->get_shm_seg_var_eright().
                                $this->element->get_shm_seg_var_delimiter());
                  unset($xseg[$tmp_key]);
                  $tmp_key -= 1; // increase it as unset doesn't modify indexes
              }
            }
            // after cleaning the lifo queue there should be enough space
            $has_space = true;

          // if the segment type is 'stor' simply try to store it or throw an
          // exception if the length do not fit 
          } else if(strncmp($segtype, "stor", 4)==0) {

            // if the modified length is less or equal the segment size 
            // simply write it 
            if($segnlen <= $this->element->get_shm_seg_size()) {
              $has_space = true;

            } else {
              $has_space = false;
              $this->log(__METHOD__.": %", 
                         array(new SegmentSpaceException("no more space", 1)));
              throw(new SegmentSpaceException("no more space", 1));

            } 
                    
          }

        } 
          
      } else {
        $this->log(__METHOD__.": %", array(new ParamNotValidException(
                     __METHOD__.": key(int)=".var_export($key,true))));
        throw(new ParamNotValidException(
                __METHOD__.": key(int)=".var_export($key,true))."\n");

      }

      // write it using shmop_write
      if($has_layout && $has_space && $woffset>-1
         && $this->element->get_shm_seg_id()>-1) {

        // TODO: WriterThread to allow multiple writes on different 
        //       but "correctly calced" locations/offsets     

        $this->log(__METHOD__.": tel=%, tels=%, pstr=%, pstrs=%, els=%, ".
                     "woff=%, woff+lpstr=%, segs-(woff+lpstr)=%",
                   array($this->telement, strlen($this->telement),
                         $pstr, strlen($pstr), $this->element->get_shm_seg_size(),
                         $woffset, ($woffset+strlen($pstr)),
                         ($this->element->get_shm_seg_size()-($woffset+strlen($pstr)))));

        // write modified prefix
        $written_prefix = shmop_write($this->element->get_shm_seg_id(),
                               $this->telement, 0);

        // write additional entry from offset 
        $written_data = shmop_write($this->element->get_shm_seg_id(),
                               $pstr, $woffset);

        // write additional entry from offset 
        // TODO: only write flush/whitespace pad for overlapping bytes at reduction
        $written_flush = shmop_write($this->element->get_shm_seg_id(),
                               str_pad("",$this->element->get_shm_seg_size()
                                               -($woffset+strlen($pstr))," "), 
                               $woffset+strlen($pstr));
 
        $this->log(__METHOD__.": wp=%, wd=%, wf=%", 
                   array($written_prefix, $written_data, $written_flush));

        if($written_prefix===false || $written_data===false 
           || $written_flush===false){
          $this->log(__METHOD__."%", array(new SegmentWriteException($this->element)));
          throw(new SegmentWriteException($this->element));

        }

      }
 
    } // close if
 
    return $written_data;

  } // close write

  /** 
   * Print information on this writer.
   * @return a string representing writer information
   */
  public function __toString() {
    return __CLASS__." ( telement={ ".$this->telement." }".
                     ", element={ ".$this->element." }".
                     " )";
  }

} // close class

?>
