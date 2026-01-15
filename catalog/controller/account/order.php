<?php
class ControllerAccountOrder extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/order');
	
		$this->document->setTitle($this->language->get('heading_title'));
		
		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/order', $url, true)
		);

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['orders'] = array();

		$this->load->model('account/order');
		$this->load->model('tool/image');

		$order_total = $this->model_account_order->getTotalOrders();

		$results = $this->model_account_order->getOrders(($page - 1) * 10, 10);

	foreach ($results as $result) {
			$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
			$voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);
			$awbno = $this->model_account_order->getAwbNo($result['order_id']);
			error_log("AWB for order {$result['order_id']}: " . $awbno);
			
			$image =  $this->model_account_order->getOrderProducts_New($result['order_id']);


			$image2 = (!empty($image) && isset($image[0]['image']) && is_file(DIR_IMAGE .$image[0]['image']))
			? $this->model_tool_image->resize($image[0]['image'], 50, 50) // Resize to 100x100px
			: 'no image';

		
			$data['orders'][] = array(
				'order_id'      => $result['order_id'],
				'name'          => $result['firstname'] . ' ' . $result['lastname'],
				'status'        => $result['status'],
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'products'      => ($product_total + $voucher_total),
				'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'product_image' =>$image2,
				'product_names' => $result['product_names'], // Include product names
				'view'          => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], true),
				'awbno'         => $awbno
			);
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('account/order', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($order_total - 10)) ? $order_total : ((($page - 1) * 10) + 10), $order_total, ceil($order_total / 10));

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
	$data['column_left_account'] = $this->load->controller('account/column_left_account');
		$this->response->setOutput($this->load->view('account/order_list', $data));
	}

	public function info() {
		$this->load->language('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			$this->document->setTitle($this->language->get('text_order'));
			
			if ($order_info['order_status_id'] == 5) {
			    $data['invoice'] = $this->url->link('account/order/invoice', 'order_id=' . $order_id, true);
			}
			
			$data['cancel'] = $this->url->link('account/order');
			
			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/order', $url, true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_order'),
				'href' => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'] . $url, true)
			);

			if (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			if ($order_info['invoice_no']) {
				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$data['invoice_no'] = '';
			}

			$data['order_id'] = $this->request->get['order_id'];
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);

			$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['payment_method'] = $order_info['payment_method'];

			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);

			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['shipping_method'] = $order_info['shipping_method'];

			$this->load->model('catalog/product');
			$this->load->model('tool/upload');

			// Products
			$data['products'] = array();

			$products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);

			foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_account_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$product_info = $this->model_catalog_product->getProduct($product['product_id']);
				
				/////this validation for order status is shipped then return button are visible
                
                // added on 01-04-2025 ------------------------
				 if ($order_info['order_status_id'] == 18) { // Replace '3' with '18' which is order-status-id of delivered status 
					$data['show_return_button'] = true;
				}
				
             /////To hide the Return button if the order was shipped more than 7 days ago
            // if ($order_info['order_status'] == 'Shipped') {
            // $shipped_date = strtotime($order_info['date_modified']); // Assuming 'date_modified' stores the shipped date
            // $current_date = time();
            // $days_since_shipped = ($current_date - $shipped_date) / (60 * 60 * 24); // Convert seconds to days

            // if ($days_since_shipped <= 7) {
            //     $show_return_button = true;
            // }
            
            // Updated the changes for the return button on 01-04-2025
                if ($order_info['order_status'] == 'Complete' || $order_info['order_status'] == 'Reverse') {
                    $data['show_return_button'] = false;
    		    }
    		    //------------------------------------------------
            
            // added at 14-06-2025 for hsn code
            $hsn_result = $this->db->query("SELECT hsn_code, gst_rate FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product['product_id'] . "'");
					if (!empty($hsn_result->row)) {
						$hsn_code = $hsn_result->row['hsn_code'];
						$gst_rate = (float)$hsn_result->row['gst_rate'];
					} else {
						$hsn_code = '';
						$gst_rate = 18.00; // default if not set
					}
            // ==========-------------============
            // -----------------------------------------------------------------------courier charges
				// 	$courier_result = $this->db->query("SELECT courier_charges FROM " . DB_PREFIX . "order_product WHERE order_product_id = '" . (int)$product['order_product_id'] . "'");

					if ($order_info['total_courier_charges']) {
						$product['courier_charges'] = $order_info['total_courier_charges'];
					}  else {
						$product['courier_charges'] = 0.00;

			
					}
					// -----------------------------------------------------------------------courier charges
            
        	// Product base values
				$price = (float)$product['price'];
				$quantity = (int)$product['quantity'];
				$courier_charges = isset($product['courier_charges']) ? (float)$product['courier_charges'] : 0.00;
            
            // GST calculations
					$rate = $gst_rate > 0 ? $gst_rate : 18.00;
					$gst_amount = ($rate * $price * $quantity) / 100;
					
					if ($gst_type == 'cgst_sgst') {
						$cgst = $gst_amount / 2;
						$sgst = $gst_amount / 2;
						$igst = 0.00;
					} else {
						$cgst = 0.00;
						$sgst = 0.00;
						$igst = $gst_amount;
					}
            
            	
                   	
					
                    $price=$price-($gst_amount/$quantity);
					// Total = base price * qty + GST + courier
					$total_price = ($price * $quantity) + $gst_amount + $courier_charges;
				
	
				if ($product_info) {
					$reorder = $this->url->link('account/order/reorder', 'order_id=' . $order_id . '&order_product_id=' . $product['order_product_id'], true);
				} else {
					$reorder = '';
				}

				$data['products'][] = array(
					'name'     => $product['name'],
					'model'    => $product['model'],
					'option'   => $option_data,
					'quantity' => $product['quantity'],
					'hsn_code' => $hsn_code,
					'gst_rate' => $rate,
					'gst'=>$gst_amount,
					// Courier Charges
					'courier_charges' => $this->currency->format($courier_charges, $order_info['currency_code'], $order_info['currency_value']),
					
					
					// Raw GST values
					'cgst'     => $cgst,
					'sgst'     => $sgst,
					'igst'     => $igst,
				
					// Formatted GST values
					'cgst_formatted' => $this->currency->format($cgst, $order_info['currency_code'], $order_info['currency_value']),
					'sgst_formatted' => $this->currency->format($sgst, $order_info['currency_code'], $order_info['currency_value']),
					'igst_formatted' => $this->currency->format($igst, $order_info['currency_code'], $order_info['currency_value']),
				
					
				    'price'    => $this->currency->format($price, $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($total_price, $order_info['currency_code'], $order_info['currency_value']),
				// 	'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				// 	'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'reorder'  => $reorder,
					'return'   => $this->url->link('account/return/add', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], true)
				);
			}
			
