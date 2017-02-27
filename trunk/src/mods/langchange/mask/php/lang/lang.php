<?php 
  global $language, $filelogger, $session;
  use core\util\param\Validator as Validator;
  use core\util\xml\XMLDocument as XMLDocument;
  use core\util\string\StringUtil as StringUtil;
  use core\session\token\Tokenizer as Tokenizer;
  use core\db\data\DataGetter as DataGetter;

  $token = Tokenizer::get();
  $session->set("tokens","changelang",$token);

  // TODO: css class update for the lang menu

?>

<table cellpadding="0" cellspacing="0">
<tr><td class="width_max"></td><td>

<form id="mlang" action="" method="post">
<table cellpadding="0" cellspacing="0" class="table_inner text_white">
        
  <tr>
    <td>
      <?php 
        echo $language->get("language"); 
      ?>:
    </td>
    <td>

      <select name="core:language" size="1" class="select"
              onchange="this.form.submit();">
      <?php

        $dg = new DataGetter("languages'");
        $data = $dg->get(array(),false);
        foreach($data as $r) {
          // set selected tag for default or cookie language
          $selected = "";
          if(Validator::equals($r['lab'],$session->get("language"))) 
            $selected = "selected=\"selected\"";
          // print it
          echo "<option value=\"".$r['lab']."\" ".$selected.">".
                 $r['text']."</option>\n";
        }
      ?>
      </select>

    </td>
   
    <td>

      <script type="text/javascript">
        //<![CDATA[
        document.write('<input name="core:action" type="hidden" value="changelang"/>');
        //]]>
      </script>
      <noscript>
        <button name="core:action" type="submit" value="changelang" 
                class="text_white button_link">
          <?php echo $language->get('change'); ?>
        </button>
      </noscript>

      <input name="core:token" type="hidden" 
             value="<?php echo $session->get("tokens","changelang"); ?>" />

    </td>

  </tr>

</table>
</form>

</td></tr></table>

