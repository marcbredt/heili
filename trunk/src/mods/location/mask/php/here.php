<?php

  use core\mask\Mask as Mask;
  echo PROJECT_TITLE." | ".(new Mask("location","current"))->get();

?>
