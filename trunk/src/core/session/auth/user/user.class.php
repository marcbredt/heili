<?php

namespace core\session\auth\user;

use core\object\SerializableObject as SerializableObject;
use core\util\param\Validator as Validator;

class User extends SerializableObject {

  protected $cid = null;

  protected $fname = null;

  protected $lname = null;

  protected $email = null;
  
  protected $auth = false;

  protected $role = null;

  public function __construct($user = "", $pass = "") {
    if(Validator::isa($user,"string")) $this->email = $user;
    if(Validator::isa($pass,"string")) $this->pass = $pass;
  }

  public function get_auth() {
    return $this->auth;
  }

  public function set_auth($auth = false) {
    if(Validator::isa($auth,"boolean")) $this->auth = $auth;
  }

  public function get_cid(){
    return $this->cid;
  }

  public function set_cid($cid = null) {
    if(!Validator::isa($cid,"null") && Validator::matches($cid,"/[0-9]+/"))
      $this->cid = intval($cid);
  }

  public function get_email() {
    return $this->email;
  }

  public function set_email($email = "") {
    if(Validator::isa($email,"string")) $this->email = $email;
  }

  public function get_pass() {
    return $this->pass;
  }

  public function set_pass($pass = "") {
    if(Validator::isa($pass,"string")) $this->pass = $pass;
  }

  public function get_fname() {
    return $this->fname;
  }

  public function set_fname($fname = "") {
    if(Validator::isa($fname,"string")) $this->fname = $fname;
  }

  public function get_lname() {
    return $this->lname;
  }

  public function set_lname($lname = "") {
    if(Validator::isa($lname,"string")) $this->lname = $lname;
  }

  public function get_role() {
    return $this->role;
  }

  public function set_role($role = null) {
    if(!Validator::isa($role,"null") && Validator::isa($role,"string"))
      $this->role = $role;
  }

  public function __toString() {
    return get_class($this)."-".spl_object_hash($this)."( ".
           "cid=".$this->cid.", ".
           "fname=".$this->fname.", ".
           "lname=".$this->lname.", ".
           "email=".$this->email.", ".
           "auth=".intval($this->auth).", ".
           "role=".$this->role.
           " )";
  }

}

?>