// 			added by govind on 19-03-2025
// 			$data['product_total'] = $this->model_account_order->gettotalOrderTotal($this->request->get['order_id']);

			// Voucher
			$data['vouchers'] = array();

			$vouchers = $this->model_account_order->getOrderVouchers($this->request->get['order_id']);

			foreach ($vouchers as $voucher) {
				$data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

			// Totals
			$data['totals'] = array();

			$totals = $this->model_account_order->getOrderTotals($this->request->get['order_id']);

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),
				);
			}

			$data['comment'] = nl2br($order_info['comment']);

			// History
			$data['histories'] = array();

			$results = $this->model_account_order->getOrderHistories($this->request->get['order_id']);

			foreach ($results as $result) {
				$data['histories'][] = array(
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'status'     => $result['status'],
					'comment'    => $result['notify'] ? nl2br($result['comment']) : ''
				);
			}

			$data['continue'] = $this->url->link('account/order', '', true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/order_info', $data));
		} else {
			return new Action('error/not_found');
		}
	}

	public function reorder() {
		$this->load->language('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			if (isset($this->request->get['order_product_id'])) {
				$order_product_id = $this->request->get['order_product_id'];
			} else {
				$order_product_id = 0;
			}

			$order_product_info = $this->model_account_order->getOrderProduct($order_id, $order_product_id);

			if ($order_product_info) {
				$this->load->model('catalog/product');

				$product_info = $this->model_catalog_product->getProduct($order_product_info['product_id']);

				if ($product_info) {
					$option_data = array();

					$order_options = $this->model_account_order->getOrderOptions($order_product_info['order_id'], $order_product_id);

					foreach ($order_options as $order_option) {
						if ($order_option['type'] == 'select' || $order_option['type'] == 'radio' || $order_option['type'] == 'image') {
							$option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'checkbox') {
							$option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'text' || $order_option['type'] == 'textarea' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
							$option_data[$order_option['product_option_id']] = $order_option['value'];
						} elseif ($order_option['type'] == 'file') {
							$option_data[$order_option['product_option_id']] = $this->encryption->encrypt($this->config->get('config_encryption'), $order_option['value']);
						}
					}

					$this->cart->add($order_product_info['product_id'], $order_product_info['quantity'], $option_data);

					$this->session->data['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $product_info['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

					unset($this->session->data['shipping_method']);
					unset($this->session->data['shipping_methods']);
					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
				} else {
					$this->session->data['error'] = sprintf($this->language->get('error_reorder'), $order_product_info['name']);
				}
			}
		}

		$this->response->redirect($this->url->link('account/order/info', 'order_id=' . $order_id));
	}
	
