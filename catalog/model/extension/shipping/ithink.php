<?php

class ModelExtensionShippingithink extends Model {
	
		public function getIthinkOrdersCount($last_updated) {
			
				if($last_updated != '')
					{
						$get_order_query = "SELECT count(*) as total_orders from ".DB_PREFIX."order where date_modified >= '$last_updated'";	
					}
					else
					{	
						$get_order_query = "SELECT count(*) as total_orders from ".DB_PREFIX."order";
					}

					$total_orders = 0;
				    $result_get_order_query = $this->db->query($get_order_query); 
				    $total_orders = $result_get_order_query->row['total_orders'];
					
				return $total_orders;

		}
		
		
		public function getIthinkOrders($order_id,$page,$last_updated) {
		 $db_prefix 			= DB_PREFIX;
		$order_table 		= $db_prefix."order";
		$product_table 		= $db_prefix."order_product";
		$product_details_table 		= $db_prefix."product";
		$order_total_table 		= $db_prefix."order_total";		$per_page_orders=50;
		 if($order_id != '')
					{
						$get_order_query = "SELECT * from $order_table where order_id = '$order_id'";	
					}
					else if($last_updated != '')
					{
						$limit = ' Limit 0,50';
						if($page != '' && $page > 0)
						{
							$sql_offset = ($page-1)*$per_page_orders;
							$limit = " Limit ".$sql_offset.",".$per_page_orders;
						}
						$get_order_query = "SELECT * from $order_table where date_modified >= '$last_updated' $limit";	
					}
					else
					{
						$limit = ' Limit 0,50';
						if($page != '' && $page > 0)
						{
							$sql_offset = ($page-1)*$per_page_orders;
							$limit = " Limit ".$sql_offset.",".$per_page_orders;
						}
						$get_order_query = "SELECT * from $order_table $limit";
					}
				    //print_r($get_order_query);
				    $all_order_data_array = array();
				    $all_order_id = '';
				    $all_order_id_array = array();
					$result_get_order_query = $this->db->query($get_order_query); 
					
					 foreach($result_get_order_query->rows as $row_get_order_query)
				    {
				        $all_order_data_array[$row_get_order_query['order_id']] = $row_get_order_query;
				        $order_id = $row_get_order_query['order_id'];
				        if(!in_array($order_id,$all_order_id_array))
				        {
				        	$all_order_id_array[] = $order_id;
				        	$all_order_id .= $order_id.",";
				        }
				    }
					
					
				    if(strlen($all_order_id) > 1)
				    {
				    	$all_order_id = substr($all_order_id,0,-1);

                        //get order total
                        

				    	$all_order_total_data_array = array();
				    	$get_order_total_query = "SELECT * from $order_total_table  where order_id IN ($all_order_id)";
				    	
					     $result_get_order_total_query = $this->db->query($get_order_total_query); 
						 
						 foreach($result_get_order_total_query->rows as $row_get_order_query)
						{
						  $all_order_total_data_array[$row_get_order_query['order_id']][] = $row_get_order_query;
					    }
						
                        $all_order_product_data_array = $all_product_id = array();
				    	$get_order_product_query = "SELECT * from $product_table  where order_id IN ($all_order_id)";
				    	
						$result_get_order_product_query = $this->db->query($get_order_product_query); 
				    	
					     foreach($result_get_order_product_query->rows as $row_get_order_product)
					    {
					        $all_order_product_data_array[$row_get_order_product['order_id']][] = $row_get_order_product;
					        $all_product_id[] = $row_get_order_product['product_id'];
					    } 
						
						
                        $all_product_id_list = implode(',',$all_product_id);
                        $all_detail_product_data_array = array();
                        $get_detail_products_query = "SELECT * from $product_details_table  where product_id IN ($all_product_id_list)";
    				   
						$result_get_detail_products_query = $this->db->query($get_detail_products_query); 
    				    	
					    foreach($result_get_detail_products_query->rows as $row_get_detail_products)
					    {
				            $all_detail_product_data_array[$row_get_detail_products['product_id']] = $row_get_detail_products;
					    }
						
					    foreach($all_order_product_data_array as $order_id => $product_value)
					    {
					        foreach($product_value as $key => $value)
					        {
					            $product_id = $value['product_id'];
					            $all_order_new_product_data_array[$order_id][$key] = $value;
					            $all_order_new_product_data_array[$order_id][$key]['product_details'] = $all_detail_product_data_array[$product_id];
					        }
					    }
                
					    foreach($all_order_data_array as $all_order_data)
					    {
					    	$all_order_data_array[$all_order_data['order_id']]['products'] = $all_order_new_product_data_array[$all_order_data['order_id']];
					    	$all_order_data_array[$all_order_data['order_id']]['total_charges'] = $all_order_total_data_array[$all_order_data['order_id']];
					    }
				    }
					
					return $all_order_data_array;

				    // new code start 30 june 2020.
					   
		
		}
		
		public function getIthinkOrdersExtraInfo() {
			
			 $weight_dimension_array                         = array();
	                    $weight_dimension_array['itl_version']          = '2.0';
	                    
	                    $get_weight_query             					= "SELECT * from ".DB_PREFIX."weight_class_description";  
	                    
						 $result_get_weight_query 						= $this->db->query($get_weight_query); 
    				    foreach ($result_get_weight_query->rows as $row_get_weight_query)
					    {
				            $weight_dimension_array['weight_unit'][$row_get_weight_query['weight_class_id']][] 	= $row_get_weight_query;
					    }
						
						
					    $get_dimension_query             				= "SELECT * from oc_length_class_description";  
	                   
						$result_get_dimension_query 					= $this->db->query($get_dimension_query); 
    				    foreach ($result_get_dimension_query->rows as $row_get_dimension_query)
					    {
				            $weight_dimension_array['dimension_unit'][$row_get_dimension_query['length_class_id']][] 	= $row_get_dimension_query;
					    }  
						
						
						
						return $weight_dimension_array;
					
		}
	
}