<?php

namespace mergado;

class Helper {

    public static function getOptionData($product_id, $name, $options) {

      $output_array = array();
  
      if(!empty($options)){
        $input_array = array();
        $input_array[] = array("id"=>$product_id, "name"=>$name, "delta_price"=>0, "delta_weight"=>0);
      
        $output_array = $input_array;
  
        foreach ($input_array as $input_array_member) {
          foreach ($options as $product_option_value_iterator) {

            if((int)$product_option_value_iterator['product_option_value_id'] == 0) { continue; }

            $temp_id = $input_array_member['id'] . '-' . $product_option_value_iterator['product_option_id'] . '_' . $product_option_value_iterator['product_option_value_id'];
            $temp_name = $input_array_member['name'] . ' ' . $product_option_value_iterator['value'];
  
                
            if($product_option_value_iterator['price_prefix'] == "+") {
              $temp_price = $input_array_member['delta_price'] + $product_option_value_iterator['price'];
            } else {
              $temp_price = $input_array_member['delta_price'] - $product_option_value_iterator['price'];
            }
  
            if($product_option_value_iterator['weight_prefix'] == "+") {
              $temp_weight = $input_array_member['delta_weight'] + $product_option_value_iterator['weight'];
            } else {
              $temp_weight = $input_array_member['delta_weight'] - $product_option_value_iterator['weight'];
            }
  
            $output_array = $input_array_member = array("id"=>$temp_id, "name"=>$temp_name, "delta_price"=>$temp_price, "delta_weight"=>$temp_weight);
          }
        }

      } else {
        $output_array[] = array("id"=>$product_id, "name"=>$name, "delta_price"=>0, "delta_weight"=>0);
      }
      
      return $output_array;
    }

    public static function generateOptionsData($product_id, $name, $options) {
      $output_array = array();
  
      if(!empty($options)){
        $input_array = array();
        $input_array[] = array("id"=>$product_id, "name"=>$name, "delta_price"=>0, "delta_weight"=>0, 'property' => array(), 'property_value' => array());
      
        foreach ($options as $option) {
          if(!empty($option['product_option_value'])){
            $output_array = array();
  
            //add product without options, if option isn't required
            if(isset($option['required']) && $option['required'] == 0) {
              $output_array[] = array("id"=>$product_id, "name"=>$name, "delta_price"=>0, "delta_weight"=>0, 'property' => array(), 'property_value' => array());
            }
            foreach ($input_array as $input_array_member) {
              foreach ($option['product_option_value'] as $product_option_value_iterator) {
                $temp_id = $input_array_member['id'] . '-' . $option['product_option_id'] . '_' . $product_option_value_iterator['product_option_value_id'];
                $temp_name = $input_array_member['name'] . ' ' . $product_option_value_iterator['name'];
  
                $properties = $input_array_member['property'];
                $properties[] = $option['name'];
                $properties_value = $input_array_member['property_value'];
                $properties_value[] = $product_option_value_iterator['name'];
                
                if($product_option_value_iterator['price_prefix'] == "+") {
                  $temp_price = $input_array_member['delta_price'] + $product_option_value_iterator['price'];
                } else {
                  $temp_price = $input_array_member['delta_price'] - $product_option_value_iterator['price'];
                }
  
                if($product_option_value_iterator['weight_prefix'] == "+") {
                  $temp_weight = $input_array_member['delta_weight'] + $product_option_value_iterator['weight'];
                } else {
                  $temp_weight = $input_array_member['delta_weight'] - $product_option_value_iterator['weight'];
                }
  
                array_push($output_array, array("id"=>$temp_id, "name"=>$temp_name, "delta_price"=>$temp_price, "delta_weight"=>$temp_weight, 'property' => $properties, 'property_value' => $properties_value));
              }
            }
            $input_array = $output_array;
          }
        }
        if(empty($output_array)){
          // in case there are no options just copy data to output_array so that we can iterate just through that and keep the code common
          $output_array[] = array("id"=>$product_id, "name"=>$name, "delta_price"=>0, "delta_weight"=>0, 'property' => array(), 'property_value' => array());
        }
      } else {
        // in case there are no options just copy data to output_array so that we can iterate just through that and keep the code common
        $output_array[] = array("id"=>$product_id, "name"=>$name, "delta_price"=>0, "delta_weight"=>0, 'property' => array(), 'property_value' => array());
      }
      
      return $output_array;
    }


    public static function getAllProductOptions($product_id, $name, $options) {
       $all_product_variants = self::generateOptionsData($product_id, $name, $options);
       $ids = array();
       $names = array();
       $delta_price = array();
       foreach($all_product_variants as $variant) {
          $ids[] = $variant['id'];
          $names[] = self::formatText($variant['name']);
          $delta_price[] = $variant['delta_price'];
       }

       return array(
         'ids' => $ids,
         'names' => $names,
         'delta_price' => $delta_price
       );
    }

    public static function transformPostDataToOptions($post_data, $product_options) {
      $output_data = array();
      foreach($post_data as $o_key => $o) {
        foreach($product_options as $po) {
          $option_data_value = array();
          if($po['product_option_id'] == $o_key && $po['required'] == 1 && in_array($po['type'], array('radio', 'checkbox', 'select'))) {
            if(!empty($po['product_option_value'])) {
              foreach($po['product_option_value'] as $po_v_key => $po_v) {
                if(!is_array($o) && $po_v['product_option_value_id'] == $o ) { //radio, select
                  $output_data[] = self::transformOption($po, $po_v);
                } elseif( is_array($o)) { //checkbox
                  foreach($o as $o_v) {
                    if($po_v['product_option_value_id'] == $o_v) {
                      $output_data[] = self::transformOption($po, $po_v);
                    }
                  }
                }
              }
            }
          }
        }
      }
      return $output_data;
    }

    private static function transformOption($product_option, $product_option_value) {
      $data = array(
        'product_option_id' => $product_option['product_option_id'],
        'product_option_value_id' => $product_option_value['product_option_value_id'],
        'option_id' => $product_option['option_id'],
        'option_value_id' => $product_option_value['option_value_id'],
        'name' => $product_option['name'],
        'value' => $product_option_value['name'],
        'type' => $product_option['type'],
        'quantity' => $product_option_value['quantity'],
        'subtract' => $product_option_value['subtract'],
        'price' => $product_option_value['price'],
        'price_prefix' => $product_option_value['price_prefix'],
        'weight' => $product_option_value['weight'],
        'weight_prefix' => $product_option_value['weight_prefix'] 
      );

      if(isset($product_option_value['points'])) {
        $data['points'] = $product_option_value['points'];
        $data['points_prefix'] = $product_option_value['points_prefix'];
      }

      return $data;
    }

    public static function formatText($str) {
      return addslashes($str);
    }

    public static function implodeWithQuotes($array, $delimiter = ',') {
      $result = implode("'{$delimiter}'", $array);
      return "'".$result."'";
    }

    public static function urlExists($url) {

      if($url == NULL) { return false; } 
        
      $ch = curl_init($url);  
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);  
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);  
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
      
      $data = curl_exec($ch);  
      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
      curl_close($ch);  
        
      if($httpcode >= 200 && $httpcode < 300){  
        return true;  
      } else {  
        return false;  
      }
    }

}
