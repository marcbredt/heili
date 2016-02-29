<?php

namespace core\shm;
use core\util\string\StringUtil as StringUtil;
use core\util\Writer as Writer;
use core\shm\SharedMemorySegment as SharedMemorySegment;
use core\shm\SharedMemoryReader as SharedMemoryReader;

/**
 * This class implements the abstract class Writer which simply
 * declares an $element to write and a write function that need
 * to implement by any writer.
 * @author Marc Bredt
 */
class SharedMemoryWriter extends Writer {

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
      echo "I: SMW: pstr=".$pstr.", key=".$key."\n";

      // check it against the memory layout first 
      $has_layout = false;
      if(!StringUtil::has_layout($this->element->get_shm_seg_layout(),
                                 $pstr)) {
        echo "E: SharedMemorySegmentLayoutException: ".
             "Data does not fit segment layout.\n";
        //throw(new SharedMemorySegmentLayoutException());
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

      
      echo "I: slen=".$seglen.", len=".strlen($smr->read()).
           ", type=".gettype($smr->read()).
           ", cws=".preg_replace("/[\r\n]/", " ", 
                                 var_export(count_chars($smr->read()),true))."\n";

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
            echo "E: SegmentFreeingSpaceException";
            //throw(new SegmentFreeingSpaceException());
            $has_space = false;

          }

        // lifo segment
        } else if(strncmp($segtype, "lifo", 4)==0) {
            
        // stor segment
        } else if(strncmp($segtype, "stor", 4)==0) {

          // simply check if there is still place left to push elements
          if($seglen+strlen($pstr)<=$this->element->get_shm_seg_size()) {
            $has_space = true;
          
          } else {
            echo "E: NoMoreSegmentSpaceException";
            //throw(new NoMoreSegmentSpaceException());
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
              echo "E: NoMoreSegmentSpaceException\n";
              //throw(new NoMoreSegmentSpaceException());
            } 
                    
          }

        } 
          
        // TODO: pad with spaces to override the rest of the old segment 
        //       if $pstr povided creates a shorter segment string
        //       this avoids corrupting the segment layout

      } else {
        echo "E: ParamNotValidException";
        // throw(new ParamNotValidException());
      }

      // write it using shmop_write
      if($has_layout && $has_space && $woffset>-1
         && $this->element->get_shm_seg_id()>-1) {

        // TODO: WriterThread to allow multiple writes on different 
        //       but "correctly calced" locations/offsets     

        echo "I: tel=".$this->telement.", tels=".strlen($this->telement).
                 ", pstr=".$pstr.", pstrs=".strlen($pstr).
                 ", els=".$this->element->get_shm_seg_size().", woffset=".$woffset.
                 ", woff+lpstr=".($woffset+strlen($pstr)).
                 ", segs-(woff+lpstr)=".($this->element->get_shm_seg_size()
                                          -($woffset+strlen($pstr)))."\n";

        // write modified prefix
        $written_prefix = shmop_write($this->element->get_shm_seg_id(),
                               $this->telement, 0);

        // write additional entry from offset 
        $written_data = shmop_write($this->element->get_shm_seg_id(),
                               $pstr, $woffset);

        // write additional entry from offset 
        // TODO: only write flush for overlapping bytes at reduction
        $written_flush = shmop_write($this->element->get_shm_seg_id(),
                               str_pad("",$this->element->get_shm_seg_size()
                                               -($woffset+strlen($pstr))," "), 
                               $woffset+strlen($pstr));
 
        echo "I: wp='".$written_prefix."', wd='".$written_data.
             "', wf='".$written_flush."'\n";
        if($written_prefix===false || $written_data===false 
           || $written_flush===false){
          echo "E: SharedMemoryWriteException\n";
          //throw(new SharedMemoryWriteException());
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
