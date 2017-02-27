<?php

namespace core\session\token;

/**
 * As mcrypt has to many dependencies (configure,php-mcrypt,phpenmod) this
 * tokenizer uses openssl as default. It requires an openssl package >= 0.9.6
 * installed.
 * @author Marc Bredt
 */
class Tokenizer {
 
  public static function get() {

    $bytes = 128;
    $algo = "sha512";
    $token = hash("md5",hash($algo, openssl_random_pseudo_bytes($bytes)));
    return $token;

  }

}

?>