// 	added by sagar on 16-02-2025 

    // for storing awb no 

//     public function getAwbNo() {
// 		$order_id = (int)$this->request->get['order_id'];
	
// 		$query = $this->db->query("SELECT awbno FROM " . DB_PREFIX . "order WHERE order_id = '" . $order_id . "'");
	
// 		if ($query->num_rows) {
// 			echo json_encode(['awbno' => $query->row['awbno']]);
// 		} else {
// 			echo json_encode(['awbno' => null]);
// 		}
	
// 		exit;
// 	}
	
// 	for cancel order 





// 	public function cancel() {
// 		$this->load->language('account/order');
// 		$this->load->model('account/order');
// 		$order_id = (int)$this->request->get['order_id'];
// 		$order_status = (int)$this->model_account_order->getOrderCancelId();
	
// 		if ($order_id) {
// 			$this->model_account_order->cancelOrder($order_id, $order_status);
// 			$this->session->data['success'] = $this->language->get('text_order_cancelled');
// 		} else {
// 			$this->session->data['error'] = $this->language->get('text_order_cancel_failed');
// 		}
	
// 		$this->response->redirect($this->url->link('account/order', '', true));
// 	}


// added on 19-03-2025 

    public function cancel()
   	{
    	$this->load->language('account/order');
    	$json = array();
    
    	if (!$this->customer->isLogged()) {
    		$json['redirect'] = $this->url->link('account/login', '', true);
    	}
    
    	if (isset($this->request->post['order_id'])) {
    		$order_id = (int)$this->request->post['order_id'];
    		$this->load->model('account/order');
    		$this->load->model('vendor/order_report');
    
    		// Get the canceled order status ID
    		$order_status_id = 7;//$this->model_account_order->getOrderCancelId();
    
    		if ($order_status_id) {
    			// Update order and insert into order history
    			$this->model_account_order->cancelOrder($order_id, $order_status_id);
    			// $this->model_vendor_order_report->changeOrderStatus($order_id, $order_status_id);
    			$json['success'] = 'Order successfully canceled.';
    		} else {
    			$json['error'] = 'Cancellation status not found.';
    		}
    	} else {
    		$json['error'] = 'Invalid Order ID.';
    	}
    
    	$this->response->addHeader('Content-Type: application/json');
    	$this->response->setOutput(json_encode($json));
    }


//     public function cancelShipwayOrder() {
// 		$json = array();
	
