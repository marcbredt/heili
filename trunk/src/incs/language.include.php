<?php 
  
  // language stuff

  // namespace alias for the language class
  use core\lang\Language as Language;

  // load language file
  global $language;
  $language = new Language($session->get("language"));
  $language->load();

?>
