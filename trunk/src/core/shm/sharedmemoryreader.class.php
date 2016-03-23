<?php

namespace core\shm;
use core\util\Reader as Reader;
use core\util\string\StringUtil as StringUtil;
use core\object\LoggableObject as LoggableObject;
use core\exception\ParamNotValidException as ParamNotValidException;
use core\exception\shm\PayloadExtractionException as PayloadExtractionException;
use core\exception\shm\DelimiterNotFoundException as DelimiterNotFoundException;

/**
 * This class implements a shared memory reader reagarding the segment
 * layout currently set.
 * @author Marc Bredt
 * @see SharedMemorySegment::set_shm_seg_layout()
 */
class SharedMemoryReader extends LoggableObject implements Reader {

  /** 
   * Element which shoud be read, Could be anything like a file, stream,
   * shared memory segment, etc.
   */
  private $element = null;

  /**
   * Supported search types.
   * Not modifyable as  the search type needs to be implemented by this
   * class.
   */
  private $supported_search_types = array("native", "avltree");

  /**
   * Default search type.
   */
  private $search_type = "native";

  // TODO: check if it's valueable to use a AVLTreeImplementation for searching
  //private $avltree = null;

  /**
   * Set the $element to read from. For this reader it should be a shared
   * memory segment.
   */
  public function __construct($seg = null) {
    if(strncmp(gettype($seg),"object",6)==0
       && strncmp(get_class($seg),"core\shm\SharedMemorySegment",26)==0)
      $this->element = $seg;
    else
      $this->element = null;
  }

  /**
   * This function implements the reading mechanism for a shared memory
   * segment following a specific layout.
   * @param $index position that should be read regarding the layout.
   *               if $index is left -1 then the upcoming parameters will
   *               be used to perform a search otherwise they are used to
   *               perform checks on the elment addressed.
   * @param $skey string key for the element's $kpos'th node
   * @param $kpos $skey position regarding the segment layout
   * @param $val value of the last serialized element stored
   *             if it is a string the type of the objects will be compared
   *             otherwise the obejects will be compared
   * @return trimmed string for the whole contents of the segment if 
   *         $index is -1, if $index is >= 0 and valid the complete 
   *         element at this position, otherwise an empty string
   */
  public function read($index = -1, 
                       $skey = "", $kpos = -1, $val = null, $full = true) {
    
    $rs = "";
    $this->log(__METHOD__.": idx=%, skey=%, kpos=%, val=%, full=%", 
               array($index,$skey,$kpos,StringUtil::get_object_string($val),
                     $full));
 
    // reading the whole contents if index is not set and the upcoming
    // parameters either
    if(strncmp(gettype($index),"integer",7)==0 && $index == -1
       && strncmp(gettype($skey),"string",6)==0 && strlen($skey)==0
       && strncmp(gettype($kpos),"integer",7)==0 && $kpos==-1) {

      $s = shmop_read($this->element->get_shm_seg_id(),0,
                      $this->element->get_shm_seg_size());
      if($s) $rs = trim($s);

    // searching if the index is not set but parameters 2 to 5 are
    // but not with default values
    } else if(strncmp(gettype($index),"integer",7)==0 && $index == -1
            && strncmp(gettype($skey),"string",6)==0 && strlen($skey)>0
            && strncmp(gettype($kpos),"integer",7)==0 && $kpos>-1) {

      $rs = $this->search($index,$skey,$kpos,$val,$this->get_search_type());

    // reading an index is a bit more complex
    } else if(strncmp(gettype($index),"integer",7)==0
              && $index > -1) {

      $xcontents = $this->get();
      $xcli = count($xcontents)-1;

      // now lets get the requested element if it exist
      // and complete its layout
      if($index <= $xcli ) 
        $rs = trim($xcontents[$index]).
                     $this->element->get_shm_seg_var_eright().
                     $this->element->get_shm_seg_var_delimiter(); 
    } 

    if($full) return $rs;
    else return unserialize(
                  substr($rs, 
                         strrpos($rs, $this->element->get_shm_seg_var_eleft())+1,
                         ((strlen($rs)-strlen(
                                         $this->element->get_shm_seg_var_eright().
                                         $this->element->get_shm_seg_var_delimiter())
                          ) - strrpos($rs,$this->element->get_shm_seg_var_eleft()) 
                            - 1)
                        )
                );

  }