// 		if (!isset($this->request->post['order_id'])) {
// 			$json['error'] = 'Invalid Order ID';
// 		} else {
// 			$this->load->model('account/order');
// 			$this->load->model('vendor/order_report'); // ✅ Load the vendor/order_report model
	
// 			$order_id = (int)$this->request->post['order_id'];
// 			$order_data = $this->model_account_order->getOrder($order_id);
// 	        // $post_data ="";
// 			if ($order_data) {
// 				// $awb_number = $this->$order_data["awbno"];
// 			    $awb_number = $this->model_account_order->getAwbNo($order_id); // Assuming awb_number is in order table

// 				// var_dump($awb_number);
// 				// Shipway API Credentials
// 				$username = "ipshopy1@gmail.com";
// 				$key = "96V1f01z291K02U1jg35s5Sb93gB4QmY";
// 				$auth = base64_encode($username . ":" . $key);
// 			    $url = "";
				
// 				$url = 'https://app.shipway.com/api/Cancel/';
// 				$post_data = ["awb_number" => [$awb_number]];
				
// 				// Make API request
// 				$ch = curl_init($url);
// 				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 				curl_setopt($ch, CURLOPT_POST, true);
// 				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
// 				curl_setopt($ch, CURLOPT_HTTPHEADER, [
// 					"Authorization: Basic " . $auth,
// 					"Content-Type: application/json"
// 				]);
// 				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
// 				$response = curl_exec($ch);
// 				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// 				curl_close($ch);
				
// 				$response_data = json_decode($response, true);
// 				// var_dump(($response_data));
// 				if ($http_code == 200 || isset($response_data['status']) && $response_data['status'] == "success") {
// 					$cancel_status_id = 7; // Set canceled status ID
					
// 					$this->load->model('vendor/order_report');

// 					$this->model_account_order->cancelOrder($order_id, $cancel_status_id);

// 					// ✅ Call function from `vendor/order_report`
// 					// $this->model_vendor_order_report->cancelOrder($order_id, $cancel_status);
	
// 					$json['success'] = 'Order cancellation request sent successfully.';
// 				} else {
// 					$json['error'] = 'Failed to cancel order. Please try again.';
// 				}
// 			} else {
// 				$json['error'] = 'Order not found.';
// 			}
// 		}
	
