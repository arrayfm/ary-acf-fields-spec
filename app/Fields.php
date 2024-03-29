<?php

namespace AryAcfFields;

class Fields {

  private $fields;
  private $blocks;
  private $constants;

  public function __construct(){
    $this->read_files();
    add_action('acf/init', array($this, 'setup_fields'));
  }

  function read_files(){
    $h = new Helper();
    $this->fields = $h->group_file_set('fields');
    $this->blocks = $h->group_file_set('blocks');
    $this->constants = $h->group_file_set('constants');
  }

  function setup_fields(){
    $h = new Helper();

    $h->iterate_file_set(['groups', 'sets'], function($k, $i){
      $_d = array_merge($i, [
        'key' => $k,
        'position' => $this->arraykey_merge_default($i, 'position', 'normal'),
        'style' => 'seamless',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => ['the_content'],
        'active' => true,
        'fields' => $this->integrate_fields($i['fields'], $k),
        'show_in_graphql' => $this->arraykey_merge_default($i, 'show_in_graphql', 1),
        'graphql_field_name' => $this->arraykey_merge_default($i, 'graphql_field_name', 'acf')
      ]);

      acf_add_local_field_group($_d);
    });
  }

  function integrate_fields($fields = [], $prefix_key = ''){
    $f = [];
    
    if($fields && count($fields) > 0){
      foreach($fields as $key => $field){
        $values = [];

        foreach($field as $f_key => $field_value){
          $value = $this->get_field_value($field_value, $prefix_key);
          if(!is_null($value)){
            $values[$f_key] = $value;
          }
        }

        $f[] = array_merge(
          [
            'name' => $key,
            'show_in_graphql' => 1,
            'wpml_cf_preferences' => 2
          ],
          $values,
          $this->integrate_for_field_type($values, $key, $prefix_key),
          $this->map_sub_fields($values, $key, $prefix_key),
          [
            'key' => $this->get_field_key($key, $prefix_key)
          ]
        );
      }
    }

    return $f;
  }

  function map_sub_fields($field, $key = '', $prefix_key = ''){
    if(!$this->array_has_key('sub_fields', $field)){
      return [];
    }

    $sub_fields = $field['sub_fields'];
    return [
      'sub_fields' => $this->integrate_fields($sub_fields, $this->get_field_key($key, $prefix_key))
    ];
  }

  function integrate_flexible_layouts($layouts, $key = '', $prefix_key = ''){
    $_layouts = [];
    $layouts_key = $this->get_field_key($key, $prefix_key);

    foreach($layouts as $layout){
      if(!isset($this->blocks[$layout])){
        continue;
      }  
      
      $_layout_key = $this->get_field_key($layout, $layouts_key);
      $_layouts[$_layout_key] = $this->integrate_fields([
        $layout => array_merge(
          [
            'display' => 'block'
          ],
          $this->blocks[$layout]
        )
      ], $layouts_key)[0];
    }

    return $_layouts;
  }

  function integrate_for_field_type($field, $key = '', $prefix_key = ''){
    if(!$this->array_has_key('type', $field)){
      return [];
    }

    $field_type = $field['type'];

    if(isset($this->fields[$field_type])){
      return $this->integrate_fields([
        $key => array_merge(
          $field,
          $this->fields[$field_type]
        )
      ], $prefix_key)[0];
    } else {
      switch($field_type){
        case 'flexible_content':
          return [
            'button_label' => 'Add block',
            'layouts' => $this->integrate_flexible_layouts($field['layouts'], $key, $prefix_key)
          ];
        case 'true_false':
          return [
            'default_value' => 0
          ];
          break;
        case 'select':
          return [
            'default_value' => array_key_first($field['choices'])
          ];
        default:
          return [];
      }
    }
  }

  function get_field_value($value, $prefix_key = ''){
    if($this->field_value_should_be_integrated($value)){
      return $this->integrate_field_value($value[1], $value[2], $prefix_key);
    } else if(is_array($value) && array_keys($value) === range(0, count($value) - 1)){
      return array_map(function($item) use ($prefix_key) {
        return $this->get_field_value($item, $prefix_key);
      }, $value);
    } else {
      return $value;
    }
  }

  function field_value_should_be_integrated($value){
    return $this->array_has_key(0, $value) && $value[0] === '_cf';
  }

  function integrate_field_value($type = '', $options = [], $prefix_key = ''){
    switch($type){
      case 'constant':
        $_constant_key = $options['type'];
        if(isset($this->constants[$_constant_key])){
          return $this->constants[$_constant_key];
        } else {
          return NULL;
        }
      case 'conditional':
        if(!isset($options['key']) && !isset($options['field'])){
          return NULL;
        }

        $_prefix_key = $prefix_key;
        if(isset($options['parent'])){
          $prefix_key_parts = explode('_', $prefix_key);
          if(count($prefix_key_parts) > $options['parent']){
            $prefix_key_parts = array_slice($prefix_key_parts, 0, 0 - $options['parent']);
            $_prefix_key = implode('_', $prefix_key_parts);
          }
        }

        $_conditional_options = array_merge([
          'field' => $this->get_field_key($options['key'], $_prefix_key),
          'operator' => '==',
          'value' => '1'
        ], $options);
        
        return array_intersect_key($_conditional_options, array_flip(['field', 'operator', 'value']));
      default:
        return NULL;
    }
  }

  function get_field_key($key = '', $prefix_key = ''){
		$_key = '';
		if($prefix_key){
			$_key = $prefix_key . '_';
		}
		$_key .= $key;
		return $_key;
	}

  function array_has_key($k = 0, $a = null){
    return isset($a) && is_array($a) && array_key_exists($k, $a);
  }

  function arraykey_merge_default($array = [], $key = '', $default_value = ''){
    return $this->array_has_key($key, $array) ? $array[$key] : $default_value;
  }

  public function get_set($set){
    if(property_exists($this, $set)){
      return $this->$set;
    } else {
      return NULL;
    }
  }

}