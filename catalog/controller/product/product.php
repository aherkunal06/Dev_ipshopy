<?php
class ControllerProductProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('product/product');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		
		if (!isset($this->request->get['_route_'])) {
            $this->response->redirect($this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id'], true), 301);
        }


		$this->load->model('catalog/category');
		if (isset($this->request->get['path'])) {
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path)
					);
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$url = '';

				if (isset($this->request->get['sort'])) {
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if (isset($this->request->get['order'])) {
					$url .= '&order=' . $this->request->get['order'];
				}

				if (isset($this->request->get['page'])) {
					$url .= '&page=' . $this->request->get['page'];
				}

				if (isset($this->request->get['limit'])) {
					$url .= '&limit=' . $this->request->get['limit'];
				}

				$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
				);
			}
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_brand'),
				'href' => $this->url->link('product/manufacturer')
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$data['breadcrumbs'][] = array(
					'text' => $manufacturer_info['name'],
					'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
				);
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', $url)
			);
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');
		
		
        // 		product size start 
        
        $product_sizes = $this->model_catalog_product->getProductSizes($product_id);
        
        // Map child_option_name for display
        foreach ($product_sizes as &$size) {
            if (!empty($size['child_option_id'])) {
                $option_values = $this->model_catalog_product->getOptionValuesByOptionId($size['parent_option_id']);
                foreach ($option_values as $option_value) {
                    if ($option_value['option_value_id'] == $size['child_option_id']) {
                        $size['child_option_name'] = $option_value['name'];
                        break;
                    }
                }
            }
        }
        
        $data['product_sizes'] = $product_sizes;
        $data['product_type'] = $product_sizes[0]['product_type'] ?? '';
        // 		product size end 

		$product_info = $this->model_catalog_product->getProduct($product_id);

		
		// key highlight of the product 
		$product_description = $this->model_catalog_product->getProductDescription($product_id);
        $data['key_highlight'] = $product_description['key_highlight'] ?? '';
		// faqs on the product page ---
		$data['product_faqs'] = $this->model_catalog_product->getProductFaqs($product_id);
        // 		warranty return replacement start 
		
		$data['replacement_policy'] = $this->model_catalog_product->getReplacementPolicyByProductId($product_id);
		// $data['replacement_policy'] = $replacement_policy;
		
		$data['product_warranty'] = $this->model_catalog_product->getWarrantyByProductId($product_id);
		$data['product_return'] = $this->model_catalog_product->getReturnPolicyByProductId($product_id);
        // 	product warranty and return policy end
        
		// Get product links grouped by group name
		$data['product_links'] = $this->model_catalog_product->getProductLinks($product_id);
		
		if ($product_info) {


                //  -------------- start ipshopy_assured logo--------------  
    $vendor_id = $this->model_catalog_product->getVendorIdByProductId($product_id);

    echo "<pre>";
	print_r("vendor id is ---------".$vendor_id);
	echo "</pre>";
    $allowed_vendor_ids = [16, 17, 23, 1562];

    
    $data['ipshopy_assured'] = in_array($vendor_id, $allowed_vendor_ids);

	 //  -------------- end ipshopy_assured logo--------------  

			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $product_info['name'],
				// added on 10-06-2025 for canonical 
				// 'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
				'href' => $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id'], true)
			);

			$this->document->setTitle($product_info['meta_title']);
			$this->document->setDescription($product_info['meta_description']);
			$this->document->setKeywords($product_info['meta_keyword']);
			
			// added on 10-06-2025 for canonical 
            // $this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');
            // Force clean URL by removing query parameters for canonical
            $this->document->addLink($this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id'], true), 'canonical');
			$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

			$data['heading_title'] = $product_info['name'];

			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

			$this->load->model('catalog/review');

			$data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

			$data['product_id'] = (int)$this->request->get['product_id'];
			$data['manufacturer'] = $product_info['manufacturer'];
			$data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
			$data['model'] = $product_info['model'];
			$data['reward'] = $product_info['reward'];
			$data['points'] = $product_info['points'];
// 			$data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');
			
			$raw_description = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');
            $data['description'] = $this->fixBrokenHtmlTags($raw_description);


    
// $data['product_info']=$product_info;
// var_dump('product_info',$product_info);
// 			    $data['stockStatus']=true;
// 			if ($product_info['quantity'] <= 0) {
// 			    $data['stockStatus']=false;
// 				$data['stock'] = $product_info['stock_status'];

// 			} elseif ($this->config->get('config_stock_display')) {
// 				$data['stock'] = $product_info['quantity'];
// 			} else {
// 				$data['stock'] = $this->language->get('text_instock');
// 			}
  if($product_info['status']=='1' || $product_info['status']==1){
    
    			 $data['stockStatus']=true;
    			    
    			if ($product_info['quantity'] <= 0) {
    			    $data['stockStatus']=false;
    				$data['stock'] = $product_info['stock_status'];
    
    			} elseif ($this->config->get('config_stock_display')) {
    				$data['stock'] = $product_info['quantity'];
    			} else {
    				$data['stock'] = $this->language->get('text_instock');
    			}
            }else{
                $data['notAvailable']='Currently unavailable';
            }


			$this->load->model('tool/image');

                $data['pant_chart']  = $this->model_tool_image->resize('catalog/banners/pant_chart1.jpg', 200, 300);
                $data['shirt_chart'] = $this->model_tool_image->resize('catalog/banners/shirt_chart1.jpg',200,300);

			if ($product_info['image']) {
			    
				$data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
				// $data['popup'] = $this->model_tool_image->resize($product_info['image'], 1600,1600);
			} else {
				$data['popup'] = '';
			}

			if ($product_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
				// $data['thumb'] = $this->model_tool_image->resize($product_info['image'], 1600,1600);
				
			} else {
				$data['thumb'] = '';
			}

			$data['images'] = array();

			$results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);

			foreach ($results as $result) {
				$data['images'][] = array(
				// 	'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
					'popup' => $this->model_tool_image->resize($result['image'], 1600,1600),
					'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
				);
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				// $data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                   $data['price'] = $this->tax->calculate( $product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
			} else {
				$data['price'] = false;
			}

			if ((int)$product_info['special']) {
				// $data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				   $data['special'] = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));

			} else {
				$data['special'] = false;
			}
			
				// added by sagar - for discount price

			if ((int)$product_info['special']) {
				$discount_percentage = round((($product_info['price'] - $product_info['special']) / $product_info['price']) * 100);
				$data['discount_percentage'] = $discount_percentage;
			} else {
				$data['discount_percentage'] = false;
			}
			// -----------------------------------------



			if ($this->config->get('config_tax')) {
				// $data['tax'] = $this->currency->format((int)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
				   $data['tax'] = (int)$product_info['special'] ? $product_info['special'] : $product_info['price'];

			} else {
				$data['tax'] = false;
			}

			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

			$data['discounts'] = array();

			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'max_quantity' => $discount['max_quantity'],
					'price'    => $this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax'))
				// 	'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}

			$data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (int)$option_value['price']) {
							$price = $this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false);
				// 			$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);
			}

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			$data['review_status'] = $this->config->get('config_review_status');

			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$data['review_guest'] = true;
			} else {
				$data['review_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}
        if ($this->customer->isLogged() && isset($this->request->get['product_id'])) {
            $this->load->model('catalog/review');
            $existing_review = $this->model_catalog_review->getReviewByCustomerAndProduct(
                $this->customer->getId(),
                (int)$this->request->get['product_id']
            );
            $data['has_existing_review'] = $existing_review ? true : false;
        } else {
            $data['has_existing_review'] = false;
        }
                // review start 
                

if ($this->customer->isLogged()) {
    $data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();

    // ðŸ”¥ Fetch existing review for this customer + product
    $this->load->model('catalog/review');
    $existing_review = $this->model_catalog_review->getReviewByCustomerAndProduct(
        $this->customer->getId(),
        (int)$this->request->get['product_id']
    );

    if ($existing_review) {
        // âœ… Set existing review text
        $data['customer_review_text'] = $existing_review['text'];
        // âœ… Set existing rating
        $data['customer_review_rating'] = $existing_review['rating'];

        // âœ… Prepare existing images (with thumbnail URL + filename)
        $existing_images = [];
        $this->load->model('tool/image');
        for ($i = 1; $i <= 5; $i++) {
            $image_column = 'image' . $i;
            if (!empty($existing_review[$image_column])) {
                $filename = $existing_review[$image_column];
                $thumb_url = $this->model_tool_image->resize('catalog/review/' . $filename, 90, 90);
                $existing_images[] = [
                    'url' => $thumb_url,
                    'filename' => $filename
                ];
            }
        }
        $data['existing_review_images'] = $existing_images;
    } else {
        // No existing review -> empty data
        $data['customer_review_text'] = '';
        $data['customer_review_rating'] = '';
        $data['existing_review_images'] = [];
    }

} else {
    // Guest -> empty data
    $data['customer_name'] = '';
    $data['customer_review_text'] = '';
    $data['customer_review_rating'] = '';
    $data['existing_review_images'] = [];
}



                // review end 

			$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
			$data['rating'] = (int)$product_info['rating'];

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);

			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);
			
            // --- added code changes for the pincode servicebility on 04-06-2025-------------------------------------------
			// Get customer addresses for pincode checker
		$data['addresses'] = array();
        $data['default_address'] = array();
        $data['default_postcode'] = '';
        $data['is_logged'] = $this->customer->isLogged();
        $data['profile'] = false;
        $data['add_address'] = $this->url->link('account/address/add', '', true);

        if ($data['is_logged']) {
            $customer_id = (int)$this->customer->getId();
$data['customer_id']=$customer_id;
        $data['profile'] = true;
        
        // Load required models
        $this->load->model('account/address');
        $this->load->model('account/customer');
        
        // Get all customer addresses
        $data['addresses'] = $this->model_account_address->getAddresses();
        
        // Get the default address ID for the customer
        $default_address_id = $this->customer->getAddressId();
        $customerPincode = '';
    
        // Get customer default address
        $customer_info = $this->model_account_customer->getCustomer($customer_id);
    
        // Check if the default address has a postcode
        foreach ($data['addresses'] as $address) {
            if ($address['address_id'] == $default_address_id && !empty($address['postcode'])) {
                $customerPincode = $address['postcode'];
                break;
            }
        }
    
        // If no default address with pincode, use the first address with a postcode
        if (empty($customerPincode)) {
            foreach ($data['addresses'] as $address) {
                if (!empty($address['postcode'])) {
                    $customerPincode = $address['postcode'];
                    break;
                }
            }
        }

        // If no pincode is found, fall back to a model method (getCustomerPostcode)
        if (empty($customerPincode)) {
            $customerPincode = $this->model_catalog_product->getCustomerPostcode($customer_id);
        }
    
        // Set the pincode for the drop-down UI
        if ($customerPincode) {
            $data['drop_pincode'] = $customerPincode;
        }
    
        // Handle the default address and pincode display
        if (!empty($customer_info) && !empty($customer_info['address_id'])) {
            $default_address = $this->model_account_address->getAddress($customer_info['address_id']);
    
            if (!empty($default_address)) {
                // Convert postcode to pincode for consistency in the template
                $default_address['pincode'] = $default_address['postcode'];
                $data['default_address'] = $default_address;
                $data['default_postcode'] = $default_address['postcode'];
    
                // Debug logs to confirm data is being set correctly
                $this->log->write('Default address set: ' . json_encode($data['default_address']));
            }
        }

        // Load recent pincode history from session
        $data['pincode_history'] = array();
        if (isset($this->session->data['pincode_history']) && is_array($this->session->data['pincode_history'])) {
            $data['pincode_history'] = $this->session->data['pincode_history'];
        }
     }

        // Add further logic if needed to handle displaying pincode history or updates

            // -------------------------------------------end here-------------------------------------------------------------------

			$data['products'] = array();

			$results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				// 	$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$price = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));
				} else {
					$price = false;
				}

				if ((int)$result['special']) {
				// 	$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$special = $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'));

				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
				// 	$tax = $this->currency->format((int)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
					$tax = (int)$result['special'] ? $result['special'] : $result['price'];
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'], true)
				);
			}

			$data['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . trim($tag))
					);
				}
			}

			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

        //faq starts
        			$data['url_all_faqs'] = $this->url->link('product/product/faq', 'product_id=' . $product_id);
        			$data['text_login2'] = sprintf($this->language->get('text_login2'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));
        //faq ends

    	// check pincode availablity
    
			$this->load->model('account/customer');
			// $customer_id = $this->model_account_customer->customer_id();
			if ((int)$this->customer->getId()) {

				$customerPincode = $this->model_catalog_product->getCustomerPostcode((int)$this->customer->getId());
				$courierResult = $this->model_catalog_product->getCourierCharges($product_id, $customerPincode);
				// $delivery_type = $courierResult['delivery_type'];
				// $courier_charge = 
				$data['courierCharges'] = $courierResult['courier_charge'];
				$data['freeCharges'] = $courierResult['freeCharges'];
				$data['customerPincode'] = $customerPincode;
				$data['customerLogin'] = true;
			} else {
				$data['customerLogin'] = false;
			}
				$ProductFreeOn = $this->model_catalog_product->getProductFree($product_id);
			$data['freeCharges'] = intval($ProductFreeOn);
			
            // 			var_dump($data);
            // 			var_dump('customer login:-',$data['customerLogin'],'---customer_id: ',(int)$this->customer->getId(),'---courierCharges--',	$data['courierCharges']);
            // end
            if ($product_info) {
				// Load variant data
				// $data['product_variants'] = $this->model_catalog_product->getProductsVariants($product_id);

                    $resized_images = [];
                    $this->load->model('tool/image');
                foreach ($data['product_variants'] as &$variant) {
                    // $images = explode(',', $variant['variant_image']);
            
                //   var_dump($variant['variant_image']);
                    // $variant['image'] = $this->model_tool_image->resize($variant['variant_image'], 60, 60);
                       $variant['image'] = $this->model_tool_image->resize($variant['variant_image'],60,60);
         			// var_dump( $variant['variant_image']); 
         			
                }
                    $data['variant_images'] = $resized_images;
			}
