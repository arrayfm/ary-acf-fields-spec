<?php

namespace AryAcfFields;

class Toolbars {

  public function __construct(){
    add_filter('acf/fields/wysiwyg/toolbars', array($this, 'set_toolbars'));
  }

  function set_toolbars($toolbars){
    $h = new Helper();
    $h->iterate_file_set('toolbars', function($k, $i){
      $_toolbar_key = ucfirst($k);
      $toolbars[$_toolbar_key] = [];
      $toolbars[$_toolbar_key][1] = $i;
    });
    return $toolbars;
  }

}