<?php
class ModelVendorProduct extends Model {
    // updated the following query add status as 2 on 01-05-2025
	public function addProduct($data) {
	   // added changes for show username on 21-05-2025---
	    $this->event->trigger('pre.admin.product.add', $data);
		$user = $this->user->getUserName(); // To get the admin's username
		//-----------------------------------------------------------
		//updated the following query add dynamic status on the 24-05-2025
		$this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "',shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "',hsn_code = '" . $this->db->escape($data['hsn_code']) . "', gst_rate = '" . $this->db->escape($data['gst_rate']) . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = 2 , added_by = '" . $this->db->escape($user) . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), volumetric_weight = '" . (float)$data['volumetric_weight'] . "', payment_method = '" . $this->db->escape($data['payment_method']) . "', product_condition = '" . $this->db->escape($data['product_condition']) . "', refurbished_description = '" . $this->db->escape($data['refurbished_description']) . "'");

        //----------------------------------------------------------------------------

		$product_id = $this->db->getLastId();
		
		$vendor_id = 0;
        if (isset($data['vendor_id'])) {
            $vendor_id = (int)$data['vendor_id'];
        } elseif (isset($data['vendor'])) {
            $vendor_id = (int)$data['vendor'];
        }
        
// 		if (isset($this->request->post['has_warranty']) && $this->request->post['has_warranty']) {
// 		    $this->addProductWarranty($product_id, $data);
// 		}
// 		else{
// 		    $this->deleteWarranty($product_id);
// 		}
// 		if (isset($this->request->post['is_returnable']) && $this->request->post['is_returnable'] ) {
// 		$this->addProductReturnPolicy($product_id,$data);
// 		}
// 		else{
// 		    $this->deleteReturnPolicy($product_id);
// 		}
		if($data['deliveryOption']){
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_availability SET 
            product_id = '" . (int)$product_id . "', 
            delivery_type = '" . $data['deliveryOption'] . "', 
            pincodes = '" . $data['pincodeInput'] . "', 
            created_at = NOW(),
            updated_at = NOW()
        ");}
		 //--- added code related to the unique image name -- on 23-04-2025----------
                    // Set timezone to IST
            //-- added the code related to main image--------------
            date_default_timezone_set('Asia/Kolkata');
            
            if (isset($this->request->post['image']) && $this->request->post['image']) {
            	$original_image = $this->request->post['image'];
            	$ext = pathinfo($original_image, PATHINFO_EXTENSION);
            	$filename = pathinfo($original_image, PATHINFO_FILENAME);
            
            	$date = date('Y-m-d');
            	$time = date('H-i-s');
            	$random_code = mt_rand(100000, 999999); // 6-digit random
            
            	$new_filename = $filename . '_' . $date . '_' . $time . '_' . $random_code . '.' . $ext;
            	$new_path =  'catalog/product/' . $new_filename;
            
            	$full_original_path = DIR_IMAGE . $original_image;
            	$full_new_path = DIR_IMAGE . $new_path;
            
            	// Try to rename if file exists
            	if (file_exists($full_original_path)) {
            		if (!file_exists($full_new_path)) {
            			rename($full_original_path, $full_new_path);
            		}
            	}
            
            	
            	// Always assign new path
            	$data['image'] = $new_path;
            
            } elseif (!empty($product_info)) {
            	$data['image'] = $product_info['image'];
            } else {
            	$data['image'] = '';
            }
            
            // ✅ Update product image in database
            if (isset($data['image'])) {
            	$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
            }
            // ---------------------------------------------------------------------------------------------------------
            
        // 	variation start 
        if (isset($data['variant_id']) && $data['variant_id']) {  // Check if 'variant_id' is set and not empty
        
            if (isset($data['image'])) {
                if ($data['variant_id'] == 1) {
            
                    // Insert into oc_product_variants_group and capture the inserted ID
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_variants_group SET product_id = '" . (int)$product_id . "'");
                    $variant_group_id = $this->db->getLastId();
                    $this->session->data['variant_group_id'] = $variant_group_id;
                }
                if (isset($this->session->data['variant_group_id'])) {
                    if (isset($data['variant_name']) && !empty($data['variant_name'])) {
                
                    // Now insert into oc_product_variants with the group ID
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_variants SET 
                            product_id = '" . (int)$product_id . "',
                            variant_group_id = '" . (int)$this->session->data['variant_group_id'] . "',
                            variant_image = '" . $this->db->escape($data['image']) . "',
                            variant_name = '" . $this->db->escape($data['variant_name']) . "'");
                            
                    $this->session->data['variant_data'][] = [
						'variant_name' => $data['variant_name'],
						'product_id'   => (int)$product_id
					];
                    }   
                }
            }
        }
// variation ends 
		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', key_highlight = '" . $this->db->escape($value['key_highlight']) . "',tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
						}
					}
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		}
		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}

