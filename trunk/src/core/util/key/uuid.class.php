<?php

namespace core\uuid;

/**
 * Interface defining functions which can be derived from RFC 4122.
 * An extract of the UUID format from the corresponding RFC
 * <pre>
 *   0                   1                   2                   3
 *   0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1 2 3 4 5 6 7 8 9 0 1
 *   +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
 *   |                          time_low                             |
 *   +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
 *   |       time_mid                |         time_hi_and_version   |
 *   +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
 *   |clk_seq_hi_res |  clk_seq_low  |         node (0-1)            |
 *   +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
 *   |                         node (2-5)                            |
 *   +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+
 * </pre>
 *
 * @author Marc Bredt
 * @see <a href="http://www.ietf.org/rfc/rfc4122.txt" target="_blank">RFC 4112</a>
 */
class UUID {

  /**
   * Stores the uuid version a UUID is generated for.
   */
  private $uuid_version = 1;

  /**
   * Valid UUID versions.
   * 1 - time-based version
   * 2 - DCE security version, with embedded POSIX UIDs
   * 3 - name-based version, uses MD5 hashing
   * 4 - randomly or pseudorandomly generated version
   * 5 - name-based version, uses SHA-1 hashing
   */
  private $uuid_versions = array(1,2,3,4,5);

  /**
   * Store the last UUID generated.
   */
  private $uuid = null;

  /**
   * Create a UUID instance.
   */
  public function __construct($version = 1) {}

  // dummy implementation
  /**
   * Get a UUID.
   */
  public function get() {
    return "db8280ce-9b79-4eba-aea2-7b194984f93a"; 
    //return $this->gen_uuid();
  }

  /*
   * functions for using/accessing the object that implements the uuid 
   * functions or extends the object that implements this interface
   */

  /**
   * Get the current version set.
   * @return current UUID version set
   */
  public function get_uuid_version(){
    return $this->uuid_version;
  }

  /**
   * Generate a UUID.
   * @return a UUID
   */
  private function gen_uuid(){
    return $this->get_uuid_time_low()."-".
           $this->get_uuid_time_mid()."-".
           $this->get_uuid_time_high_and_version()."-".
           $this->get_uuid_clock_seq_and_reserved().
           $this->get_uuid_clock_seq_low()."-".
           $this->get_uuid_node();
  }

  /*
   * functions for building a UUID.
   */

  private function get_uuid_time_low(){}

  private function get_uuid_time_mid(){}

  private function get_uuid_time_high_and_version(){
    // version = bits 4-7 in time_high_and_version field
    
  }

  private function get_uuid_clock_seq_and_reserved(){}

  private function get_uuid_clock_seq_low(){}

  private function get_uuid_node(){
    // v1 - IEEE 802 MAC address of any NIC, random for systems w/o NICs 
    //      w/ multicast bit set to avoid coflicts for with real NICs

    // v3,5 - 48bit constructed from a name

    // v4 - 48bit (pseudo-)randomly created value
  }

  /* 
   * helper functions for building a uuid by concatenating the six
   * functions right before
   */

  private function get_uuid_timestamp(){}

  private function get_uuid_clock_seq(){}

  private function get_uuid_node_portion(){}

}

?>
