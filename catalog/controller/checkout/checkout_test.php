<?php
class ControllerCheckoutCheckoutTest extends Controller
{
	public function index()
	{
		$this->load->model('catalog/product');
		// Validate cart has products and has stock.

		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}
		// getting mobile number 
		if ($this->customer->isLogged()) {
			$telephone = $this->customer->getTelephone();

			if (empty($telephone) || strlen(trim($telephone)) < 5) {
				$data['phone_status'] = 'missing';
			} else {
				$data['phone_status'] = 'exists';
				$data['customer_phone'] = $telephone;
			}
		} else {
			$data['phone_status'] = 'guest';
		}

		// Load Address Model
		$this->load->model('account/address');

		// Fetch all saved addresses of the logged-in customer
		if (!$this->customer->isLogged()) {
			$data['login'] = $this->load->controller('checkout/login');

			$data['addresses'] = array(); // Empty array for guests
			$data['shipping_addresses'] = array();
			// var_dump($data['shipping_addresses']);
			// var_dump('-------------------------------------------------------');
		} else {
			$data['addresses'] = $this->model_account_address->getAddresses();
			$data['shipping_addresses'] = $this->model_account_address->getAddresses();



			// nikita btn-confirm order

			// Fetch customer details (first name, last name, email, telephone)
			$data['firstname'] = $this->customer->getFirstName();
			$data['lastname'] = $this->customer->getLastName();
			$data['email'] = $this->customer->getEmail();
			$data['telephone'] = $this->customer->getTelephone();

			// Fetch all available countries for the new address form dropdown
			$this->load->model('localisation/country');
			$data['countries'] = $this->model_localisation_country->getCountries();

			// Set Default Address ID
			if (isset($this->session->data['payment_address']['address_id'])) {
				$data['selected_address_id'] = $this->session->data['payment_address']['address_id'];
			} else {
				$data['selected_address_id'] = $this->customer->getAddressId(); // Default Address
			}




			// Set Default Shipping Address to match Billing Address (if selected)
			if (isset($this->session->data['shipping_address']['address_id'])) {
				$data['selected_shipping_address_id'] = $this->session->data['shipping_address']['address_id'];
			} else {
				$data['selected_shipping_address_id'] = $data['selected_address_id']; // Default: Use same address as billing
			}

			// Get the details of the selected address
			if (!empty($data['selected_address_id'])) {
				$data['selected_billing_details'] = $this->model_account_address->getAddress($data['selected_address_id']);
			} else {
				$data['selected_billing_details'] = array();
			}

			if (!empty($data['selected_shipping_address_id'])) {
				$data['selected_shipping_details'] = $this->model_account_address->getAddress($data['selected_shipping_address_id']);
			} else {
				$data['selected_shipping_details'] = array();
			}

			// var_dump($data['selected_shipping_details']["postcode"]);

			if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {


				$this->response->redirect($this->url->link('checkout/cart'));
			}

			// Validate minimum quantity requirements.
			$products = $this->cart->getProducts();
// var_dump($products,'%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%5');
			// foreach ($products as $product) {
			// 	$product_total = 0;

			// 	foreach ($products as $product_2) {
			// 		if ($product_2['product_id'] == $product['product_id']) {
			// 			$product_total += $product_2['quantity'];
			// 		}
			// 	}

			// 	if ($product['minimum'] > $product_total) {
			// 		$this->response->redirect($this->url->link('checkout/cart'));
			// 	}
			// }

			$this->load->model('tool/image');
			$this->load->model('tool/upload');

			$sub_total = 0;
			$courierChargess = 0;
			 $isProductPrepaid = false;
			foreach ($products as $product) {
			     if(!$isProductPrepaid){

                    $isProductPrepaid = $this->model_catalog_product->isProductPrepaid($product['product_id']);
                        // 		var_dump($isProductPrepaid);
                    }
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
						// var_dump('product quantity : - ', $product_total);
						// here we can check the product is available or not

					}
				}