// 		referral and first time discount start
			if ($this->customer->isLogged()) {
			
			$first_time_offer = $this->model_catalog_product->getActiveFirstTimeOffer($this->customer->getId());
			$data['first_time_offer'] = $first_time_offer;
		} else {
			$data['first_time_offer'] = false;
		}

		//  Combined offer fetch and courier_charge/freeCharges initialization with status check
// 		$this->setActiveOfferAndCourierData($data);

		// Call the function before rendering the view
		$this->setActiveOfferAndCourierData($data);

		// Referral code tracking
		if (isset($this->request->get['ref'])) {
			$referral_code = $this->request->get['ref'];
			$this->session->data['referral_code'] = $referral_code;
			// Increment visit count for this referral code
			$this->load->model('ipoffer/offer');
			$this->model_ipoffer_offer->incrementReferralVisit($referral_code);
		}
// 		referral and first time discount end 

// product categories start 
			$categories = $this->model_catalog_product->getProductCategoriesWithLevel($product_id);
        
$data['category_id'] = isset($categories[0]['category_id']) ? $categories[0]['category_id'] : null;


// product categories end



// variant testing start 
          $customer_ids = (int)$this->customer->getId()?(int)$this->customer->getId():0;
//  if ($customer_ids==3241 ){
        // Load model
