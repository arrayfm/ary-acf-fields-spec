<?php 

/*
  Plugin Name: ARY ACF Fields Spec
  Description: Utility to map fields in theme to ACF
  Version: 0.0.1
  Author: Array
  Author URI: https://array.design
  Text Domain: ary-acf-fields-spec
*/

if(!defined('ABSPATH')){
  exit;
}

define('ARY_AFS_VERSION', '0.0.1');

require_once __DIR__ . '/vendor/autoload.php';

function aryAcfFields() {
	global $aryAcfFields;
	
	if(!isset($aryAcfFields)){
		$aryAcfFields = new AryAcfFields\AryAcfFields();
  }
  
	return $aryAcfFields;
}

aryAcfFields();