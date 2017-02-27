<?php 

namespace core\db\data;

/**
 * This class is used to represent result sets gathered as structured data
 * binding a XMLDocument on to it which will be additionally blow up elements
 * while executing a tree statement. E.g.
 * 
 * <pre>
 *   <statement provides=":lang">
 *      select * from lang;
 *      <statement>
 *        select * from lantext where lang = :lang;
 *      </statement>
 *   </statement>
 * </pre>
 * will lead to an XMLDocument looking something like
 * <pre>
 *   <languages>
 *     <lang name="de">
 *       <element name="foo">BARDE</element>
 *     </lang>
 *     <lang name="en">
 *       <element name="foo">BAREN</element>
 *     </lang>
 *   </languages>
 * </pre>
 * 
 * This way it is possible to pass a complete result set (tree) onto the
 * FormBuilder where each entry then has all its data defined.
 * 
 * @author Marc Bredt 
 * @see FormBuilder
 */
class DataObject {

}

?>
