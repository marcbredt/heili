<?php

namespace core\lang;
use core\lang\Localizable as Localizable;
use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;

/**
 * This class is used to do some language localization stuff.
 * @author Marc Bredt
 */
class Localizator implements Localizable {

  // TODO: some other localizaion stuff like geoip, ...

  /**
   * Get the localized value from $language.
   * @param $s string used for lacalization, must follow the regex
   *           "/^\\\$language->get\('[a-zA-Z_]+'\)$/" to access language 
   *           values loaded
   * @return string from language set $language which
   */
  public static function localize($s = "") {

    global $language;

    return (Validator::matches($s,"/^\\\$language->get\('[a-zA-Z_]+'\)$/")
            && isset($language) ? eval("return $s;") : $s);

  }

}

?>
