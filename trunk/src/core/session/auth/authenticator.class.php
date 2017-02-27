<?php 

namespace core\session\auth;

use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;
use core\util\xml\XMLDocument as XMLDocument;
use core\session\Session as Session;
use core\session\auth\user\User as User;
use core\session\ActionHandler as ActionHandler;
use core\db\data\DataGetter as DataGetter;
use core\exception\mask\MaskException as MaskException;
use core\exception\auth\AuthException as AuthException;

/**
 * This class is used to run or call any authentication operations. 
 * @author Marc Bredt
 */
class Authenticator {

  /**
   * Authenticate a user for a session.
   * @throws AuthException 
   * @return void
   * @see core\session\User
   */
  public static function authenticate() {

    global $session, $filelogger;
    $user = unserialize($session->get("user"));

    if(Validator::isclass($user,"core\session\auth\user\User")) {

      switch(AUTH_METHOD) {
        
        case "db": 
 
          $xml = MOD_AUTH_PATH_CONF."/db/statements.xml";
          $dg = new DataGetter("checkcred",$xml);
          $data = $dg->get(array(":pass"=>$user->get_pass(),
                                 ":email"=>$user->get_email()), false);

          if(count($data)>0 && $data[0]["valid"]==true) {

            $user->set_cid($data[0]["cid"]);
            $user->set_fname($data[0]["firstname"]);
            $user->set_lname($data[0]["lastname"]);
            $user->set_email($data[0]["email"]);
            $user->set_pass($data[0]["password"]);
            $user->set_auth(true);

            // set initial/current role = role with highest rank <> guest
            $dgr = new DataGetter("initrole");
            $datar = $dgr->get(array(":cid"=>$user->get_cid()));
            $user->set_role($datar[0]["name"]);

          } else {
            // unset credentials passed 
            $user->set_email();
            $user->set_pass();

            $filelogger->log("%",array(new AuthException("invalid credentials",1)),
                             "ERR");
            throw (new AuthException("invalid credentials",1));

          }
          
          break;

        // TODO: case "ldap": break;
      
        default:

          $filelogger->log("%",array(new AuthException("invalid method",2)),"ERR");
          throw (new AuthException("invalid method",2));
 
          break;

      } // switch end

      $session->set("user",null,serialize($user));

    }

  }

  /**
   * Check if a user is currently authenticated in a session.
   * @return true if the user was already authenticated, otherwise false 
   * @see core\session\auth\user\User::get_auth()
   */
  public static function isauthenticated() {

    global $session, $filelogger;

    $filelogger->log("session=%", array($session));
    if($session->has("user") && !Validator::isempty($session->get("user"))) 
      $user = unserialize($session->get("user"));
    else return false;

    return (!Validator::isa($user,"null") && $user->get_auth()); 

  }

  /**
   * Checks rights for a mask and the current users role set.
   * @return true if an user is allowed to access a mask
   * @throws MaskExcption
   */
  public static function has_rights($mask = null) {
  
    return RightManager::has($mask);

  }

}

?>
