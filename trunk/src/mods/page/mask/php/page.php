<?php 

  global $language, $session;
  use core\util\param\Validator as Validator;
  use core\util\xml\XMLDocument as XMLDocument;
  use core\util\string\StringUtil as StringUtil;
  use core\db\data\DataGetter as DataGetter;

  $pagesize = $session->get("pagesize"); 
  $catasize = $session->get("catasize");
  $pages = ceil($catasize/$pagesize);
  $page = $session->get("page");

  $uri = $_SERVER['PHP_SELF']."?".preg_replace("/&core:page=[1-9]+[0-9]*/", "",
           $_SERVER['QUERY_STRING']);

?>
<table cellpadding="0" cellspacing="0" class="table_inner">
  <tr>
    <?php

      // move left
      echo "<td><a class=\"text_black\" 
                   href=\"".$uri."&amp;core:page=1\">&lt;&lt;</a></td>\n";
      echo "<td><a class=\"text_black\" href=\"".$uri."&amp;core:page=".
                     (($page-1)>0 ? ($page-1) : 1)."\">&lt;</a></td>\n";
      
      // move explicitly
      for($i=1; $i<=$pages; $i++)
        echo "<td><a class=\"text_black\"
                     href=\"".$uri."&amp;core:page=".$i."\">".$i."</a></td>\n";
  
      // move right
      echo "<td><a class=\"text_black\" href=\"".$uri."&amp;core:page=".
                     (($page+1)>$pages ? $pages : ($page+1)).
                     "\">&gt;</a></td>\n";
      echo "<td><a class=\"text_black\" href=\"".$uri."&amp;core:page=".
                     $pages."\">&gt;&gt;</a></td>\n";

    ?>
  </tr>
<table>

