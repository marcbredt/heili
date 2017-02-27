<?php

namespace core\html\form;
use core\object\Loadable as Loadable;
use core\html\form\Form as Form;
use core\html\form\FormBuilder as FormBuilder;
use core\session\auth\RightManager as RightManager;

class FormLoader implements Loadable {

  public static function load($xml = null) {

    global $filelogger,$session;

    // setup the form
    $filelogger->info("building form '%' ...",array($xml));
    $f = new Form($xml);
    $fb = new FormBuilder($f);

    // check (user) rights and roles before building
    if(RightManager::has($f)) $fb->build();

  }

}

?>
