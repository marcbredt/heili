<?php

namespace core\layout;
use core\util\param\Validator as Validator;
use core\util\xml\XMLDocument as XMLDocument;
use core\util\map\Map as Map;
use core\layout\TemplateMask as TemplateMask;

/**
 * This class provides access to the layout map.
 * @author Marc Bredt
 */
class TemplateMap extends Map {

  /**
   * Store the template map as XMLDocument.
   */
  private $doc = null;

  /**
   * Create a TemplateMap setting up the map document and the map type first.
   */
  public function __construct($xml = null) {

    $this->type = "core\layout\TemplateMask";
    if(!Validator::isa($xml,"null") && file_exists($xml))
      $this->doc = new XMLDocument($xml,PATH_DTD."/template/map.dtd",true);

  }

  /**
   * Initialize the template mask map.
   */
  public function load() {

    global $filelogger;

    // load masks if the xml document is valid
    if(!Validator::isa($this->doc,"null")) {
      $md = $this->doc->xpath("//map/mask[@active=\"y\"]",true);
      $filelogger->log("md=%, nodes=%",
                       array($md,$md->get_doc()->childNodes[0]),
                       "DEBUG");

      foreach($md->get_doc()->childNodes[0]->childNodes as $mask) {
        $nme = $mask->getAttribute("name");
        $mod = $mask->getAttribute("module");
        $tgt = $mask->getAttribute("target");
        $ttp = $mask->getAttribute("ttype");
        $idx = $mask->getAttribute("index");
        $msk = $mask->getAttribute("mask");
        $tpt = $mask->getAttribute("template");
        $typ = $mask->getAttribute("type");
        $tm = new TemplateMask($mod,$tgt,$ttp,$idx,$msk,$tpt,$typ);
        $filelogger->log("#####-0 adding mask %",array($nme),"DEBUG");
        $this->add($nme,$tm);
      }

      $filelogger->log("#####-1 map=%",array($this->map),"DEBUG");
    }

  }

}