// 		$this->response->addHeader('Content-Type: application/json');
// 		$this->response->setOutput(json_encode($json));
// 	}
	
	
	public function getAwbNo() {
        $json = array();
        
        if (isset($this->request->get['order_id'])) {
            $order_id = (int)$this->request->get['order_id'];
            $this->load->model('account/order');
            $awb_no = $this->model_account_order->getAwbNo($order_id);
            
            if ($awb_no) {
                $json['awbno'] = $awb_no;
            } else {
                $json['awbno'] = '';
            }
        } else {
            $json['awbno'] = '';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    
    public function invoice() {
		$this->load->language('account/order_invoice');
	

		$data['title'] = $this->language->get('text_invoice');

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['direction'] = $this->language->get('direction');
		$data['lang'] = $this->language->get('code');

		$this->load->model('account/order');
		$this->load->model('setting/setting');

		$data['orders'] = array();

		$orders = array();

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		}

		foreach ($orders as $order_id) {
			$order_info = $this->model_account_order->getOrder($order_id);
// 			$data['customer_gstin'] = isset($order_info['gstin']) ? $order_info['gstin'] : '';

			if ($order_info && $order_info['order_status_id'] == 5) { // Only process if order is completed (status 5)

				if (!$order_info['invoice_no']) {
					$invoice_no = $this->model_account_order->createInvoiceNo($order_id);
					$order_info['invoice_no'] = $invoice_no;
				} else {
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				}

				$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

				$store_address = $store_info ? $store_info['config_address'] : $this->config->get('config_address');
				$store_email = $store_info ? $store_info['config_email'] : $this->config->get('config_email');
				$store_telephone = $store_info ? $store_info['config_telephone'] : $this->config->get('config_telephone');
				$store_fax = $store_info ? $store_info['config_fax'] : $this->config->get('config_fax');

				$format = $order_info['payment_address_format'] ?:  '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';

				$find = array(
					'{firstname}', '{lastname}', '{company}', '{address_1}', '{address_2}',
					'{city}', '{postcode}', '{zone}', '{zone_code}', '{country}'
				);

				$replace = array(
					'firstname' => $order_info['payment_firstname'],
					'lastname'  => $order_info['payment_lastname'],
					'company'   => $order_info['payment_company'],
					'address_1' => $order_info['payment_address_1'],
					'address_2' => $order_info['payment_address_2'],
					'city'      => $order_info['payment_city'],
					'postcode'  => $order_info['payment_postcode'],
					'zone'      => $order_info['payment_zone'],
					'zone_code' => $order_info['payment_zone_code'],
					'country'   => $order_info['payment_country']
				);

				$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$format = $order_info['shipping_address_format'] ?: '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';

				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					'zone_code' => $order_info['shipping_zone_code'],
					'country'   => $order_info['shipping_country']
				);

				$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$this->load->model('tool/upload');

				$product_data = array();

				$products = $this->model_account_order->getOrderProducts($order_id);

				foreach ($products as $product) {
					$option_data = array();

					$options = $this->model_account_order->getOrderOptions($order_id, $product['order_product_id']);
					
								
		// ------------------return policy----------------------------------------------------------------------------------------------------------------------------
  
					$return_policy_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_return_policy WHERE product_id = '" . (int)$product['product_id'] . "'");

						if ($return_policy_query->num_rows) {
							$return_duration_period = $return_policy_query->row['return_duration_period'];
							$return_policy_details = $return_policy_query->row['return_policy_details'];
						} else {
							$return_duration_period = '-';
							$return_policy_details = 'No Return Policy';
						}

					
		// ----------------------------------------------------------------------------------------------------------------------------------------------



		// ---------------------warranty-------------------------------------------------------------------------------------------------------------------------

					$warranty_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_warranty WHERE product_id = '" . (int)$product['product_id'] . "'");

					if ($warranty_query->num_rows) {
						$product['warranty_by'] = $warranty_query->row['warranty_by'];
						$product['warranty_duration'] = $warranty_query->row['warranty_duration'];
						$product['warranty_description'] = $warranty_query->row['description'];
						$product['warranty'] = 1;
						$product['No_warranty'] =null;
					} else {
						$product['warranty_by'] = '-';
						$product['warranty_duration'] = '-';
						$product['warranty_description'] = '-';
						$product['warranty'] = 0;
						$product['No_warranty'] = 'No Warranty';
					
					}
		// ---------------------------------------------------------------------------------------------

					foreach ($options as $option) {
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
							$value = $upload_info ? $upload_info['name'] : '';
						}

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $value
						);
					}
					
					 // added at 14-06-2025 for hsn code
            $hsn_result = $this->db->query("SELECT hsn_code, gst_rate FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product['product_id'] . "'");
					if (!empty($hsn_result->row)) {
						$hsn_code = $hsn_result->row['hsn_code'];
						$gst_rate = (float)$hsn_result->row['gst_rate'];
					} else {
						$hsn_code = '';
						$gst_rate = 18.00; // default if not set
					}
            // ==========-------------============
            
             //  for vendor signatures 18-06-2025
                $product['signature'] = isset($order_info['vendor_signatures'][$product['product_id']]) 
            	? $order_info['vendor_signatures'][$product['product_id']] 
            	: '';
            
            
            


            //     // ------------========
            // -----------------------------------------------------------------------courier charges
				// 	$courier_result = $this->db->query("SELECT courier_charges FROM " . DB_PREFIX . "order_product WHERE order_product_id = '" . (int)$product['order_product_id'] . "'");

					if ($order_info['total_courier_charges']) {
						$product['courier_charges'] = $order_info['total_courier_charges'];
					}  else {
						$product['courier_charges'] = 0.00;

			
					}
					// -----------------------------------------------------------------------courier charges
            
                    // Product base values
					$price = (float)$product['price'];
					$quantity = (int)$product['quantity'];
					$courier_charges = isset($product['courier_charges']) ? (float)$product['courier_charges'] : 0.00;
					
					// GST calculations
					$rate = $gst_rate > 0 ? $gst_rate : 18.00;
					$gst_amount = ($rate * $price * $quantity) / 100;
					
					if ($gst_type == 'cgst_sgst') {
						$cgst = $gst_amount / 2;
						$sgst = $gst_amount / 2;
						$igst = 0.00;
					} else {
						$cgst = 0.00;
						$sgst = 0.00;
						$igst = $gst_amount;
					}
					$price=$price-($gst_amount/$quantity);
					// Total = base price * qty + GST + courier
					$total_price = ($price * $quantity) + $gst_amount + $courier_charges;

					$product_data[] = array(
						'name'     => $product['name'],
						'model'    => $product['model'],
						'option'   => $option_data,
						'quantity' => $product['quantity'],
				// 		'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				// 		'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
					    'price'    => $this->currency->format($price, $order_info['currency_code'], $order_info['currency_value']),
						'total'    => $this->currency->format($total_price, $order_info['currency_code'], $order_info['currency_value']),
						'hsn_code' => $hsn_code,
						'gst_rate' => $rate,
						'signature' => $product['signature'],
					
						// Raw GST values
						'cgst'     => $cgst,
						'sgst'     => $sgst,
						'igst'     => $igst,
					
						// Formatted GST values
						'cgst_formatted' => $this->currency->format($cgst, $order_info['currency_code'], $order_info['currency_value']),
						'sgst_formatted' => $this->currency->format($sgst, $order_info['currency_code'], $order_info['currency_value']),
						'igst_formatted' => $this->currency->format($igst, $order_info['currency_code'], $order_info['currency_value']),
						
						// Courier Charges
						'courier_charges' => $this->currency->format($courier_charges, $order_info['currency_code'], $order_info['currency_value']),
					
						// ✅ Return Policy
						'return_duration_period' => $return_duration_period,
						'return_policy_details' => $return_policy_details,
					
						// ✅ Warranty
						'warranty_by' => $product['warranty_by'],
						'warranty_duration' => $product['warranty_duration'],
						'warranty_description' => $product['warranty_description'],
						'warranty'=>$product['warranty'],
						'No_warranty'=>$product['No_warranty']
					);
				}

				$voucher_data = array();

				$vouchers = $this->model_account_order->getOrderVouchers($order_id);

				foreach ($vouchers as $voucher) {
					$voucher_data[] = array(
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
					);
				}

				$total_data = array();

				$totals = $this->model_account_order->getOrderTotals($order_id); 

				foreach ($totals as $total) {
					$total_data[] = array(
						'title' => $total['title'],
						'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
					);
				}

				$data['orders'][] = array(
					'order_id'         => $order_id,
					'invoice_no'       => $invoice_no,
					'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					'store_name'       => $order_info['store_name'],
					'store_url'        => rtrim($order_info['store_url'], '/'),
					'store_address'    => nl2br($store_address),
					'store_email'      => $store_email,
					'store_telephone'  => $store_telephone,
					'store_fax'        => $store_fax,
					'email'            => $order_info['email'],
					'telephone'        => $order_info['telephone'],
					'shipping_address' => $shipping_address,
					'shipping_method'  => $order_info['shipping_method'],
					'payment_address'  => $payment_address,
					'payment_method'   => $order_info['payment_method'],
					'product'          => $product_data,
					'voucher'          => $voucher_data,
					'total'            => $total_data,
					'comment'          => nl2br($order_info['comment']),
					'order_status_id'  => $order_info['order_status_id']
				);
		    }
		}
		
		$this->response->setOutput($this->load->view('account/order_invoice', $data));
	}
	
	
	public function invoicePDF() {
		$this->load->language('account/order_invoice');

		$data['title'] = $this->language->get('text_invoice');

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['direction'] = $this->language->get('direction');
		$data['lang'] = $this->language->get('code');

		$this->load->model('checkout/order');
		$this->load->model('setting/setting');

		$data['orders'] = array();

		$orders = array();

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		}

		foreach ($orders as $order_id) {
			$order_info = $this->model_checkout_order->getOrder($order_id);

			

			if ($order_info && $order_info['order_status_id'] == 5) { // Only process if order is completed (status 5)

				if (!$order_info['invoice_no']) {
					$invoice_no = $this->model_checkout_order->createInvoiceNo($order_id);
					$order_info['invoice_no'] = $invoice_no;
				} else {
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				}

				$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

				$store_address = $store_info ? $store_info['config_address'] : $this->config->get('config_address');
				$store_email = $store_info ? $store_info['config_email'] : $this->config->get('config_email');
				$store_telephone = $store_info ? $store_info['config_telephone'] : $this->config->get('config_telephone');
				$store_fax = $store_info ? $store_info['config_fax'] : $this->config->get('config_fax');

				$format = $order_info['payment_address_format'] ?:  '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';

				$find = array(
					'{firstname}', '{lastname}', '{company}', '{address_1}', '{address_2}',
					'{city}', '{postcode}', '{zone}', '{zone_code}', '{country}'
				);

				$replace = array(
					'firstname' => $order_info['payment_firstname'],
					'lastname'  => $order_info['payment_lastname'],
					'company'   => $order_info['payment_company'],
					'address_1' => $order_info['payment_address_1'],
					'address_2' => $order_info['payment_address_2'],
					'city'      => $order_info['payment_city'],
					'postcode'  => $order_info['payment_postcode'],
					'zone'      => $order_info['payment_zone'],
					'zone_code' => $order_info['payment_zone_code'],
					'country'   => $order_info['payment_country']
				);

				$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$format = $order_info['shipping_address_format'] ?: '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';

				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					'zone_code' => $order_info['shipping_zone_code'],
					'country'   => $order_info['shipping_country']
				);

				$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$this->load->model('tool/upload');

				$product_data = array();

				$products = $this->model_checkout_order->getOrderProducts($order_id);

				foreach ($products as $product) {
					$option_data = array();

					$options = $this->model_checkout_order->getOrderOptions($order_id, $product['order_product_id']);

					foreach ($options as $option) {
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
							$value = $upload_info ? $upload_info['name'] : '';
						}

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $value
						);
					}
					
							 // added at 14-06-2025 for hsn code
            $hsn_result = $this->db->query("SELECT hsn_code, gst_rate FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product['product_id'] . "'");
					if (!empty($hsn_result->row)) {
						$hsn_code = $hsn_result->row['hsn_code'];
						$gst_rate = (float)$hsn_result->row['gst_rate'];
					} else {
						$hsn_code = '';
						$gst_rate = 18.00; // default if not set
					}
            // ==========-------------============
            
             //  for vendor signatures 18-06-2025
            //     $product['signature'] = isset($order_info['vendor_signatures'][$product['product_id']]) 
            // 	? $order_info['vendor_signatures'][$product['product_id']] 
            // 	: '';
            
            
            


            //     // ------------========
            // -----------------------------------------------------------------------courier charges
				// 	$courier_result = $this->db->query("SELECT courier_charges FROM " . DB_PREFIX . "order_product WHERE order_product_id = '" . (int)$product['order_product_id'] . "'");

					if ($order_info['total_courier_charges']) {
						$product['courier_charges'] = $order_info['total_courier_charges'];
					}  else {
						$product['courier_charges'] = 0.00;

			
					}
					// -----------------------------------------------------------------------courier charges
            
                    // Product base values
					$price = (float)$product['price'];
					$quantity = (int)$product['quantity'];
					$courier_charges = isset($product['courier_charges']) ? (float)$product['courier_charges'] : 0.00;
					
					// GST calculations
					$rate = $gst_rate > 0 ? $gst_rate : 18.00;
					$gst_amount = ($rate * $price * $quantity) / 100;
					
					if ($gst_type == 'cgst_sgst') {
						$cgst = $gst_amount / 2;
						$sgst = $gst_amount / 2;
						$igst = 0.00;
					} else {
						$cgst = 0.00;
						$sgst = 0.00;
						$igst = $gst_amount;
					}
					$price=$price-($gst_amount/$quantity);
					// Total = base price * qty + GST + courier
					$total_price = ($price * $quantity) + $gst_amount + $courier_charges;

					$product_data[] = array(
						'name'     => $product['name'],
						'model'    => $product['model'],
						'option'   => $option_data,
						'quantity' => $product['quantity'],
						'price'    => $this->currency->format($price, $order_info['currency_code'], $order_info['currency_value']),
						'total'    => $this->currency->format($total_price, $order_info['currency_code'], $order_info['currency_value']),
						'hsn_code' => $hsn_code,
						'gst_rate' => $rate,
				// 		'signature' => $product['signature'],
					
						// Raw GST values
						'cgst'     => $cgst,
						'sgst'     => $sgst,
						'igst'     => $igst,
					
						// Formatted GST values
						'cgst_formatted' => $this->currency->format($cgst, $order_info['currency_code'], $order_info['currency_value']),
						'sgst_formatted' => $this->currency->format($sgst, $order_info['currency_code'], $order_info['currency_value']),
						'igst_formatted' => $this->currency->format($igst, $order_info['currency_code'], $order_info['currency_value']),
						
						// Courier Charges
						'courier_charges' => $this->currency->format($courier_charges, $order_info['currency_code'], $order_info['currency_value'])
					
				// 		'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				// 		'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
					);
				}

				$voucher_data = array();

				$vouchers = $this->model_checkout_order->getOrderVouchers($order_id);

				foreach ($vouchers as $voucher) {
					$voucher_data[] = array(
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
					);
				}

				$total_data = array();

				$totals = $this->model_checkout_order->getOrderTotals($order_id);

				foreach ($totals as $total) {
					$total_data[] = array(
						'title' => $total['title'],
						'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
					);
				}

				$data['orders'][] = array(
					'order_id'         => $order_id,
					'invoice_no'       => $invoice_no,
					'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					'store_name'       => $order_info['store_name'],
					'store_url'        => rtrim($order_info['store_url'], '/'),
					'store_address'    => nl2br($store_address),
					'store_email'      => $store_email,
					'store_telephone'  => $store_telephone,
					'store_fax'        => $store_fax,
					'email'            => $order_info['email'],
					'telephone'        => $order_info['telephone'],
					'shipping_address' => $shipping_address,
					'shipping_method'  => $order_info['shipping_method'],
					'payment_address'  => $payment_address,
					'payment_method'   => $order_info['payment_method'],
					'product'          => $product_data,
					'voucher'          => $voucher_data,
					'total'            => $total_data,
					'comment'          => nl2br($order_info['comment']),
					'order_status_id'  => $order_info['order_status_id']
				);
		    }
		}

		// Save HTML to check
        // 		file_put_contents(DIR_LOGS . 'invoice_debug.html', $this->load->view('account/pdf_invoice', $order_info));
	
		// Generate PDF
		require_once(DIR_SYSTEM . 'library/tcpdf/tcpdf.php');
		$pdf = new TCPDF();
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Ipshopy');
		$pdf->SetTitle('Order Invoice');
		$pdf->AddPage();
	
		$html = $this->load->view('account/pdf_invoice', $data);
		$pdf->writeHTML($html, true, false, true, false, '');
	
		$invoice_path = DIR_INVOICE . 'Invoice-' . $order_id . '.pdf';
		$pdf->Output($invoice_path, 'F');
	
		return $invoice_path;
	}

}