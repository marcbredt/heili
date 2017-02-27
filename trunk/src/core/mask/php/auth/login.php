<?php 

  global $language, $session; 
  use core\util\param\Validator as Validator;
  use core\session\token\Tokenizer as Tokenizer;

  $token = Tokenizer::get();
  $session->set("tokens","login",$token);
  
?>
<div id="c_login">

  <form id="mlogin" action="" method="post">

    <div id="c_login_user_text" class="div_left div_text text_white text_bold">
      <?php echo $language->get("user"); ?>:
    </div>

    <div id="c_login_user_input" class="div_left">
      <input name="core:email" type="text" class="input" />
    </div>

    <div id="c_login_pass_text" class="div_left div_text text_white text_bold">
      <?php echo $language->get("pass"); ?>:
    </div>

    <div id="c_login_pass_input" class="div_left">
      <input name="core:password" type="password" class="input" />
    </div>

    <div id="c_login_submit" class="div_left div_margin_top_1_px">
      <button name="core:action" type="submit" value="login" class="button text_bold">
        <?php echo $language->get("login"); ?></button>
      <input name="core:token" type="hidden" 
             value="<?php echo $session->get("tokens","login"); ?>" />
    </div>

  </form>

</div>

