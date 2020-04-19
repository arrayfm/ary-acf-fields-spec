<?php

namespace AryAcfFields;

class AryAcfFields {

  private $fields;

  public function __construct(){
    new Toolbars();
    $this->fields = new Fields();
  }

  public function helper($functionName, ...$params){
    $h = new Helper();

    if(method_exists($h, $functionName)){
      return $h->$functionName(...$params);
    }
  }

  public function getConstant($constant){
    $constants = $this->fields->get_set('constants');
    if(isset($constants[$constant])){
      return $constants[$constant];
    } else {
      return NULL;
    }
  }

}