$this->load->model('catalog/variant');

$current_product_id = (int)$this->request->get['product_id'];
$variant_payload = [
  'current_product_id' => $current_product_id,
  'variants' => []
];

// 1) Get this product's variant row to read the group id
$currentRow = $this->model_catalog_variant->getVariantRowByProductId(isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] :20292);

if ($currentRow && !empty($currentRow['variant_group_id'])) {
  $group_id = (int)$currentRow['variant_group_id'];

  // 2) Load all variants in the same group
  $rows = $this->model_catalog_variant->getVariantsByGroupId($group_id);

  foreach ($rows as $r) {
    $variant_payload['variants'][] = [
      'product_id' => (int)$r['product_id'],
      'url'        => $this->url->link('product/product', 'product_id=' . (int)$r['product_id']),
      'color'      => $r['variant_name'] ?: null,   // treat variant_name as Color
      'size'       => $r['size_value']   ?: null,   // Size
      'image'      => $r['variant_image'] ? $this->model_tool_image->resize($r['variant_image'], 80, 80) : null,  // optional swatch
      'in_stock'   => isset($r['quantity']) ? ((int)$r['quantity'] > 0) : true,
      'status'     => isset($r['status']) ? (bool)$r['status'] : true,
    ];
  }
}

$data['variant_payload'] = $variant_payload;
//  }
// variant testing end

			$this->response->setOutput($this->load->view('product/product', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
		
        // 		to display seller name on there product page 03-05-2025
        		$product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;
        		$vendors = $this->model_catalog_product->getVendorInfoByProductId($product_id);
                    

            $vendor_id = $this->model_catalog_product->getVendorIdByProductId($product['product_id']);

// या vendor IDs साठीच logo दाखवायचा
$allowed_vendor_ids = [16, 17, 23, 1562];

// flag सेट करा
$product['ipshopy_assured'] = in_array($vendor_id, $allowed_vendor_ids);


				
		// 		echo "<pre>";
		//    print_r($product_id);
		//    echo "</pre>";
        		$data['vendor_info_list'] = $vendors;
        		
        // 		$data['currency'] = $this->session->data['currency'];
		
        // for product share on social media  added on 17-04-2025 by sagar
		$data['product_url'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);
// 		$data['custom_breadcrumb'] = $this->load->controller('product/breadcrumbpath');
// After resolving $category_id ...

$data['custom_breadcrumb'] = $this->load->controller('product/breadcrumbpath', [
    'category_id'   => $data['category_id'],
    'product_name'  => isset($product_info['name']) ? $product_info['name'] : null
]);


        $this->response->setOutput($this->load->view('product/product', $data));
        // end here 
    	}

        
         public function review() {
		$this->load->language('product/product');

		$this->load->model('catalog/review');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['reviews'] = array();

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {

			    $images = [];

        for ($i = 1; $i <= 5; $i++) {
            if (!empty($result['image' . $i])) {
                $images[] = 'image/catalog/review/' . $result['image' . $i];
            }
        }

			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => $result['text'],
				'rating'     => (int)$result['rating'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				    // 'images'     => isset($result['images']) ? $result['images'] : []
            'images'     => $images // Now correctly populated
				
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

		$this->response->setOutput($this->load->view('product/review', $data));
	    }   


  
        
public function write() {
    $this->load->language('product/product');
    $json = [];

    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
        // âœ… Validate name
        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
            $json['error'] = $this->language->get('error_name');
        }

        // âœ… Validate review text
        if ((utf8_strlen($this->request->post['text']) < 3) || (utf8_strlen($this->request->post['text']) > 1000)) {
            $json['error'] = $this->language->get('error_text');
        }

        // âœ… Check for words exceeding 20 characters
        if (preg_match('/\b\w{21,}\b/u', $this->request->post['text'])) {
            $json['error'] = 'Invalid message: Words cannot exceed 20 characters.';
        }

        // âœ… Validate rating
        if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
            $json['error'] = $this->language->get('error_rating');
        }

        $this->load->model('catalog/review');
        $uploaded_images = [];

        // âœ… Upload new images
        if (isset($this->request->files['review_images'])) {
            $review_image_dir = DIR_IMAGE . 'catalog/review/';
            if (!is_dir($review_image_dir)) {
                mkdir($review_image_dir, 0755, true);
            }

            foreach ($this->request->files['review_images']['name'] as $key => $original_name) {
                if (!empty($original_name)) {
                    $file = [
                        'name'     => $this->request->files['review_images']['name'][$key],
                        'type'     => $this->request->files['review_images']['type'][$key],
                        'tmp_name' => $this->request->files['review_images']['tmp_name'][$key],
                        'error'    => $this->request->files['review_images']['error'][$key],
                        'size'     => $this->request->files['review_images']['size'][$key]
                    ];

                    $clean_name = basename($file['name']);
                    $random_code = bin2hex(random_bytes(4));
                    $new_name = 'review_' . $random_code . '_' . $key . '_' . $clean_name;
                    $target_path = $review_image_dir . $new_name;

                    if (move_uploaded_file($file['tmp_name'], $target_path)) {
                        $uploaded_images[] = $new_name;
                    }
                }
            }
        }

        // âœ… Read kept existing images sent from hidden fields
        $kept_existing_images = [];
        if (isset($this->request->post['existing_images']) && is_array($this->request->post['existing_images'])) {
            foreach ($this->request->post['existing_images'] as $filename) {
                $kept_existing_images[] = basename($filename);
            }
        }

        // âœ… Read deleted images from deleted_review_images[]
        $deleted_images = [];
        if (isset($this->request->post['deleted_review_images']) && is_array($this->request->post['deleted_review_images'])) {
            foreach ($this->request->post['deleted_review_images'] as $filename) {
                $deleted_images[] = basename($filename);
            }
        }

        // âœ… Remove deleted images from kept_existing_images
        if (!empty($deleted_images)) {
            $kept_existing_images = array_diff($kept_existing_images, $deleted_images);
        }

        // âœ… Combine kept existing + new uploads
        $final_images = array_merge($kept_existing_images, $uploaded_images);

        // âœ… Validate combined images count
        // if (!isset($json['error'])) {
        //     if (count($final_images) < 2) {
        //         $json['error'] = 'You must have at least 3 images total (existing + new).';
        //     } elseif (count($final_images) > 5) {
        //         $json['error'] = 'You can upload a maximum of 5 images.';
        //     }
        // }



		if (!isset($json['error']) && count($final_images) > 5) {
    $json['error'] = 'You can upload a maximum of 5 images.';
}



        // âœ… Captcha validation
        if (!isset($json['error']) &&
            $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') &&
            in_array('review', (array)$this->config->get('config_captcha_page'))) {

            $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');
            if ($captcha) {
                $json['error'] = $captcha;
            }
        }

        if (!isset($json['error'])) {
            $product_id = (int)$this->request->get['product_id'];

            // âœ… Update or insert review
            if ($this->customer->isLogged()) {
                $customer_id = $this->customer->getId();
                $existing_review = $this->model_catalog_review->getReviewByCustomerAndProduct($customer_id, $product_id);

                if ($existing_review) {
                    $this->model_catalog_review->updateReview($existing_review['review_id'], $this->request->post, $final_images);
                } else {
                    $this->model_catalog_review->addReview($product_id, $this->request->post, $final_images, $customer_id);
                }
            } else {
                $this->model_catalog_review->addReview($product_id, $this->request->post, $final_images);
            }

            $json['success'] = $this->language->get('text_success');
        }

    } else {
        // GET request: prefill existing review if logged in
        if ($this->customer->isLogged()) {
            $this->load->model('catalog/review');
            $existing_review = $this->model_catalog_review->getReviewByCustomerAndProduct(
                $this->customer->getId(),
                (int)$this->request->get['product_id']
            );

            if ($existing_review) {
                $existing_images = [];
                $this->load->model('tool/image');
                for ($i = 1; $i <= 5; $i++) {
                    $image_column = 'image' . $i;
                    if (!empty($existing_review[$image_column])) {
                        $filename = $existing_review[$image_column];
                        $thumb_url = $this->model_tool_image->resize('catalog/review/' . $filename, 90, 90);

                        $existing_images[] = [
                            'url' => $thumb_url,
                            'filename' => $filename
                        ];
                    }
                }
                $json['existing_review_images'] = $existing_images;
            } else {
                $json['existing_review_images'] = [];
            }
        } else {
            $json['existing_review_images'] = [];
        }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}

// review end 
        

    	public function getRecurringDescription() {
    		$this->load->language('product/product');
    		$this->load->model('catalog/product');
    
    		if (isset($this->request->post['product_id'])) {
    			$product_id = $this->request->post['product_id'];
    		} else {
    			$product_id = 0;
    		}
    
    		if (isset($this->request->post['recurring_id'])) {
    			$recurring_id = $this->request->post['recurring_id'];
    		} else {
    			$recurring_id = 0;
    		}
    
    		if (isset($this->request->post['quantity'])) {
    			$quantity = $this->request->post['quantity'];
    		} else {
    			$quantity = 1;
    		}
    
    		$product_info = $this->model_catalog_product->getProduct($product_id);
    		
    		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);
    
    		$json = array();
    
    		if ($product_info && $recurring_info) {
    			if (!$json) {
    				$frequencies = array(
    					'day'        => $this->language->get('text_day'),
    					'week'       => $this->language->get('text_week'),
    					'semi_month' => $this->language->get('text_semi_month'),
    					'month'      => $this->language->get('text_month'),
    					'year'       => $this->language->get('text_year'),
    				);
    
    				if ($recurring_info['trial_status'] == 1) {
    				// 	$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
    					$price = $this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax'));
    					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
    				} else {
    					$trial_text = '';
    				}
    
    				// $price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
    				   $price = $this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax'));
    
    
    				if ($recurring_info['duration']) {
    					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
    				} else {
    					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
    				}
    
    				$json['success'] = $text;
    			}
    		}
    
    		$this->response->addHeader('Content-Type: application/json');
    		$this->response->setOutput(json_encode($json));
    	}
	
        // 	check pincode
        public function checkPincode(){
        
        		$this->load->model('catalog/product');
        		$json = [];
        
        		$product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;
        		$pincode = isset($this->request->get['pincode']) ? $this->request->get['pincode'] : '';
        
        		if ($product_id && $pincode) {
        			$this->load->model('vendor/product');
        			$json['available'] = $this->model_vendor_product->checkProductAvailability($product_id, $pincode);
        			
                // var_dump($json['available']);
                    
        			if ($json['available']) {
        				// $customerPincode = $this->model_catalog_product->getCustomerPostcode();
        				$courierResult = $this->model_catalog_product->getCourierCharges($product_id, $pincode);
        
        				$json['courierCharges'] = $courierResult['courier_charge'];
        				$json['freeCharges'] = $courierResult['freeCharges'];
        			}
        		} else {
        			$json['available'] = false;
        		}
        		$this->response->addHeader('Content-Type: application/json');
        		$this->response->setOutput(json_encode($json));
        	}
        	 // return_list page image 
        	public function getProductByModel($model) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE model = '" . $this->db->escape($model) . "' LIMIT 1");
            return $query->row;
        }
        // review start
        // review end 
    
    
        //faq starts

    	public function submitFaq() {
        $json = [];
    
        if (!$this->customer->isLogged()) {
            $json['error'] = 'You must be logged in to submit a question.';
        } elseif ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('catalog/product');
    
            $product_id = (int)$this->request->post['product_id'];
            $question = trim($this->request->post['question']);
            $customer_id = (int)$this->customer->getId();
            $language_id = (int)$this->config->get('config_language_id');
    
            if ($product_id && $question) {
                $this->model_catalog_product->addProductFaq([
                    'product_id' => $product_id,
                    'customer_id' => $customer_id,
                    'language_id' => $language_id,
                    'question' => $question
                ]);
                $json['success'] = true;
            } else {
                $json['error'] = 'Missing data.';
            }
        } else {
            $json['error'] = 'Invalid request method.';
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
        }


        public function faq() {
            $this->load->language('product/product');
            $this->load->model('catalog/product');
        
        	$this->load->language('product/product');
        	
        	
            if (isset($this->request->get['product_id'])) {
        		$product_id = (int)$this->request->get['product_id'];
            } else {
        		return $this->response->redirect($this->url->link('common/home'));
            }
        	
        	$data['text_login2'] = sprintf($this->language->get('text_login2'), 
        		$this->url->link('account/login', '', true),
        		$this->url->link('account/register', '', true)
        	);
        	$data['product_id'] = $product_id; // make sure this is correct
        	$data['logged'] = $this->customer->isLogged();
            $product_info = $this->model_catalog_product->getProduct($product_id);
        
            if (!$product_info) {
                return $this->response->redirect($this->url->link('common/home'));
            }
        
            // Load image tool for thumbnail
            $this->load->model('tool/image');
        
            $data['product'] = array(
                'name'     => $product_info['name'],
                'price'    => $this->currency->format($product_info['price'], $this->session->data['currency']),
                'thumb'    => $product_info['image'] ? $this->model_tool_image->resize($product_info['image'], 200, 200) : '',
                'href'     => $this->url->link('product/product', 'product_id=' . $product_id)
            );
        
            // Load FAQs
            $data['product_faqs'] = $this->model_catalog_product->getProductFaqs($product_id);
        
            $this->document->setTitle($product_info['name'] . ' - FAQs');
            $data['heading_title'] = 'All FAQs for ' . $product_info['name'];
        
            $data['breadcrumbs'] = array(
                array(
                    'text' => $this->language->get('text_home'),
                    'href' => $this->url->link('common/home')
                ),
                array(
                    'text' => $product_info['name'],
                    'href' => $this->url->link('product/product', 'product_id=' . $product_id)
                ),
                array(
                    'text' => 'All FAQs',
                    'href' => ''
                )
            );
        
        			$data['continue'] = $this->url->link('common/home');
        
        			$data['column_left'] = $this->load->controller('common/column_left');
        			$data['column_right'] = $this->load->controller('common/column_right');
        			$data['content_top'] = $this->load->controller('common/content_top');
        			$data['content_bottom'] = $this->load->controller('common/content_bottom');
        			$data['footer'] = $this->load->controller('common/footer');
        			$data['header'] = $this->load->controller('common/header');
            $this->response->setOutput($this->load->view('product/faq_list', $data));
        }
        //faq ends
        
        // added on 5-07-2025 for geolocation
        public function getVendorPostcode() {
            $this->load->model('catalog/product'); // Load model
        
            $json = array();
        
            if (isset($this->request->post['product_id'])) {
                $product_id = (int)$this->request->post['product_id'];
        
                // Call model function to get postcode
                $postcode = $this->model_catalog_product->getVendorPostcodeByProductId($product_id);
        
                if ($postcode) {
                    $json['postcode'] = $postcode;
                }
            }
        
            // Return response as JSON
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
        protected function setActiveOfferAndCourierData(&$data)
	{
		// Fetch offer by offer_id from oc_ipoffer
		$offer_id = 1; // Replace with dynamic value if needed
		$this->load->model('catalog/product');
		$offer = $this->model_catalog_product->getOfferById($offer_id);
		// Only show offer if it exists and is enabled
		if ($offer && isset($offer['status']) && $offer['status'] == 1) {
			$data['active_offer'] = $offer;
		} else {
			$data['active_offer'] = false;
		}
		// Fix courier_charge and freeCharges undefined index
		if (!isset($data['courier_charge'])) {
			$data['courier_charge'] = '';
		}
		if (!isset($data['freeCharges'])) {
			$data['freeCharges'] = '';
		}
	}
	
// 	description 
private function fixBrokenHtmlTags($html) {
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();

    // This ensures UTF-8 is respected and unclosed tags are handled
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $fixedHtml = $dom->saveHTML();
    libxml_clear_errors();
    return $fixedHtml;
}

// description end 

// load more

// public function loadMoreProducts() {
//     $this->load->model('catalog/product');
//     $this->load->model('tool/image');

//     $category_id = (int)$this->request->get['category_id'];
//     $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
//     $limit = 6;
//     $start = ($page - 1) * $limit;

//     $filter_data = array(
//         'filter_category_id' => $category_id,
//         'start' => $start,
//         'limit' => $limit
//     );

//     $results = $this->model_catalog_product->getProducts($filter_data);

//     $products = array();
//     foreach ($results as $result) {
//         $image = $result['image']
//             ? $this->model_tool_image->resize($result['image'], 200, 200)
//             : $this->model_tool_image->resize('placeholder.png', 200, 200);

//         $products[] = array(
//             'product_id' => $result['product_id'],
//             'thumb'      => $image,
//             'name'       => $result['name'],
//             'price'      => $result['price'],
//             'href'       => $this->url->link('product/product', 'product_id=' . $result['product_id'])
//         );
//     }

//     $this->response->addHeader('Content-Type: application/json');
//     $this->response->setOutput(json_encode($products));
// }
public function loadMoreProducts() {
    $this->load->model('catalog/product');
    $this->load->model('tool/image');

    $category_id = (int)$this->request->get['category_id'];
    $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
    $limit = 10;
    $start = ($page - 1) * $limit;

    $filter_data = array(
        'filter_category_id' => $category_id,
        'start' => $start,
        'limit' => $limit
    );

    $results = $this->model_catalog_product->getProducts($filter_data);
    $products = array();

    foreach ($results as $result) {
        $image = $result['image']
            ? $this->model_tool_image->resize($result['image'], 180, 180)
            : $this->model_tool_image->resize('placeholder.png', 180, 180);

        $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

        $special = false;
        $discount_percent = null;

        if ((float)$result['special']) {
            $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            $discount_percent = round((($result['price'] - $result['special']) / $result['price']) * 100);
        }

        $products[] = array(
            'product_id' => $result['product_id'],
            'thumb'      => $image,
            'name'       => $result['name'],
            'price'      => $price,
            'special'    => $special,
            'discount'   => $discount_percent,
            'href'       => $this->url->link('product/product', 'product_id=' . $result['product_id'])
        );
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($products));
}

// load more end
// emi start 
public function emi()
	{
		$this->response->addHeader('Content-Type: application/json');

		$key_id = 'rzp_live_bQbZP0Klg0VXhd';      // 🔁 Replace with your test key
		$url = "https://api.razorpay.com/v1/methods";

		$curl = curl_init($url);

		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERPWD => $key_id,
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
		));

		$response = curl_exec($curl);

		if (curl_errno($curl)) {
			$this->response->setOutput(json_encode(['error' => curl_error($curl)]));
		} else {
			$this->response->setOutput($response);
		}

		curl_close($curl);
	}
