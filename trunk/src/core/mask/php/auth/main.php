<?php

  use core\session\auth\Authenticator as Authenticator;

?>
<table cellpadding="0" cellspacing="0" class="width_max">
  <tr>

    <?php if(!Authenticator::isauthenticated()) { ?>
    <td id="c_auth_login" class="left"></td>
    <?php } else if(Authenticator::isauthenticated()) { ?>
    <td id="c_auth_logout" class="left width_500"></td>
    <?php } ?>

  </tr>
</table>

