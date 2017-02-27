<?php 
  
  global $language, $session; 
  use core\util\param\Validator as Validator;
  use core\util\xml\XMLDocument as XMLDocument;
  use core\session\token\Tokenizer as Tokenizer;
  use core\db\data\DataGetter as DataGetter;

  $token = Tokenizer::get();
  $session->set("tokens","logout",$token);

?>
<table cellpadding="0" cellspacing="0" class="table_inner">
<form name="mlogout" action="" method="post">
  <tr>
    <td class="width_max">
      <?php 
        // get the user object from the session
        $user = unserialize($session->get("user"));

        // get the role name
        $dg = new DataGetter("rolename");
        $data = $dg->get(array(":rname"=>$user->get_role(),
                               ":lab"=>$session->get("language")),false);        

        // print greeter and role
        echo $language->get("hello")." ".$user->get_fname()." ".
               $user->get_lname()." ".
               $language->get("role_as")." ".
               "<a href=\"".$_SERVER["PHP_SELF"]."?core:mask=roles\" ".
                  "class=\"text_white\">".
                 $data[0]["text"]."</a>, ";
      ?>
    </td>
    <td>
      <button name="core:action" type="submit" value="logout"
              class="text_white button_link">
        <?php echo $language->get('logout'); ?></button>
      <input name="core:token" type="hidden" 
             value="<?php echo $session->get("tokens","logout"); ?>" />
    </td>
  </tr>
</form>
</table>