// emi end 

    // Aggregate Ratings and Reviews
    private function getProductJsonLd($product_info, $product_id) {
        
        $this->load->model('catalog/review');

        $review_count = $this->model_catalog_review->getTotalReviewsByProductId($product_id);
        $avg_rating   = $this->model_catalog_review->getAverageRatingByProductId($product_id);

        $jsonld = [
            "@context" => "https://schema.org",
            "@type"    => "Product",
            "name"     => $product_info['name'],
            "image"    => $this->model_tool_image->resize($product_info['image'], 800, 800),
            "description" => html_entity_decode($product_info['meta_description'] ?: strip_tags($product_info['description']), ENT_QUOTES, 'UTF-8'),
            "sku"      => $product_info['sku'] ?? "",
            "brand"    => ["@type" => "Brand", "name" => $product_info['manufacturer'] ?? ""],
            "offers"   => [
                "@type" => "Offer",
                "url"   => $this->url->link('product/product', 'product_id=' . (int)$product_id),
                "priceCurrency" => $this->session->data['currency'] ?? $this->config->get('config_currency'),
                "price" => (float)$product_info['price'],
                "availability" => ($product_info['quantity'] > 0) ? "https://schema.org/InStock" : "https://schema.org/OutOfStock"
            ]
        ];
    
        if ($review_count > 0 && $avg_rating > 0) {
            $jsonld["aggregateRating"] = [
                "@type" => "AggregateRating",
                "ratingValue" => round($avg_rating, 1),
                "reviewCount" => $review_count
            ];
        }
    
        return [
            'jsonld' => '<script type="application/ld+json">' .
                json_encode($jsonld, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) .
                '</script>',
            'review_count' => $review_count,
            'avg_rating'   => $avg_rating ? round($avg_rating, 1) : 0
        ];
    }
    
    
    
    
}