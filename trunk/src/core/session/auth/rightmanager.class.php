<?php

namespace core\session\auth;
use core\util\param\Validator as Validator;
use core\exception\auth\RightException as RightException;

/**
 * Central manager to verify (user) rights for different assets like masks, 
 * forms, resources and other stuff.
 * @author Marc Bredt 
 */
class RightManager {

  public static function has($asset = null) {
   
    global $filelogger,$session;

    // get the user
    if(!Validator::isa($session,"null") && $session->has("user")
       && !Validator::isempty($session->get("user"))) {
      $user = unserialize($session->get("user"));
      $role = $user->get_role();
    } else {
      $user = null;
      $role = "guest";
    }
    $filelogger->debug("determined role for user '%' is '%'",array($user,$role));

    // initiate the roles array which will contain valid roles for the asset
    $roles = array();

    // check rights for different assets
    switch(Validator::get_type($asset)) {

      // check rights for asset: Form
      case "core\html\\form\Form" :
        $roles = explode(",",$asset->getRoles());
        break;
    
      // TODO: several other assets, mask/formelement
      // Mask
      case "core\mask\Mask" :

        // check against mask definitions in masks.xml
        $xml = PATH_CONF."/xml/masks.xml";
        $dtd = PATH_DTD."/data/masks.dtd";
        $x = new XMLDocument($xml,$dtd,true);
        $xm = $x->xpath("//mask[@name='".$asset->get_nid()."' and @active='y']");
        $filelogger->debug("mask config=%",array($xm));
        $roles = explode(",",$x->xpath("string(//mask[@name='".$asset->get_nid().
                                        "' and @active='y']/@roles)"));
        break;

      // FormElement
      case "core\html\\form\io\FormElement" : 
          $roles = $asset->get_roles();
        break;

      // invalid asset
      default: 
        $filelogger->alert("invalid asset type %",array(Validator::get_type($asset)));
      break;

    } // end switch

    // send the respone
    if (Validator::in("*",$roles) || Validator::in($role,$roles)) {
      $filelogger->info("sufficient rights to % for user % with role % (%)",
                        array($asset,$user,$role,$roles));
      return true;
    } else {
      $filelogger->alert("insufficient rights to % for user % with role % (%)",
                         array($asset,$user,$role,$roles));
      throw(new RightException());
    }

  }  

}

?>
