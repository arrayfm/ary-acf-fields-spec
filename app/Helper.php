<?php

namespace AryAcfFields;

class Helper {

  function iterate_file_set($name, $func){
    $set = $this->group_file_set($name);

    if($set){
      foreach($set as $key => $item){
        if(is_callable($func)){
          $func($key, $item);
        }
      }
    }
  }

  function group_file_set($name){
    $file_reader = new Files();
    $files = $file_reader->get_files_in_folder($name);
    
    $set = [];

    if($files){
      foreach($files as $key => $file){
        $data = include $file;
        
        if(!isset($data) || !is_array($data)){
          continue;
        } else {
          $set[$key] = $data;
        }
      }
    }

    return $set;
  }

}