				if ($product['minimum'] > $product_total) {
					$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}
				// print_r($this->config->get('config_theme'));
				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], 40, 40);
				} else {
					$image = '';
				}

				$option_data = array();

				foreach ($product['option'] as $option) {
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

				// Display prices
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
					$total = $unit_price * $product['quantity'];
					$price = $this->currency->format($unit_price, $this->session->data['currency']);
					$total = $this->currency->format($total, $this->session->data['currency']);

					$sub_total += ($unit_price * $product['quantity']);
					$courierResult = $this->model_catalog_product->getCourierCharges($product['product_id'], $data['selected_shipping_details']["postcode"]);
				
					$courierCharges = $courierResult['courier_charge'];
					$courierChargess += $courierResult['courier_charge'];
					$freeCharges = $courierResult['freeCharges'];
					$localCharges =  $courierResult['local_charges'];
			
					$quantity = (int)$product['quantity'];
				if ($courierCharges) {
					if ((float)$product['quantity'] === 1) {
						$final_courier_charges = (float)$courierCharges;
						$grand_total = (float)$final_courier_charges + ((float)$unit_price * (float)$quantity);
					} else if ((float)$product['quantity'] < (float)$freeCharges) {
						$final_courier_charges = (float)$localCharges * (float)$quantity; // FIXED LOGIC
						$grand_total = (float)$final_courier_charges + ((float)$unit_price * (float)$quantity);
					} else {
						$final_courier_charges = 0;
						$grand_total = (float)$final_courier_charges + ((float)$unit_price * (float)$quantity);
					}
				} else {
					$final_courier_charges = 0;
					$grand_total = (float)$unit_price * (float)$quantity;
				}
				$final_courier_charges = $this->currency->format($final_courier_charges , $this->session->data['currency']);
				$grand_total = $this->currency->format($grand_total, $this->session->data['currency']);
				} else {
					$price = false;
					$total = false;
				}

				$recurring = '';

				if ($product['recurring']) {
					$frequencies = array(
						'day'        => $this->language->get('text_day'),
						'week'       => $this->language->get('text_week'),
						'semi_month' => $this->language->get('text_semi_month'),
						'month'      => $this->language->get('text_month'),
						'year'       => $this->language->get('text_year')
					);

					if ($product['recurring']['trial']) {
						$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
					}

					if ($product['recurring']['duration']) {
						$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					} else {
						$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					}
				}

				$data['products'][] = array(
					'cart_id'   => $product['cart_id'],
					'thumb'     => $image,
					'name'      => $product['name'],
					'model'     => $product['model'],
					'option'    => $option_data,
					'recurring' => $recurring,
					'quantity'  => $product['quantity'],
					'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
					'price'     => $price,
					'total'     => $total,
					'grand_total'     => $grand_total,
					'courier_charges' =>  $final_courier_charges,
					'free_charges' => $freeCharges,
					'local_charges' => $freeCharges,
					'zonal_charges' => $freeCharges,
					'national_charges' => $freeCharges,
					'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}


			$data['courierCharges'] =  $this->currency->format($courierChargess, $this->session->data['currency']);
			$data['grand_total'] = $this->currency->format($sub_total + $courierChargess, $this->session->data['currency']);
			$data['sub_total'] = $this->currency->format($sub_total, $this->session->data['currency']);



			$this->load->language('checkout/checkout');

			$this->document->setTitle($this->language->get('heading_title'));

			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

			// Required by klarna
			if ($this->config->get('payment_klarna_account') || $this->config->get('payment_klarna_invoice')) {
				$this->document->addScript('http://cdn.klarna.com/public/kitt/toc/v1.0/js/klarna.terms.min.js');
			}

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_cart'),
				'href' => $this->url->link('checkout/cart')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('checkout/checkout', '', true)
			);

			$data['text_checkout_option'] = sprintf($this->language->get('text_checkout_option'), 1);
			$data['text_checkout_account'] = sprintf($this->language->get('text_checkout_account'), 2);
			$data['text_checkout_payment_address'] = sprintf($this->language->get('text_checkout_payment_address'), 2);
			$data['text_checkout_shipping_address'] = sprintf($this->language->get('text_checkout_shipping_address'), 3);
			$data['text_checkout_shipping_method'] = sprintf($this->language->get('text_checkout_shipping_method'), 4);

			if ($this->cart->hasShipping()) {
				$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 5);
				$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 6);
			} else {
				$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 3);
				$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 4);
			}

			if (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];
				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			$data['logged'] = $this->customer->isLogged();

			if (isset($this->session->data['account'])) {
				$data['account'] = $this->session->data['account'];
			} else {
				$data['account'] = '';
			}

			$data['shipping_required'] = $this->cart->hasShipping();


			// var_dump($data['register']);


			$data['payment_method-new'] = $this->load->controller('checkout/payment_method');

			$data['payment_address_action'] = $this->url->link('checkout/payment_address/save', '', true);
			$data['shipping_address_action'] = $this->url->link('checkout/shipping_address/save', '', true);

			// var_dump($data['payment_method-new']);
			$data['cart_edit_action'] = $this->url->link('checkout/cart/edit', '', true);
		}
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');


		// Ensure default address is set as payment_address
		if ($this->customer->isLogged() && !isset($this->session->data['payment_address'])) {
			$address_id = $this->customer->getAddressId();
			if ($address_id) {
				$default_address = $this->model_account_address->getAddress($address_id);
				if ($default_address) {
					$this->session->data['payment_address'] = $default_address;
				}
			}
		}

		 $this->load->controller('checkout/payment_method');
		// Check if 'payment_methods' is set in the session
	if (isset($this->session->data['payment_methods'])) {
		    $payment_methodS = $this->session->data['payment_methods'];
		    if ($isProductPrepaid) {
		var_dump($isProductPrepaid);
        // Remove 'cod' (Cash On Delivery)
        foreach ($payment_methodS as $key => $method) {
            if ($method['code'] == 'cod') {
                unset($payment_methodS[$key]);
		var_dump($payment_methodS);
            }
        }
    }
    
		    $data['payment_methodsss'] = $payment_methodS;

		} else {
			// Handle the case where payment_methods is not set
// 			var_dump("Payment methods not set in session");
		}
        
        $data['first_Purchase'] =null;
        
		// echo '<pre>';
		// print_r($this->session->data);
		// echo '</pre>';
		// exit;
		$this->response->setOutput($this->load->view('checkout/checkout_test', $data));
	}

	public function updateProductQuandity()
	{
		$this->load->language('checkout/cart');

		$json = array();

		// Update
		if (!empty($this->request->post['quantity'])) {
			foreach ($this->request->post['quantity'] as $key => $value) {
				$this->cart->update($key, $value);
				// var_dump($key, $value);
				$json['success'] = 'success';
			}

			$this->session->data['success'] = $this->language->get('text_remove');

			// unset($this->session->data['shipping_method']);
			// unset($this->session->data['shipping_methods']);
			// unset($this->session->data['payment_method']);
			// unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

			// $this->response->redirect($this->url->link('checkout/cart'));
		}
		$this->response->addHeader('Content-Type: application/json');
// echo '<pre>';
// 		print_r($this->session->data);
// 		echo '</pre>';
// 		exit;
//         $json='payment method';
		$this->response->setOutput(json_encode($json));
	}

	public function country()
	{
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function customfield()
	{
		$json = array();

		$this->load->model('account/custom_field');

		// Customer Group
		if (isset($this->request->get['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->get['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->get['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		foreach ($custom_fields as $custom_field) {
			$json[] = array(
				'custom_field_id' => $custom_field['custom_field_id'],
				'required'        => $custom_field['required']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