  /**
   * Get an array representation for the elements currently stored in the
   * shared memory segment regarding the layout. This should be used to
   * address elements currently stored.
   * @param index to address a specific element
   * @return fully exploded shared memory string if $index was not provided,
   *         if $index was provided and is valid the specif element will be 
   *         returned
   */
  public function get($index = -1) {

    // first get the complete contents
    $contents = $this->read();

    // then get the last key for complete exploded memory contents
    // avoiding blank entries at the end
    $xcontents = explode($this->element->get_shm_seg_var_eright().
                           $this->element->get_shm_seg_var_delimiter(), 
                         $contents);

    // delete blank values at the end of the array, key adjustment does not
    // need to be regarded as only the last element could be empty
    if(strlen(trim($xcontents[count($xcontents)-1]))<=0)
      unset($xcontents[count($xcontents)-1]);

    // NOTE: delimiter string used to explode will not be appended for
    //       performance reasons, append it somewhere else if necessary

    // return element at $index as array if $index is valid
    if(strncmp(gettype($index),"integer",7)==0 
       && $index>-1 && $index<count($xcontents)) 
      return array_slice($xcontents,$index,1,1); // preserve indexes

    // or return all elements as array otherwise
    return $xcontents;
  }

  /** 
   * Extracts the serialized payload from an entry. This function is able
   * to handle an (string) entry itself or the entry can be addressed via an
   * index first.
   * @param $entry index pointing to the $entry'th entry in the segment
   *               or if it is a string it needs to be the entry itself
   * @return serialized payload on success or an empty string upon failures
   * @throws ParamNotValidException
   * @throws PayloadExtractionException 
   */ 
  private function get_payload($entry = 0){

    $el = null;

    // if an index was provided via $entry
    if(strncmp(gettype($entry),"integer",7)==0) {

      $el = $this->get($entry);
      if(count($el)!=1) {
        $this->log(__METHOD__.": %", 
                   array(new PayloadExtractionException($this->element.", ".$el, 0)));
        throw(new PayloadExtractionException($this->element.", ".$el, 0));
      }
      $el = $el[$entry]; // get the entry string
 
    // if $entry is an valid entry itself
    } else if(strncmp(gettype($entry),"string",6)==0) {

      // the layout must be valid  
      if(!StringUtil::has_layout($this->element->get_shm_seg_layout(), 
                                 $entry)) {
        $this->log(__METHOD__.": %", 
                   array(new PayloadExtractionException($this->element.", ".$el, 1)));
        throw(new PayloadExtractionException($this->element.", ".$el, 1));

      } 
      $el = $entry; // get the entry string

    } else {
      $this->log(__METHOD__.": %", 
                 array(new ParamNotValidException("entry(int|string)=".gettype($entry))));
      throw(new ParamNotValidExcepton("entry(int|string)=".gettype($entry)));

    }

    // finally extract the payload if all conditions passed
    if(strncmp(gettype($el),"string",6)==0) {

      $olleft = StringUtil::get_offset_last(
                  $this->element->get_shm_seg_var_eleft(), $el);
      $olright = StringUtil::get_offset_last(
                  $this->element->get_shm_seg_var_eleft(), $el);
      return substr($el,($olleft+1),$olright-($olleft+1));

    } else {

      return "";

    }

  }

  /**
   * Get the length for the variables currently stored regarding the memory
   * layout. This function is used to calculate free space after removal of
   * an indexed entry.
   * @return current amount of used of this shared memory segment
   */
  public function getlen() {
    return strlen($this->read());
  }

