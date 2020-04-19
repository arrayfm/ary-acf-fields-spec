<?php

namespace AryAcfFields;

class Files {

  function get_files_in_folder($folder){
    $files = $this->get_field_folder($folder);
    if($files){
      $dir = new \DirectoryIterator($files);
      $file_paths = [];
      foreach($dir as $file){
        $_filename = $file->getFilename();

        if(
          !$file->isDot() && 
          !$file->isDir() && 
          $file->isFile() && 
          $file->isReadable() && 
          $file->getExtension() === 'php' &&
          !in_array($_filename, self::IGNORE_FILES)
        ){
          $__key = $file->getBasename('.php');
          $file_paths[$__key] = $file->getPathname();
        }
      }

      return $file_paths;
    } else {
      return NULL;
    }
  }

  function get_field_folder($folder){
    $dir = get_template_directory();
    $folder_dir = $dir . '/_fields/' . $folder . '/';
    if(!$folder || !file_exists($folder_dir)){
      return NULL;
    } else {
      return $folder_dir;
    }
  }

  const IGNORE_FILES = [];

}