<?php 
  
  global $language, $filelogger, $session; 
  use core\util\param\Validator as Validator;
  use core\util\xml\XMLDocument as XMLDocument;
  use core\util\string\StringUtil as StringUtil;
  use core\db\data\DataGetter as DataGetter;
  use core\session\token\Tokenizer as Tokenizer;

  $token = Tokenizer::get();
  $session->set("tokens","changerole",$token);

?>
<table cellpadding="0" cellspacing="0" class="table_inner">
<form name="msrole" action="" method="post">
  <tr>
    <td class="width_max text_black">
      <?php 
        // get the user object pinned onto the session
        $user = unserialize($session->get("user"));

        // get roles to change to
        $dg = new DataGetter("selectrole");
        $data = $dg->get(array(":cid"=>$user->get_cid(),
                               ":rname"=>$user->get_role(),
                               ":lab"=>$session->get("language")));

        // print some information on the current role
        $dgr = new DataGetter("rolename");
        $datar = $dgr->get(array(":rname"=>$user->get_role(),
                                 ":lab"=>$session->get("language")));

        echo $language->get("logged_in_as")." ".$language->get("role_as").
                 " ".$datar[0]["text"].". <br><br>";

        // if there were other roles found
        if(count($data)>0) {
          echo $language->get("choose")." ". $language->get("your_f")." ".
                 $language->get("role").": <br>";

          // and those this user is able to change to
          foreach($data as $d)
            echo "<a href=\"".$_SERVER["PHP_SELF"]."?core:mask=roles&amp;core:role=".
                   $d["name"]."\">".$d["text"]."</a><br>";
        
        // otherwise print a note that there are no more
        } else {
          echo $language->get("no_more_roles").".";
 
        }
      ?>
    </td>
    <!-- TODO: buttons for role select to submit an action marker as well --> 
  </tr>
</form>
</table>