// 		if (isset($data['product_image'])) {
// 			foreach ($data['product_image'] as $product_image) {
// 				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
// 			}
// 		}

       
            //------------------------- added code related to the product images on 22-04-2025 -----------------------------------------------------------------
            // $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");

            if (isset($data['product_image']) && is_array($data['product_image'])) {
            	foreach ($data['product_image'] as $product_image) {
            		if (isset($product_image['image']) && $product_image['image']) {
            
            
            // Set timezone to IST
            date_default_timezone_set('Asia/Kolkata');
            
            $original_image = $product_image['image'];
            $ext = pathinfo($original_image, PATHINFO_EXTENSION);
            $filename = pathinfo($original_image, PATHINFO_FILENAME);
            
            // Now IST time will be used
            $date = date('Y-m-d');
            $time = date('H-i-s');
            $random_code = mt_rand(100000, 999999);
            
            $new_filename = $filename . '_' . $date . '_' . $time . '_' . $random_code . '.' . $ext;
            $new_path = 'catalog/product/' . $new_filename;
            
            if (file_exists(DIR_IMAGE . $original_image) && !file_exists(DIR_IMAGE . $new_path)) {
            	rename(DIR_IMAGE . $original_image, DIR_IMAGE . $new_path);
            }
            
            			$final_image = $new_path;
            			$sort_order = isset($product_image['sort_order']) ? (int)$product_image['sort_order'] : 0;
            
            			$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET 
            				product_id = '" . (int)$product_id . "', 
            				image = '" . $this->db->escape($final_image) . "', 
            				sort_order = '" . $sort_order . "'");
            		}
            	}
            }
            
        // -------------------------------------------------------------------------------------




		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
			}
		}

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		if (isset($data['product_filter'])) {
			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
				if ((int)$product_reward['points'] > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward['points'] . "'");
				}
			}
		}
		/* 08 02 2020 */
		if(isset($data['vendor_id'])){
		$vendor_id = $data['vendor_id'];
		} 
		/* 08 02 2020 */
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_to_product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_to_product SET product_id = '" . (int)$product_id . "', vendor_id = '" . (int)$vendor_id . "'");
		
		// SEO URL
		if (isset($data['product_seo_url'])) {
			foreach ($data['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		

		if (isset($data['product_recurrings'])) {
			foreach ($data['product_recurrings'] as $recurring) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_recurring` SET `product_id` = " . (int)$product_id . ", customer_group_id = " . (int)$recurring['customer_group_id'] . ", `recurring_id` = " . (int)$recurring['recurring_id']);
			}
		}
		
		/// Seller Signup To Mail ///
		$this->load->model('vendor/mail');
		$this->load->model('vendor/vendor');
		$sellertype = 'seller_product_add_to_mail';
		
		$mailinfo = $this->model_vendor_mail->getMailInfo($sellertype);
	
		
		
		$seller_info = $this->model_vendor_vendor->getVendor($vendor_id);
		
		/*Status Enabled*/
		if(isset($mailinfo['status'])){
			$find = array(
				'{vendorname}',
				'{productname}',
				'{model}',											
				'{emails}',											
				'{loginlink}'										
			);
			if(isset($seller_info['email'])) {
				$emails = $seller_info['email'];
			} else {
				$emails ='';
			}
			
			if(isset($seller_info['firstname'])) {
				$firstname = $seller_info['firstname'];
			} else {
				$firstname ='';
			}
			
			if(isset($seller_info['lastname'])) {
				$lastname = $seller_info['lastname'];
			} else {
				$lastname ='';
			}
			$replace = array(
				'vendorname' => $firstname.' '.$lastname ,
				'productname' =>$value['name'],
				'model' 	=> $data['model'],
				'emails' 	=> $emails,
				'loginlink' => $this->url->link('vendor/product', '', true) . "\n\n"
			);
			
			if(!empty($mailinfo['message'])){
				$subject = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['subject']))));

				$message = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['message']))));
				
				$mail = new Mail($this->config->get('config_mail_engine'));
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

				$mail->setTo($emails);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
				$mail->setSubject($subject);
				$mail->setHtml(html_entity_decode($message));
				$mail->send();
			}		
		}
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_courier_charges WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_courier_charges SET 
        product_id = '" . (int)$product_id . "', 
        local_charges = '" . $this->db->escape($data['localCharges']) . "', 
        zonal_charges = '" . $this->db->escape($data['zonalCharges']) . "', 
        national_charges = '" . $this->db->escape($data['nationalCharges']) . "', 
        courier_free_price = '" . $this->db->escape(isset($data['courier_free_price']) ? $data['courier_free_price'] : null) . "', 
        date_added = NOW()");

		$this->cache->delete('product');

		return $product_id;
	}
    // variant edit start 
    public function getProductsVariants($product_id) {
		$product_id = (int)$product_id;
	
		$variant_group_query = $this->db->query("
			SELECT variant_group_id 
			FROM " . DB_PREFIX . "product_variants 
			WHERE product_id = '" . $product_id . "' 
			LIMIT 1
		");
	
		if ($variant_group_query->num_rows) {
			$variant_group_id = (int)$variant_group_query->row['variant_group_id'];
	        $this->session->data['variant_group_id'] = $variant_group_id;
			$query = $this->db->query("
				SELECT product_id, variant_name
				FROM " . DB_PREFIX . "product_variants
				WHERE variant_group_id = '" . $variant_group_id . "'
			");
	
			return $query->rows;
		}
	
		return [];
	}
	
	public function updateProductVariantName($product_id, $variant_name) {
		$this->db->query("UPDATE " . DB_PREFIX . "product_variants SET variant_name = '" . $this->db->escape($variant_name) . "' WHERE product_id = '" . (int)$product_id . "'");
	}
	
    // variant edit end 
	public function editProduct($product_id, $data) {
	    // added changes on the 21-05-2025
	    $this->event->trigger('pre.admin.product.edit', $data);
		$user = $this->user->getUserName(); // To get the admin's username
		
		// Track old status BEFORE update on 24-05-2025
		$query = $this->db->query("SELECT status FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");
		$old_status = (int)$query->row['status'];
		
		// updated the query for edited by which user on 21-05-2025
	    // updated the following query add status as 2 on 01-05-2025
		$this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "',shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', hsn_code = '" . $this->db->escape($data['hsn_code']) . "',
          gst_rate = '" . $this->db->escape($data['gst_rate']) . "',length_class_id = '" . (int)$data['length_class_id'] . "',status = 2, edited_by = '" . $this->db->escape($user) . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW(), volumetric_weight = '" . (float)$data['volumetric_weight'] . "', payment_method = '" . $this->db->escape($data['payment_method']) . "', product_condition = '" . $this->db->escape($data['product_condition']) . "',  refurbished_description = '" . $this->db->escape($data['refurbished_description']) . "' WHERE product_id = '" . (int)$product_id . "'");
        
        
        // ✅ Track who changed the status
			$query = $this->db->query("SELECT status FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");
			
			$new_status = (int)$data['status'];

			if ($old_status !== $new_status) {
				$this->db->query("UPDATE `" . DB_PREFIX . "product` SET 
					approved_by = '" . $this->db->escape($this->user->getUserName()) . "',
					approved_date = NOW()
					WHERE product_id = '" . (int)$product_id . "'");
			}

			// Continue with other queries like description, categories, images etc.
        
        
      // warranty starts 
        // if (isset($this->request->post['has_warranty']) && $this->request->post['has_warranty']) {
        // 			Save or update warranty info
    // 			$this->addProductWarranty($product_id, $this->request->post);
    // 		} else {
        // 	Checkbox is unchecked, delete warranty
    // 			$this->deleteWarranty($product_id);
    // 		}
    // warranty ends 
    // return policy starts
        // if (isset($this->request->post['is_returnable']) && $this->request->post['is_returnable']) {
    // 			Save or update return policy info
    // 			$this->addProductReturnPolicy($product_id, $data);
    // 		} else {
    // Checkbox is unchecked, delete return policy
    // 			$this->deleteReturnPolicy($product_id, $data);
    // 		}
    // return policy ends 
        
        // local zonal national charges update 
        
        // if(isset($data['deliveryOption']))
        // {
        // $this->db->query("UPDATE " . DB_PREFIX . "product_availability SET 
        //     delivery_type = '" . $data['deliveryOption'] . "', 
        //     pincodes = '" . $data['pincodeInput'] . "', 
        //     updated_at = NOW() 
        //     WHERE product_id = '" . (int)$product_id . "'
        // ");
        // if(isset($data['localCharges']) || isset($data['zonalCharges']) || isset($data['nationalCharges']) || isset($data['courier_free_price'])){
        //     $this->db->query("UPDATE " . DB_PREFIX . "product_courier_charges SET 
        //     local_charges = '" . $this->db->escape($data['localCharges']) . "', 
        //     zonal_charges = '" . $this->db->escape($data['zonalCharges']) . "', 
        //     national_charges = '" . $this->db->escape($data['nationalCharges']) . "', 
        //     courier_free_price = '" . $this->db->escape(isset($data['courier_free_price']) ? $data['courier_free_price'] : null) . "',
        //     date_added = NOW()
        //     WHERE product_id = '" . (int)$product_id . "'
        // ");

        // }
        // }
        if (isset($data['deliveryOption'])) {
    $product_id = (int)$product_id; // Ensure it's an integer
    $deliveryType = $this->db->escape($data['deliveryOption']);
    $pincodeInput = $this->db->escape($data['pincodeInput']);

    // Check if product_id exists in product_availability
    $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "product_availability WHERE product_id = '" . $product_id . "'");
    
    if ($query->row['total'] > 0) {
        // Update product_availability
        $this->db->query("
            UPDATE " . DB_PREFIX . "product_availability SET 
                delivery_type = '" . $deliveryType . "', 
                pincodes = '" . $pincodeInput . "', 
                updated_at = NOW() 
            WHERE product_id = '" . $product_id . "'
        ");
    } else {
        // Insert into product_availability
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "product_availability 
                (product_id, delivery_type, pincodes, updated_at) 
            VALUES 
                ('" . $product_id . "', '" . $deliveryType . "', '" . $pincodeInput . "', NOW())
        ");
    }

    // Prepare courier charge values
    if (
        isset($data['localCharges']) || 
        isset($data['zonalCharges']) || 
        isset($data['nationalCharges']) || 
        isset($data['courier_free_price'])
    ) {
        $localCharges = $this->db->escape($data['localCharges'] ?? '');
        $zonalCharges = $this->db->escape($data['zonalCharges'] ?? '');
        $nationalCharges = $this->db->escape($data['nationalCharges'] ?? '');
        $courierFreePrice = isset($data['courier_free_price']) 
            ? "'" . $this->db->escape($data['courier_free_price']) . "'" 
            : "NULL";

        // Check if product_id exists in product_courier_charges
        $checkCharges = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "product_courier_charges WHERE product_id = '" . $product_id . "'");

        if ($checkCharges->row['total'] > 0) {
            // Update product_courier_charges
            $this->db->query("
                UPDATE " . DB_PREFIX . "product_courier_charges SET 
                    local_charges = '" . $localCharges . "', 
                    zonal_charges = '" . $zonalCharges . "', 
                    national_charges = '" . $nationalCharges . "', 
                    courier_free_price = " . $courierFreePrice . ", 
                    date_added = NOW() 
                WHERE product_id = '" . $product_id . "'
            ");
        } else {
            // Insert into product_courier_charges
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "product_courier_charges 
                    (product_id, local_charges, zonal_charges, national_charges, courier_free_price, date_added) 
                VALUES 
                    ('" . $product_id . "', '" . $localCharges . "', '" . $zonalCharges . "', '" . $nationalCharges . "', " . $courierFreePrice . ", NOW())
            ");
        }
    }
}

        // local zonal national charges update end
        
		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', 			key_highlight = '" . $this->db->escape($value['key_highlight']) . "',tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");

		if (!empty($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

						$product_option_id = $this->db->getLastId();
						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
						}
					}
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		}
		

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', max_quantity = '" . (int)$product_discount['max_quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_filter'])) {
			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $value) {
				if ((int)$value['points'] > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
				}
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_to_product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_to_product SET product_id = '" . (int)$product_id . "', vendor_id = '" . (int)$data['vendor_id'] . "'");
		
		// SEO URL
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
		
		if (isset($data['product_seo_url'])) {
			foreach ($data['product_seo_url']as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_recurring` WHERE product_id = " . (int)$product_id);

		if (isset($data['product_recurring'])) {
			foreach ($data['product_recurring'] as $product_recurring) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_recurring` SET `product_id` = " . (int)$product_id . ", customer_group_id = " . (int)$product_recurring['customer_group_id'] . ", `recurring_id` = " . (int)$product_recurring['recurring_id']);
			}
		}

		$this->cache->delete('product');
	}
	
	public function status($product_id){
	    
	    // Track old status BEFORE update on 24-05-2025
		$query = $this->db->query("SELECT status FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");
		$old_status = (int)$query->row['status'];
		
		$this->db->query("UPDATE " . DB_PREFIX . "product SET status = '1' WHERE product_id = '" . (int)$product_id . "'");
		
		// updated the changes on the 24-05-2025
		$query = $this->db->query("SELECT status FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");
			
			$new_status = (int)$data['status'];

			if ($old_status !== $new_status) {
				$this->db->query("UPDATE `" . DB_PREFIX . "product` SET 
					approved_by = '" . $this->db->escape($this->user->getUserName()) . "',
					approved_date = NOW()
					WHERE product_id = '" . (int)$product_id . "'");
			}

			// end here ------------------------------
			
		/// Product Approve To Mail ///
		
		$this->load->model('vendor/vendor');
		$this->load->model('vendor/product');
		
		$sellertype ='seller_product_approve_email';
			
		$mailinfo = $this->model_vendor_product->getMailInfo($sellertype);
		
		$sellerproduct_info = $this->model_vendor_product->getVendorProduct($product_id);
		
		$seller_info = $this->model_vendor_vendor->getVendor($sellerproduct_info['vendor_id']);
		/* 13-03-2019 */
		$product_info = $this->getProduct($product_id);
		/* 13-03-2019 */
		$url='';
		/*Status Enabled*/
		/* 13-03-2019 productname */
		if(isset($mailinfo['sellertype'])){
			$find = array(
				'{vendorname}',	
				'{productname}',		
				'{emails}',										
				'{loginlink}',										
													
			);
			
			/* 13-03-2019 update */
			if(isset($seller_info['firstname'])) {
				$firstname = $seller_info['firstname'];
			} else {
				$firstname='';
			}
			if(isset($seller_info['lastname'])) {
				$lastname = $seller_info['lastname'];
			} else {
				$lastname='';
			}
			
			/* 13-03-2019 update */
			if(isset($seller_info['email'])) {
				$emails = $seller_info['email'];
			} else {
				$emails='';
			}
			/* 13-03-2019 update firstname lastname */
			$replace = array(
				'vendorname' => $firstname.' '.$lastname,
				'productname'=> $product_info['name'],
				'emails'	=> $emails,
				'loginlink' => $this->url->link('vendor/product', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . (int)$product_id . $url, true)
			);
			
			
			if(!empty($mailinfo['message'])){
				$subject = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['subject']))));
			
				$message = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['message']))));
				
				$mail = new Mail($this->config->get('config_mail_engine'));
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

				$mail->setTo($emails);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
				$mail->setSubject($subject);
				$mail->setHtml(html_entity_decode($message));
				$mail->send();			
			}
					
		}
		
	}
	
	public function DisStatus($product_id){
		$this->db->query("UPDATE " . DB_PREFIX . "product SET status = '0' WHERE product_id = '" . (int)$product_id . "'");
	}
	
	public  function getMailInfo($sellertype){
		$query=$this->db->query("select * from " . DB_PREFIX . "vendor_mail vm LEFT JOIN " . DB_PREFIX . "vendor_mail_language vml on(vm.mail_id=vml.mail_id) where vm.sellertype='" .$sellertype."' and vml.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		return $query->row;
		
	}

	public function copyProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p WHERE p.product_id = '" . (int)$product_id . "'");

		if ($query->num_rows) {
			$data = $query->row;
			/* 08 02 2020 */
			
			$query1 = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "vendor_to_product  WHERE product_id = '" . (int)$product_id . "'");
			if ($query1->num_rows) {
				$data['vendor_id'] = $query1->row['vendor_id'];
			}
			
			/* 08 02 2020 */
			$data['sku'] = '';
			$data['upc'] = '';
			$data['viewed'] = '0';
			$data['keyword'] = '';
			$data['status'] = '0';
			
			

			$data['product_attribute'] = $this->getProductAttributes($product_id);
			$data['product_description'] = $this->getProductDescriptions($product_id);
			$data['product_discount'] = $this->getProductDiscounts($product_id);
			$data['product_filter'] = $this->getProductFilters($product_id);
			$data['product_image'] = $this->getProductImages($product_id);
			$data['product_option'] = $this->getProductOptions($product_id);
			$data['product_related'] = $this->getProductRelated($product_id);
			$data['product_reward'] = $this->getProductRewards($product_id);
			$data['product_special'] = $this->getProductSpecials($product_id);
			$data['product_category'] = $this->getProductCategories($product_id);
			$data['product_download'] = $this->getProductDownloads($product_id);
			$data['product_layout'] = $this->getProductLayouts($product_id);
			$data['product_store'] = $this->getProductStores($product_id);
			$data['product_recurrings'] = $this->getRecurrings($product_id);

			$this->addProduct($data);
		}
	}

	public function deleteProduct($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_to_product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_recurring WHERE product_id = " . (int)$product_id);
		$this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE product_id = '" . (int)$product_id . "'");

		$this->cache->delete('product');
	}

	public function getProduct($product_id) {
    		$query = $this->db->query("SELECT DISTINCT * 
    		    FROM " . DB_PREFIX . "product p 
    		    LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
    		    LEFT JOIN " . DB_PREFIX . "vendor_to_product v2p ON (p.product_id = v2p.product_id) 
    		    LEFT JOIN " . DB_PREFIX . "product_availability pa ON (p.product_id = pa.product_id)
                LEFT JOIN " . DB_PREFIX . "product_courier_charges pcc ON (p.product_id = pcc.product_id)
    		    WHERE p.product_id='".(int)$product_id."' 
    		      and pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
   
    // $query = $this->db->query("
    //     SELECT 
    //         DISTINCT p.*, 
    //         pd.*, 
    //         v2p.*,
    //         pa.delivery_type,
    //         pa.pincodes,
    //         pa.created_at AS availability_created,
    //         pa.updated_at AS availability_updated,
    //         pcc.local_charges,
    //         pcc.zonal_charges,
    //         pcc.national_charges,
    //         pcc.courier_free_price,
    //         pcc.date_added AS courier_date_added
    //     FROM " . DB_PREFIX . "product p
    //     LEFT JOIN " . DB_PREFIX . "product_description pd 
    //         ON (p.product_id = pd.product_id)
    //     LEFT JOIN " . DB_PREFIX . "vendor_to_product v2p 
    //         ON (p.product_id = v2p.product_id)
    //     LEFT JOIN " . DB_PREFIX . "product_availability pa 
    //         ON (p.product_id = pa.product_id)
    //     LEFT JOIN " . DB_PREFIX . "product_courier_charges pcc 
    //         ON (p.product_id = pcc.product_id)
    //     WHERE p.product_id = '" . (int)$product_id . "' 
    //       AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
    // ");

		return $query->row;
	}

	public function getProducts($data = array()) {
		/* 18 02 2020 update query */
		$sql = "SELECT *,vd.name as vendorstorename, pd.name as name FROM " . DB_PREFIX . "vendor_to_product v2p LEFT JOIN " . DB_PREFIX . "product p ON (v2p.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (v2p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v2p.vendor_id = vd.vendor_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND v2p.vendor_id<>0";
		
// 		// Restrict products to the current user (vendor) except created user for ipshopy changes on 09-06-2025
		$allowed_users = [2,34, 21,25,33,27,26];
		
		if (!empty($data['added_by']) && !in_array((int)$data['user_id'], $allowed_users)) {
			$sql .= " AND p.added_by = '" . $data['added_by'] . "'";
		}
        // -------------------------------------------------------------- 		
		
		/* 11 02 2020  */
		if (!empty($data['filter_vendor1'])){
		 	$sql .=" and v2p.vendor_id='".$this->db->escape($data['filter_vendor1'])."'";
		}
		/* 11 02 2020  */
		
		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}
		
		
		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status'])){
		 	$sql .=" and p.status like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		// for filter at 21-05-2025
		// $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE 1";

		if (!empty($data['filter_added_by'])) {
			$sql .= " AND p.added_by LIKE '" . $this->db->escape($data['filter_added_by']) . "%'";
		}

		if (!empty($data['filter_edited_by'])) {
			$sql .= " AND p.edited_by LIKE '" . $this->db->escape($data['filter_edited_by']) . "%'";
		}

		// ====----

		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order',
					
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getProductsByCategoryId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

		return $query->rows;
	}

	public function getProductDescriptions($product_id) {
		$product_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'description'      => $result['description'],
				'key_highlight'    => $result['key_highlight'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'tag'              => $result['tag']
			);
		}

		return $product_description_data;
	}

	public function getProductCategories($product_id) {
		$product_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}

	public function getProductFilters($product_id) {
		$product_filter_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_filter_data[] = $result['filter_id'];
		}

		return $product_filter_data;
	}

	public function getProductAttributes($product_id) {
		$product_attribute_data = array();

		$product_attribute_query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' GROUP BY attribute_id");

		foreach ($product_attribute_query->rows as $product_attribute) {
			$product_attribute_description_data = array();

			$product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

			foreach ($product_attribute_description_query->rows as $product_attribute_description) {
				$product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
			}

			$product_attribute_data[] = array(
				'attribute_id'                  => $product_attribute['attribute_id'],
				'product_attribute_description' => $product_attribute_description_data
			);
		}

		return $product_attribute_data;
	}

	public function getProductOptions($product_id) {
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();

			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_id = '" . (int)$product_option['product_option_id'] . "'");

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],
					'points'                  => $product_option_value['points'],
					'points_prefix'           => $product_option_value['points_prefix'],
					'weight'                  => $product_option_value['weight'],
					'weight_prefix'           => $product_option_value['weight_prefix']
				);
			}

			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => $product_option['value'],
				'required'             => $product_option['required']
			);
		}

		return $product_option_data;
	}

	public function getProductOptionValue($product_id, $product_option_value_id) {
		$query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getProductDiscounts($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY quantity, max_quantity, priority, price");

		return $query->rows;
	}
	

	public function getProductSpecials($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

		return $query->rows;
	}

	public function getProductRewards($product_id) {
		$product_reward_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
		}

		return $product_reward_data;
	}

	public function getProductDownloads($product_id) {
		$product_download_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_download_data[] = $result['download_id'];
		}

		return $product_download_data;
	}

	public function getProductStores($product_id) {
		$product_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_store_data[] = $result['store_id'];
		}

		return $product_store_data;
	}
	
	public function getProductSeoUrls($product_id) {
		$product_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $product_seo_url_data;
	}
	
	public function getProductLayouts($product_id) {
		$product_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $product_layout_data;
	}

	public function getProductRelated($product_id) {
		$product_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}

		return $product_related_data;
	}

	public function getRecurrings($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_recurring` WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}

	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "vendor_to_product v2p LEFT JOIN " . DB_PREFIX . "product p ON (v2p.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (v2p.product_id = pd.product_id)  LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v2p.vendor_id = vd.vendor_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND v2p.vendor_id<>0";
		
		// Restrict products to the current user (vendor) except created user for ipshopy changes on 09-06-2025
    	$allowed_users = [2,34, 21,25, 33,27,26];
		
		if (!empty($data['added_by']) && !in_array((int)$data['user_id'], $allowed_users)) {
			$sql .= " AND p.added_by = '" . $data['added_by'] . "'";
		}
        //-------------------------------------------------------------- 		
		
			/* 11 02 2020  */
		if (!empty($data['filter_vendor1'])){
		 	$sql .=" and v2p.vendor_id='".$this->db->escape($data['filter_vendor1'])."'";
		}
		/* 11 02 2020  */
		
		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}
		
		
		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status'])){
		 	$sql .=" and p.status like '".$this->db->escape($data['filter_status'])."%'";
		}

        	// for filter at 23-05-2025
		// $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE 1";

		if (!empty($data['filter_added_by'])) {
			$sql .= " AND p.added_by LIKE '" . $this->db->escape($data['filter_added_by']) . "%'";
		}

		if (!empty($data['filter_edited_by'])) {
			$sql .= " AND p.edited_by LIKE '" . $this->db->escape($data['filter_edited_by']) . "%'";
		}

		// ====----
		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalProductsByTaxClassId($tax_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE tax_class_id = '" . (int)$tax_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByStockStatusId($stock_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE stock_status_id = '" . (int)$stock_status_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByWeightClassId($weight_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE weight_class_id = '" . (int)$weight_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByLengthClassId($length_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE length_class_id = '" . (int)$length_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByDownloadId($download_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_download WHERE download_id = '" . (int)$download_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByManufacturerId($manufacturer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByAttributeId($attribute_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_attribute WHERE attribute_id = '" . (int)$attribute_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByOptionId($option_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_option WHERE option_id = '" . (int)$option_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByProfileId($recurring_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_recurring WHERE recurring_id = '" . (int)$recurring_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}
	
	public function getVendorProduct($product_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_to_product where product_id='". (int)$product_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	// added on 02-04-2025
	
	// nikita  product approval comment
	public function getLatestAdminComment($product_id) {
		$query = $this->db->query("SELECT comment FROM " . DB_PREFIX . "product_approval_comments WHERE product_id = '" . (int)$product_id . "' AND comment_by = 'admin' ORDER BY date_added DESC LIMIT 1");
		return ($query->num_rows > 0) ? $query->row['comment'] : '';
	}
	
	public function getCommentThread($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_approval_comments WHERE product_id = '" . (int)$product_id . "' ORDER BY date_added ASC");
		return $query->rows;
	}
	
	public function saveAdminComment($product_id, $comment) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_approval_comments SET product_id = '" . (int)$product_id . "', comment_by = 'admin', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
	}
	
	public function getAllComments($product_id) {
		$query = $this->db->query("SELECT comment_by, comment, date_added, media FROM " . DB_PREFIX . "product_approval_comments WHERE product_id = '" . (int)$product_id . "' ORDER BY date_added ASC");
	
		$comments = $query->rows;
		foreach ($comments as &$comment) {
			$comment['media'] = !empty($comment['media']) ? explode(',', $comment['media']) : [];
		}
	
		return $comments;
	}
	
	public function submitAdminComment($product_id, $comment) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_approval_comments SET product_id = '" . (int)$product_id . "', comment_by = 'admin', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
	}
	
	public function submitVendorReply($product_id, $comment, $media = '', $vendor_id = 0) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_approval_comments SET
			product_id = '" . (int)$product_id . "',
			comment_by = 'admin',
			vendor_id = '" . (int)$vendor_id . "',
			comment = '" . $this->db->escape($comment) . "',
			media = '" . $this->db->escape($media) . "',
			date_added = NOW()");
	}
	//---------------------------------------------------------------------------
// 	warranty starts

		public function getReplacementPolicy($product_id) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_replacement_policy WHERE product_id = '" . (int)$product_id . "'");
			return $query->row;
		}

		public function saveReplacementPolicy($product_id, $data) {
			$query = $this->db->query("SELECT replacement_policy_id FROM " . DB_PREFIX . "product_replacement_policy WHERE product_id = '" . (int)$product_id . "'");

			$now = date('Y-m-d H:i:s');

			if ($query->num_rows) {
				// Update existing record
				$this->db->query("UPDATE " . DB_PREFIX . "product_replacement_policy SET 
					is_replacable = '" . (int)$data['is_replacable'] . "',
					replacement_reason = '" . $this->db->escape($data['replacement_reason']) . "',
					replacement_period = '" . $this->db->escape($data['replacement_period']) . "',
					replacement_policy = '" . $this->db->escape($data['replacement_policy']) . "',
					replacement_description = '" . $this->db->escape($data['replacement_description']) . "',
					date_modified = '" . $this->db->escape($now) . "'
					WHERE product_id = '" . (int)$product_id . "'");
			} else {
				// Insert new record
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_replacement_policy SET 
					product_id = '" . (int)$product_id . "',
					is_replacable = '" . (int)$data['is_replacable'] . "',
					replacement_reason = '" . $this->db->escape($data['replacement_reason']) . "',
					replacement_period = '" . $this->db->escape($data['replacement_period']) . "',
					replacement_policy = '" . $this->db->escape($data['replacement_policy']) . "',
					replacement_description = '" . $this->db->escape($data['replacement_description']) . "',
					date_added = '" . $this->db->escape($now) . "',
					date_modified = '" . $this->db->escape($now) . "'");
			}
		}


		// Replacement Policy end 



public function getProductWarranty($product_id) {
	$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_warranty WHERE product_id = '" . (int)$product_id . "'");
	return $query->row;
}

public function saveProductWarranty($product_id, $data) {
	$now = date('Y-m-d H:i:s');

	$query = $this->db->query("SELECT warranty_id FROM " . DB_PREFIX . "product_warranty WHERE product_id = '" . (int)$product_id . "'");

	if ($query->num_rows) {
		$this->db->query("UPDATE " . DB_PREFIX . "product_warranty SET 
			is_warranty = '" . (int)$data['is_warranty'] . "',
			warranty_by = '" . $this->db->escape($data['warranty_by']) . "',
			warranty_duration = '" . $this->db->escape($data['warranty_duration']) . "',
			warranty_description = '" . $this->db->escape($data['warranty_description']) . "',
			date_modified = '" . $this->db->escape($now) . "'
			WHERE product_id = '" . (int)$product_id . "'");
	} else {
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_warranty SET 
			product_id = '" . (int)$product_id . "',
			is_warranty = '" . (int)$data['is_warranty'] . "',
			warranty_by = '" . $this->db->escape($data['warranty_by']) . "',
			warranty_duration = '" . $this->db->escape($data['warranty_duration']) . "',
			warranty_description = '" . $this->db->escape($data['warranty_description']) . "',
			date_added = '" . $this->db->escape($now) . "',
			date_modified = '" . $this->db->escape($now) . "'");
	}
}


public function getReturnPolicy($product_id) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_return_policy WHERE product_id = '" . (int)$product_id . "'");
    return $query->row;
}

public function saveReturnPolicy($product_id, $data) {
    $now = date('Y-m-d H:i:s');

    $query = $this->db->query("SELECT return_policy_id FROM " . DB_PREFIX . "product_return_policy WHERE product_id = '" . (int)$product_id . "'");

    if ($query->num_rows) {
        $this->db->query("UPDATE " . DB_PREFIX . "product_return_policy SET 
            is_returnable = '" . (int)$data['is_returnable'] . "',
            return_duration_period = '" . $this->db->escape($data['return_duration_period']) . "',
            return_policy_details = '" . $this->db->escape($data['return_policy_details']) . "',
            date_modified = '" . $this->db->escape($now) . "'
            WHERE product_id = '" . (int)$product_id . "'");
    } else {
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_return_policy SET 
            product_id = '" . (int)$product_id . "',
            is_returnable = '" . (int)$data['is_returnable'] . "',
            return_duration_period = '" . $this->db->escape($data['return_duration_period']) . "',
            return_policy_details = '" . $this->db->escape($data['return_policy_details']) . "',
            date_added = '" . $this->db->escape($now) . "',
            date_modified = '" . $this->db->escape($now) . "'");
    }
}



// return policy ends 
	
	
	public function getvendorPincode(){
		$query = $this->db->query("
        SELECT cp.state, cp.city 
        FROM " . DB_PREFIX . "vendor v 
        JOIN " . DB_PREFIX . "city_pincode cp ON cp.pincode = CAST(v.postcode AS UNSIGNED) 
        WHERE v.vendor_id = '" . (int)$this->vendor->getId() . "'
    		LIMIT 1
        ");
    
		if ($query->num_rows) {
			return [

				'state' => $query->row['state'],
				'city' => $query->row['city']
			];
		} else {
			return null;
		}
	}
	
// 	Krishna Changes - 21/05/2025
	public function getCategories() {
        // Prepare the SQL query to fetch categories from the all_category table
        $query = $this->db->query("SELECT c.category_id, c.category_name, c.parent_id
                                   FROM " . DB_PREFIX . "all_category c
                                   ORDER BY c.parent_id, c.category_name");

        // Check if there are categories
        if ($query->num_rows) {
            return $query->rows;
        } else {
            return [];
        }
    }

// 	public function getCategoriesByParentId($parent_id = 0) {
// 		$query = $this->db->query("SELECT category_id, category_name FROM " . DB_PREFIX . "all_category WHERE parent_id = '" . (int)$parent_id . "'");
// 		return $query->rows;
// 	}

	public function getCategoriesByParentId($parent_id = 0) {
		// only categories >= 1887 - 2024-06-13
		$query = $this->db->query("SELECT c.category_id, cd.name as category_name FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c.category_id >= 1887");
		return $query->rows;
	}
	
    public function addCategory($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "category SET 
                          parent_id = '" . (int)$data['parent_id'] . "', 
                          column = '" . (int)$data['column'] . "', 
                          sort_order = '" . (int)$data['sort_order'] . "', 
                          status = '" . (int)$data['status'] . "', 
                          date_modified = NOW(), 
                          date_added = NOW(),
                          vendor_id = '" . (int)$data['vendor_id'] . "'");

        $category_id = $this->db->getLastId(); 

        
        foreach ($data['category_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET 
                              category_id = '" . (int)$category_id . "', 
                              language_id = '" . (int)$language_id . "', 
                              name = '" . $this->db->escape($value['name']) . "', 
                              meta_title = '" . $this->db->escape($value['meta_title']) . "', 
                              meta_description = '" . $this->db->escape($value['meta_description']) . "', 
                              meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
        }

        
        foreach ($data['category_store'] as $store_id) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET 
                              category_id = '" . (int)$category_id . "', 
                              store_id = '" . (int)$store_id . "'");
        }

        return $category_id; 
    }
	
	public function getCategoryPath($category_id) {
		// Path ko store karne ke liye ek array
		$path = [];
		
		// Loop chalao aur path banate jao
		while ($category_id) {
			// Category ki info fetch karo (parent aur category name)
			$query = $this->db->query("SELECT category_id, category_name, parent_id FROM " . DB_PREFIX . "all_category WHERE category_id = '" . (int)$category_id . "'");
	
			if ($query->num_rows) {
				// Category ko path mein add karo
				array_unshift($path, $query->row['category_name']); // Start se add karenge
				$category_id = $query->row['parent_id']; // Parent category par chale jao
			} else {
				// Agar koi category nahi milti to loop band karo
				break;
			}
		}
		
		// Path ko string mein convert kar ke return karo
		return implode(' > ', $path); // Category ka path: Fashion > Men’s Clothing > T-shirts
	}
	
	public function getProductCategoryLevels($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_product_category WHERE product_id = '" . (int)$product_id . "'");
	
		return $query->row;
	}
	
	public function getProductCategoriesinfo($product_id) {
		$query = $this->db->query("SELECT category_level_1, category_level_2, category_level_3, category_level_4, category_level_5 
								FROM " . DB_PREFIX . "vendor_product_category 
								WHERE product_id = '" . (int)$product_id . "'");

		$categories = ['level_1' => 0, 'level_2' => 0, 'level_3' => 0, 'level_4' => 0, 'level_5' => 0];

		if ($query->num_rows) {
			$row = $query->row;
			for ($i = 1; $i <= 5; $i++) {
				if (!empty($row['category_level_' . $i])) {
					$categories['level_' . $i] = (int)$row['category_level_' . $i];
				}
			}
		}

		return $categories;
	}
// 	product faq starts
    public function getProductFaqs($product_id) {
    	$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_faq 
		WHERE product_id = '" . (int)$product_id . "' 
    		AND status = 1 
    		AND question != '' 
    		AND answer != '' 
    		AND (customer_id IS NULL OR customer_id = 0)");
			
		return $query->rows;
	}


	public function saveProductFaqs($product_id, $faqs) {
    	$this->db->query("DELETE FROM " . DB_PREFIX . "product_faq WHERE product_id = '" . (int)$product_id . "'");
    
    	foreach ($faqs as $faq) {
    		$this->db->query("INSERT INTO " . DB_PREFIX . "product_faq SET 
    			product_id = '" . (int)$product_id . "',
    			language_id = '" . (int)$faq['language_id'] . "',
    			question = '" . $this->db->escape($faq['question']) . "',
    			answer = '" . $this->db->escape($faq['answer']) . "',
    			date_added = NOW(),
    			date_modified = NOW(),
    			status = 1");
    	}
    }

// 	product faq ends 

		
	public function getVendorIdByProductId($product_id) {
		$query = $this->db->query("SELECT vendor_id FROM " . DB_PREFIX . "vendor_product_category WHERE product_id = '" . (int)$product_id . "' LIMIT 1");
		if ($query->num_rows) {
			return $query->row['vendor_id'];
		}
		return 0;
	}
	
	
    // added code changes on 11/07/2025	
	public function getVendors($data) {
		/* 09-06-2025 update query for the filemanager access based on the assign seller*/
// 		$sql = "SELECT *, CONCAT(firstname, ' ', lastname) AS vendorname , (SELECT count(*) as total FROM  " . DB_PREFIX . "vendor_to_product where vendor_id=v.vendor_id) AS totalproduct FROM " . DB_PREFIX . "vendor v where vendor_id<>0";
	    $sql = "SELECT v.*, CONCAT(v.firstname, ' ', v.lastname) AS vendorname, 
		(SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_to_product WHERE vendor_id = v.vendor_id) AS totalproduct 
		FROM " . DB_PREFIX . "vendor v WHERE v.vendor_id <> 0";
	    
	    $allowed_users = [2,34,33,36,35,32,31,30,29,28,27,26,25,24,23,22,21,20,19,18,17,16,15,37,38,39,40,41,42];
	    
	    if (!empty($data['user_id']) && !in_array((int)$data['user_id'], $allowed_users)) {
			$sql .= " AND v.vendor_id IN (
				SELECT seller_id 
				FROM " . DB_PREFIX . "assignment 
				WHERE user_id = '" . (int)$data['user_id'] . "'
				AND status = 1
			)";
		}
	    //--------------------------------------------------------------------------
		if (!empty($data['filter_firstname'])){
		 	$sql .=" and CONCAT(v.firstname, ' ', v.lastname) LIKE '%" . $this->db->escape($data['filter_firstname'])."%'";
		}
		
		if (!empty($data['filter_name'])){
		 	$sql .=" and CONCAT(v.firstname, ' ', v.lastname) LIKE '%" . $this->db->escape($data['filter_name'])."%'";
		}
		
		if (isset($data['filter_approved'])){
		 	$sql .=" and v.approved like '".$this->db->escape($data['filter_approved'])."%'";
		}
		
		/* 05 02 2020 */
		if (isset($data['filter_status'])){
		 	$sql .=" and v.status like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		
		if (!empty($data['filter_date'])){
		 	$sql .=" and v.date_added like '".$this->db->escape($data['filter_date'])."%'";
		}
		
		// Filter by email Shubham Sir - 29/05/2025
		if (!empty($data['filter_email'])) {
			$sql .= " AND v.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}
		
		$sort_data = array(
			/* 05 02 2020 */
			'vendorname',
			'date_added',
			'totalproduct',
			/* 05 02 2020 */
			'firstname',
			'lastname',
			'lastname',			
			'email'			
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY vendor_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
	
		$query = $this->db->query($sql);

		return $query->rows;
	}

    	
}
