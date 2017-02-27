<?php

namespace core\mask;
use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;
use core\object\Loadable as Loadable;
use core\exception\mask\MaskException as MaskException;

/**
 * Includes a mask. It still throws a warning message if include failed.
 * Currently it is suppressed with "@" but further improvement could implement
 * error handling replacement (check the link below).
 * @see http://stackoverflow.com/questions/1241728/can-i-try-catch-a-warning
 * @author Marc Bredt
 */
class MaskLoader implements Loadable {

  /**
   * Load any mask from $target. The $target directory is a folder containing
   * all masks and loads them from $target/$type/$mask/$template.$type.
   * This method additionally provides the ability to load template files w/o
   * providing a mask name.
   * The default folder for masks is core/mask but can be adjusted using $target
   * which is especially necessary when loading module masks.
   * Examples:
   * <pre>
   *   $mymask = new Mask("mymask","foo","html");
   *   MaskLoader::load($mymask); // core/mask/html/mymask/foo.html
   *   $mymask = new Mask("mymask","bar");
   *   MaskLoader::load($mymask); // core/mask/php/mymask/bar.php
   *   $mymask = new Mask(null,"main");
   *   MaskLoader::load($mymask); // core/mask/php/main.php
   *   $mymask = new Mask("survey","confirm","php","survey");
   *   MaskLoader::load($mymask); // mods/survey/mask/php/confirm.php
   * </pre>
   *
   * TODO: evaluate module's rights/masks tag to determine module global mask
   *       rights and and mask specific roles
   * @param $mask Mask 
   * @param $frame NYI, flag to decide if a surrounding frame should be build
   *                    around the mask loaded
   * @return evaluated mask, especially any php code got evaluated before
   *         returning. necessary to load (only) HTML code into templates
   * @see Mask
   */
  public static function load($mask = null, $frame = false) {

    global $filelogger;

    if(!Validator::isclass($mask,"core\mask\Mask")) {
      $filelogger->err("%, mask=%",array(new MaskException("mask invalid",2),$mask));
      throw(new MaskException("mask not found"));

    } else {
      $filelogger->info("loading mask '%'",array($mask));
      return $mask->get();

    }
 
  }

}

?>
