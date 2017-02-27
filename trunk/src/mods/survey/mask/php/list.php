<?php 
  global $language, $session; 
  use core\util\xml\XMLDocument as XMLDocument;
  use core\util\param\Validator as Validator;
  use core\util\string\StringUtil as StringUtil;
  use core\session\token\Tokenizer as Tokenizer;
  use core\db\data\DataGetter as DataGetter;

?>
<table cellpadding="0" cellspacing="0" class="table_inner text_black">
  <?php

    // source tree describing database statements used to build a survey  
    $qsource = "surveys|survey_questions_only|survey_answers_only";

    $dg = new DataGetter("surveys");
    $data = $dg->get(array(":lab"=>$session->get("language")),false);

    foreach($data as $r) {
      echo "<tr>\n".
           "  <td>".$r["text"]."</td>\n".
           "  <td>\n".
           "    <a href=\"".$_SERVER["PHP_SELF"]."?core:mask=survey".
                          "&amp;core:stree=".$qsource."&amp;survey:sid=".
                   $r["sid"]."\">".$language->get("start")."</a>\n"; 
           "  </td>\n".
           "</tr>\n"; 
    }
   
  ?>
</table>