  /**
   * Search for an element.
   * @param $index to search $skey at $kpos'th position regarding the 
   *        the element layout
   * @param $skey string key value to lookup at position $kpos
   * @param $kpos position to search for $skey
   * @param $kval value to compare the last serialized object with
   * @param $type search type, "native" or "avltree"
   * @return the first complete element found, otherwise an empty string
   */
  public function search($index = -1, $skey = "", $kpos = 0, $kval = null,
                         $type = "native") {

    if(strncmp(gettype($type),"string",6)==0
       && strncmp($type,"native",6)==0) {

      if(strncmp(gettype($index),"integer",7)==0
         && strncmp(gettype($skey),"string",6)==0
         && strncmp(gettype($kpos),"integer",7)==0) {

        // get the element stored at $index or all on failure
        $elems = "";
        if($index>-1) $elems = $this->get($index);

        // find first match for $skey at $kpos in $elems 
        // and compare the last entry with $kval
        $dl = $this->element->get_shm_seg_var_eleft();
        $dr = $this->element->get_shm_seg_var_eright();

        foreach($elems as $k => $v) {

          // first check if there is an $skey 
          $mix = StringUtil::get_offset_first($dl.$skey.$dr,$v);

          // if $skey was found
          if( $mix !== -1) {

            // substring length for the match
            $sub = $substr($v, 0, $mix);
            $sl = strlen($sub);
            $c = 0; // $sl could only be 0 if it is located at the
                    // first 0th position due to substr($v,0,0) is empty
             
            // check if it is the correct $kpos and if left side delimiter(s) 
            // were found
            $dlb = unpack("C*",$dl)[1]; // 91 for "["
            $cc = count_chars($sub, 1);
            if($sl>0 && array_key_exists($dlb, $cc)) {

              if($kpos===$cc[$dlb]-1 && $kval===null) {
                return $v;

              } else if($kpos===$cc[$dlb]-1 && $kval===null) {

                // check the serialized version of $kval passed against the last
                // term of the current element, this check is optional but when 
                // it is passed it need to be checked
                //$sv = $this->get_payload($k);
                $sv = $this->get_payload($v);
                if(strncmp($sv, serialize($kval), strlen($sv))==0) return $v;

              }

            } else {
              $this->log(__METHOD__.": %", 
                         array(new DelimiterNotFoundException("'".$dl."', ".$sub)));
              throw(new DelimiterNotFoundException("'".$dl."', ".$sub));
            }

          }
        }

        return "";

      } else {
        $this->log(__METHOD__.": %", 
                   array(new ParamNotValidException(
                           "index(int)=".gettype($index).
                           ", skey(string)=".gettype($skey).
                           ", kpos(int)=".gettype($kpos))));
        throw(new ParamNotValidExcepton(
                           "index(int)=".gettype($index).
                           ", skey(string)=".gettype($skey).
                           ", kpos(int)=".gettype($kpos)));

      } 

    } else if(strncmp(gettype($type),"string",6)==0
       && strncmp($type,"avltree",6)==0) {

      // TODO: probably use AVLTreeImplementation, see above $avltree
      // NOTE: probably just useful if there is a way to keep an already
      //       created avl object in a way restoring is not exhausting

    } else {
        $this->log(__METHOD__.": %", 
                   array(new ParamNotValidException(
                           "type(string)=".gettype($type))));
        throw(new ParamNotValidExcepton(
                "type(string)=".gettype($kpos)));

    }

  }

  /**
   * Get supprted search types.
   * @return array containing defined search types
   */
  public function get_supported_search_types() {
    return $this->supported_search_types;
  }

  /**
   * Get the search type currently set.
   * @return search type 
   */
  public function get_search_type() {
    return $this->search_type;
  }

  /**
   * Set the search type. Defaults to "native".
   */
  public function set_search_type($type = "native") {
    if(strncmp(gettype($type),"string",6)==0
       && in_array($type,$this->get_supported_search_types(),true))
      $this->search_type = $type;
    else
      $this->search_type = "native";
  }

  /**
   * Print information on this reader.
   * @return a string representing reader information
   */
  public function __toString() {
    return __CLASS__." ( search_types=".preg_replace("/[\r\n]/","", 
                           var_export($this->supported_search_types, true)).
                     ", search_type=".$this->search_type.
                     ", element=[ ".$this->element." ]".
                     " )";
  }

}

?>
