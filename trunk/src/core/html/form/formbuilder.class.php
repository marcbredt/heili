<?php

namespace core\html\form;
use core\util\param\Validator as Validator;
use core\util\string\StringUtil as StringUtil;
use core\object\Buildable as Buildable;
use core\html\form\Form as Form;

/**
 * This class is used to create HTML code for any form.
 * @author Marc Bredt
 * @see Form
 */
class FormBuilder implements Buildable {

  private $form = null;

  /**
   * Load the form to build.
   * @param $form core\html\form\Form
   */
  public function __construct($form = null) {
    $this->form = $form;
  }

  /**
   * Build HTML code for a form.
   * 
   * TODO: pass element data
   *
   * Currently the following types of forms are available: 
   * - single, all values for an entry in a two columned table with multiple rows
   * - grid, all values for an entry in one row, up to $this->size values in the
   *         form
   * @param $data data for element group "entries" which will be mapped via result
   *              set names on to elements. Data for elements of group "globals" 
   *              should be set via config where the values are constants or can
   *              be probably defined via evaluators like through Localizator.
   * @return HTML code for the specified form type
   * @see Lacalizator
   */
  public function build($data = array()) {

    global $filelogger;
    $filelogger->info("data = %", array($data));
    $filelogger->debug("#entries = %, #globals = %",
                       array(count($this->form->getElements()),
                             count($this->form->getElements("globals"))));

    // setup layout classes
    if(Validator::equals($this->form->getMode(),"horizontal")) 
      $dclasses="div_left";
    else if(Validator::equals($this->form->getMode(),"vertical")) 
      $dclasses="div_bottom_10px";
    else
      $filelogger->warn("unknown form mode '%',",array($this->form->getMode()));

    $html = "<div id=\"div.form.".$this->form->getModule().".".
                                  $this->form->getName()."\">".
            "\n  <form id=\"form.".$this->form->getModule().".".
                                 $this->form->getName()."\" ".
                      "method=\"post\" ".
                      "action=\"\">\n".
            "    <div class=\"".$dclasses."\">";
    
    switch($this->form->getType()) {

      // single, all values for an entry in a two columned table with
      // multiple rows
      case "single":

          // build w/ data
          if(count($data)>0) {

            for($ixrow=0;$ixrow<count($data);$ixrow++) {
              $html .= "      <div class=\"div_margin_bottom_10px\">\n";
              for($ixcol=0;$ixcol<count($this->form->getElements());$ixcol++) {
                $e = $this->form->getElements()[$ixcol];       
                // element text
                $html .= "\n        <div class=\"div_text ".$dclasses." ".$e->getTclasses()."\">".
                                $e->getTitle().":</div>";
                // input text
                $html .= "\n        <div class=\"".$dclasses."\">".
                                $e->build($ixrow,$ixcol,-1,$data[$ixrow])."</div>";
              }
              $html .= "      </div>";
            } 

          // build w/o data
          } else {
            foreach($this->form->getElements() as $num => $e) { 
              // element text
              $html .= "\n      <div class=\"div_text ".$dclasses." ".
                                  $e->getTclasses()."\">".$e->getTitle().":".
                               "</div>";
              // input text
              $html .= "\n      <div class=\"".$dclasses."\">".
                                  $e->build(0,$num,-1).
                               "</div>";
          }

        }

        break;

      // grid, all values for an entry in one row
      case "grid": 

          // headers
          $html .= "  <div class=\"div_bottom_10px\">\n";
          foreach($this->form->getElements() as $e) 
            $html .= "    <div class=\"div_left".$e->getTclasses()."\">".
                            $e->getTitle()."<div>\n";
          $html .= "  </div>\n";

          // data
          for($ixrow=0;$ixrow<count($data);$ixrow++) {
            $html .= "  <div class=\"div_bottom_10px\">\n";
            for($ixcol=0;$ixcol<count($this->form->getElements());$ixcol++) {
              $e = $this->form->getElements()[$ixcol];
              $filelogger->debug("FormElement = %", array($e)); 
              $html .= "    <div class=\"div_left ".$e->getIclasses()."\">".
                              $e->build($ixrow,$ixcol,-1,$data[$ixrow]).
                           "</div>\n";
            }   
            $html .= "  </div>\n";
          }

        break;

      default: break;

    }

    $html .= "\n    </div>\n";

    // globals
    $html .= "    <div>\n";
    foreach($this->form->getElements("globals") as $num => $g)
      $html .= "      <div class=\"div_left\">".$g->build(-1,-1,$num)."</div>\n";
    $html .= "    </div>\n";

    $html .= "  </form>\n</div>";

    echo $html;

  }
}

?>
