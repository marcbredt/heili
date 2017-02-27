<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
  <title>Another title</title>
</head>
<body>
  <!-- no input fields directly in form allowed, therefor surround with div/table -->
  <form id="bar" method="get" action="">
  
    <div>

    <!-- text -->
    <textarea name="module:input_0" id="id0" class="class" cols="10" rows="1" title="A" disabled="disabled"></textarea><br/>
    <input name="module:input_1" id="id1" class="class" title="A" alt="A" type="text" size="10" maxlength="4"  disabled="disabled"/>
    <br />

    <!-- password -->
    <input name="module:input_2" id="id2" class="class" title="B" alt="B" type="password" size="10" maxlength="4"  disabled="disabled"/>
    <br />

    <!-- checkboxes -->
    <input name="module:input_3" id="id3" class="class" title="B" alt="B" type="checkbox" value="y" />
    <br />

    <input name="module:input_4" id="id4" class="class" title="B" alt="B" type="checkbox" value="y"  disabled="disabled" />
    <br />

    <!-- radios -->
    <input name="module:input_5" id="id5" class="class" title="B" alt="B" type="radio" value="1"  disabled="disabled"/>
    <br />

    <input name="module:input_5" id="id6" class="class" title="B" alt="B" type="radio" value="1" checked="checked"  disabled="disabled"/>
    <br />

    <!-- select -->
    <select name="module:input_6" id="id7" class="class" title="B" size="10" multiple="multiple">
      <optgroup id="id71" class="class" title="" label="Kategeorie #1">
        <option id="id711" class="class" title="" value="1">Eins</option>
        <option id="id712" class="class" title="" value="2" selected="selected">Zwei</option>
      </optgroup>
      <optgroup id="id72" class="class" title="" label="Kategeorie #2">
        <option id="id721" class="class" title="" value="3">Drei</option>
        <option id="id722" class="class" title="" value="4">Vier</option>
      </optgroup>
    </select>
    <br/>

    <!-- data -->
    <input name="module:input_7" id="id8" class="class" type="hidden" value="hval" />
    <br />

    <input name="module:input_8" id="id9" title="B" alt="B" class="class" type="file" disabled="disabled" />
    <br />

    <!-- input buttons -->
    
    <input name="module:input_100" id="id10" class="class" title="B" alt="B" type="submit" value="hinfort" />
    <br />

    <input name="module:input_101" id="id11" class="class" title="B" alt="B" type="reset" value="brechen" />
    <br />

    <input name="module:input_102" id="id12" title="B" alt="B" class="class" type="image" src="crop.jpg" />
    <br />

    <input name="module:input_103" id="id13" class="class" title="B" alt="B" type="button" value="press" />
    <br />

    <!-- buttons -->

    <button name="module:input_104" id="id14" class="class" title="B" type="button" value="b1">foo</button>
    <br />

    <button name="module:input_105" id="id15" class="class" title="B" type="submit" value="b2">bar</button>
    <br />

    <button name="module:input_106" id="id16" class="class" title="B" type="reset" value="b3">poo</button>
    <br />

    </div>

  </form>
</body>
</html>
