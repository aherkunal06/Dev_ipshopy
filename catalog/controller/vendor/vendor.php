<?php
class ControllerVendorVendor extends Controller
{
	private $error = array();

	public function index() {

		if ($this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/success', '', true));
		}

		

        
        // Handle AJAX request first to ensure proper JSON response
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && 
			isset($this->request->server['HTTP_X_REQUESTED_WITH']) && 
			$this->request->server['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			
			// Suppress any PHP warnings/notices that could corrupt JSON output
			error_reporting(0);
			ini_set('display_errors', 0);
			
			// Start output buffering to catch any stray output
			ob_start();
			
			// Clear any existing output buffer
			while (ob_get_level()) {
				ob_end_clean();
			}
			
			// Start fresh output buffer
			ob_start();
			
			$this->response->addHeader('Content-Type: application/json');
			
			try {
				// Load vendor model first
				$this->load->model('vendor/vendor');
				
				// Map form fields to expected backend fields BEFORE validation
				if (!empty($this->request->post['country']) && empty($this->request->post['country_id'])) {
					$this->request->post['country_id'] = $this->request->post['country'];
				}
				if (!empty($this->request->post['zone']) && empty($this->request->post['zone_id'])) {
					$this->request->post['zone_id'] = $this->request->post['zone'];
				}
				
				if (!$this->validate()) {
					error_log("[VendorRegistration] Validation failed: " . json_encode($this->error));
					error_log("[VendorRegistration] POST data keys: " . json_encode(array_keys($this->request->post)));
					
					// Log specific missing fields
					$required_fields = ['firstname', 'lastname', 'email', 'telephone', 'company', 'address_1', 'city', 'postcode', 'country_id', 'zone_id', 'password', 'confirm', 'agree'];
					$missing_fields = [];
					
					foreach ($required_fields as $field) {
						if (empty($this->request->post[$field])) {
							$missing_fields[] = $field;
						}
					}
					
					error_log("[VendorRegistration] Missing required fields: " . json_encode($missing_fields));
					
					ob_clean();
					$this->response->setOutput(json_encode([
						'success' => false,
						'error' => 'Validation failed',
						'errors' => $this->error,
						'missing_fields' => $missing_fields,
						'debug_post_data' => array_keys($this->request->post)
					]));
					return;
				}
				
				// Add registered_by field
				$data = $this->request->post;
				$data['registered_by'] = 'Self Registered';
				
				// Create vendor account with error handling
				try {
					$vendor_id = $this->model_vendor_vendor->addVendor($data);
					
					if (!$vendor_id) {
						// Get the actual database error
						$db_error = $this->db->error;
						error_log("[VendorRegistration] Database error: " . $db_error);
						
						ob_clean();
						$this->response->setOutput(json_encode([
							'success' => false,
							'error' => 'Database error: ' . $db_error
						]));
						return;
					}
				} catch (Exception $db_e) {
					error_log("[VendorRegistration] Exception during addVendor: " . $db_e->getMessage());
					ob_clean();
					$this->response->setOutput(json_encode([
						'success' => false,
						'error' => 'Database exception: ' . $db_e->getMessage()
					]));
					return;
				}
				
				$response_data = [
					'success' => true,
					'vendor_id' => $vendor_id,
					'message' => 'Vendor registered successfully'
				];
				
				// Google Ads integration is now handled separately via microservice
				// The frontend will call googleAdsAutoCreate() endpoint after successful registration
				
				// Clean output buffer and send JSON
				ob_clean();
				$this->response->setOutput(json_encode($response_data));
				return;
				
			} catch (Exception $e) {
				error_log("[VendorRegistration] Exception: " . $e->getMessage());
				// Clean output buffer and send JSON error
				ob_clean();
				$this->response->setOutput(json_encode([
					'success' => false,
					'error' => $e->getMessage()
				]));
				return;
			}
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			// Add registered_by field
			$data = $this->request->post;
			$data['registered_by'] = 'Self Registered';
		
			try {
				// Create vendor account
				$vendor_id = $this->model_vendor_vendor->addVendor($data);
				
				// For regular form submission, proceed normally
				// Login vendor
				$this->vendor->login($this->request->post['email'], $this->request->post['password']);
				$this->response->redirect($this->url->link('vendor/success', '', true));
				
			} catch (Exception $e) {
				$this->error['warning'] = $e->getMessage();
			}
		}
        
        //---------- added code changes on 11-10-2025
        $this->load->language('vendor/vendor');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('vendor/vendor');
        //------------- end here-------------------------------

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_login'),
			'href' => $this->url->link('vendor/dashboard', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('vendor/vendor', '', true)
		);

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('vendor/login', '', true));
		$data['text_yes'] 			= $this->language->get('text_yes');
		$data['text_no'] 			= $this->language->get('text_no');
		$data['text_select'] 		= $this->language->get('text_select');
		$data['text_none'] 			= $this->language->get('text_none');
		$data['text_loading'] 		= $this->language->get('text_loading');
		$data['entry_firstname'] 	= $this->language->get('entry_firstname');
		$data['entry_lastname'] 	= $this->language->get('entry_lastname');
		$data['entry_telephone'] 	= $this->language->get('entry_telephone');
		$data['entry_gstin'] 			= $this->language->get('entry_gstin');
		$data['entry_company'] 		= $this->language->get('entry_company');
		$data['entry_address_1'] 	= $this->language->get('entry_address_1');
		$data['entry_address_2'] 	= $this->language->get('entry_address_2');

		$data['entry_newsletter'] 	= $this->language->get('entry_newsletter');
		$data['entry_password'] 	= $this->language->get('entry_password');
		$data['entry_confirm'] 		= $this->language->get('entry_confirm');
		$data['entry_about'] 		= $this->language->get('entry_about');
		$data['entry_image'] 		= $this->language->get('entry_image');
		$data['entry_display_name'] = $this->language->get('entry_display_name');
		$data['entry_bankname']  		= $this->language->get('entry_bankname');
		$data['entry_bnumber']  		= $this->language->get('entry_bnumber');
		$data['entry_swiftcode']  		= $this->language->get('entry_swiftcode');
		$data['entry_aname']  			= $this->language->get('entry_aname');
		$data['entry_anumber']  		= $this->language->get('entry_anumber');
		$data['entry_Emailid']  		= $this->language->get('entry_Emailid');
		$data['entry_method']  			= $this->language->get('entry_method');
		$data['text_bank']  			= $this->language->get('text_bank');
		$data['text_paypal']  			= $this->language->get('text_paypal');

		$data['entry_storename'] 		= $this->language->get('entry_storename');
		$data['entry_description'] 		= $this->language->get('entry_description');
		$data['entry_shippingpolicy'] 	= $this->language->get('entry_shippingpolicy');
		$data['entry_returnpolicy'] 	= $this->language->get('entry_returnpolicy');
		$data['entry_metakeyword'] 		= $this->language->get('entry_metakeyword');
		$data['entry_metadescription'] 	= $this->language->get('entry_metadescription');
		$data['entry_email'] 			= $this->language->get('entry_email');
		$data['entry_phone'] 			= $this->language->get('entry_phone');
		$data['entry_address'] 			= $this->language->get('entry_address');
		$data['entry_country'] 			= $this->language->get('entry_country');
		$data['entry_zone'] 			= $this->language->get('entry_zone');
		$data['entry_city'] 			= $this->language->get('entry_city');
		$data['entry_postcode'] 		= $this->language->get('entry_postcode');
		$data['entry_detail'] 			= $this->language->get('entry_detail');
		$data['entry_tax'] 				= $this->language->get('entry_tax');
		$data['entry_charges'] 			= $this->language->get('entry_charges');
		$data['entry_url'] 				= $this->language->get('entry_url');
		$data['entry_logo'] 			= $this->language->get('entry_logo');
		$data['entry_banner'] 			= $this->language->get('entry_banner');
		$data['entry_store_about'] 		= $this->language->get('entry_store_about');
		$data['entry_mapurl'] 		    = $this->language->get('entry_mapurl');
		$data['button_upload'] 			= $this->language->get('button_upload');
		$data['button_banner'] 			= $this->language->get('button_banner');
		$data['button_add'] 			= $this->language->get('button_add');
		$data['tab_general'] 			= $this->language->get('tab_general');
		$data['tab_data'] 			    = $this->language->get('tab_data');

		$data['tab_seller'] 		    = $this->language->get('tab_seller');
		$data['tab_generalstore'] 		= $this->language->get('tab_generalstore');
		$data['tab_datastore'] 			= $this->language->get('tab_datastore');
		$data['tab_payment'] 			= $this->language->get('tab_payment');

		$data['help_product'] = $this->language->get('help_product');

		$data['button_continue'] 	= $this->language->get('button_continue');
		$data['button_upload'] 		= $this->language->get('button_upload');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['filedwarning'])) {
			$data['filedwarning'] = $this->error['filedwarning'];
		} else {
			$data['filedwarning'] = '';
		}


		if (isset($this->error['display_name'])) {
			$data['error_display_name'] = $this->error['display_name'];
		} else {
			$data['error_display_name'] = '';
		}

		if (isset($this->error['firstname'])) {
			$data['error_firstname'] = $this->error['firstname'];
		} else {
			$data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$data['error_lastname'] = $this->error['lastname'];
		} else {
			$data['error_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['telephone'])) {
			$data['error_telephone'] = $this->error['telephone'];
		} else {
			$data['error_telephone'] = '';
		}

		if (isset($this->error['fax'])) {
			$data['error_fax'] = $this->error['fax'];
		} else {
			$data['error_fax'] = '';
		}
		if (isset($this->request->post['uin'])) {
			$data['uin'] = $this->request->post['uin'];
		} else {
			$data['uin'] = '';
		}
		if (isset($this->error['company'])) {
			$data['error_company'] = $this->error['company'];
		} else {
			$data['error_company'] = '';
		}

		if (isset($this->error['address_1'])) {
			$data['error_address_1'] = $this->error['address_1'];
		} else {
			$data['error_address_1'] = '';
		}

		if (isset($this->error['address_2'])) {
			$data['error_address_2'] = $this->error['address_2'];
		} else {
			$data['error_address_2'] = '';
		}

		if (isset($this->error['city'])) {
			$data['error_city'] = $this->error['city'];
		} else {
			$data['error_city'] = '';
		}


		if (isset($this->error['country'])) {
			$data['error_country'] = $this->error['country'];
		} else {
			$data['error_country'] = '';
		}

		if (isset($this->error['zone'])) {
			$data['error_zone'] = $this->error['zone'];
		} else {
			$data['error_zone'] = '';
		}

		if (isset($this->error['password'])) {
			$data['error_password'] = $this->error['password'];
		} else {
			$data['error_password'] = '';
		}

		if (isset($this->error['confirm'])) {
			$data['error_confirm'] = $this->error['confirm'];
		} else {
			$data['error_confirm'] = '';
		}

		if (isset($this->error['about'])) {
			$data['error_about'] = $this->error['about'];
		} else {
			$data['error_about'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['meta_description'])) {
			$data['error_meta_description'] = $this->error['meta_description'];
		} else {
			$data['error_meta_description'] = '';
		}

		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = '';
		}

		if (isset($this->error['shipping_policy'])) {
			$data['error_shipping_policy'] = $this->error['shipping_policy'];
		} else {
			$data['error_shipping_policy'] = '';
		}

		if (isset($this->error['return_policy'])) {
			$data['error_return_policy'] = $this->error['return_policy'];
		} else {
			$data['error_return_policy'] = '';
		}

		if (isset($this->error['meta_keyword'])) {
			$data['error_meta_keyword'] = $this->error['meta_keyword'];
		} else {
			$data['error_meta_keyword'] = '';
		}

		if (isset($this->error['bank_detail'])) {
			$data['error_bank_detail'] = $this->error['bank_detail'];
		} else {
			$data['error_bank_detail'] = '';
		}

		if (isset($this->error['store_about'])) {
			$data['error_store_about'] = $this->error['store_about'];
		} else {
			$data['error_store_about'] = '';
		}

		if (isset($this->error['map_url'])) {
			$data['error_map_url'] = $this->error['map_url'];
		} else {
			$data['error_map_url'] = '';
		}

		if (isset($this->error['tax_number'])) {
			$data['error_tax_number'] = $this->error['tax_number'];
		} else {
			$data['error_tax_number'] = '';
		}

		if (isset($this->error['shipping_charge'])) {
			$data['error_shipping_charge'] = $this->error['shipping_charge'];
		} else {
			$data['error_shipping_charge'] = '';
		}

		if (isset($this->error['paypal'])) {
			$data['error_paypal'] = $this->error['paypal'];
		} else {
			$data['error_paypal'] = '';
		}

		if (isset($this->error['bank_account_name'])) {
			$data['error_bank_account_name'] = $this->error['bank_account_name'];
		} else {
			$data['error_bank_account_name'] = '';
		}

		if (isset($this->error['bank_account_number'])) {
			$data['error_bank_account_number'] = $this->error['bank_account_number'];
		} else {
			$data['error_bank_account_number'] = '';
		}
		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}



		// if (isset($this->request->get['pincode']) && !empty($this->request->get['pincode'])) {
		// 	$pincode = $this->request->get['pincode'];
		// 	$api_url = "http://www.postalpincode.in/api/pincode/" . $pincode;

		// 	// Use cURL to fetch data from API (Recommended over file_get_contents)
		// 	$ch = curl_init();
		// 	curl_setopt($ch, CURLOPT_URL, $api_url);
		// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// 	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// 	$response = curl_exec($ch);
		// 	curl_close($ch);

		// 	// Convert JSON response to PHP array
		// 	$pincode_data = json_decode($response, true);

		// 	// Check if API returned valid data
		// 	if ($pincode_data['Status'] === "Success" && isset($pincode_data['PostOffice'][0])) {
		// 		$postOffice = $pincode_data['PostOffice'][0];

		// 		// Prepare response array
		// 		$result = [
		// 			'city' => $postOffice['District'],   // City
		// 			'zone' => $postOffice['State'],      // State
		// 			'country' => 'India'                 // Country (Fixed)
		// 		];
		// 	} else {
		// 		$result = [
		// 			'city' => 'Invalid Pincode',
		// 			'zone' => '',
		// 			'country' => ''
		// 		];
		// 	}

		// 	// Send JSON response
		// 	header('Content-Type: application/json');
		// 	echo json_encode($result);
		// 	exit();
		// }


		/*advance settings*/
		$data['required_displayname'] 	= $this->config->get('vendor_required_displayname');
		$data['required_lastname'] 		= $this->config->get('vendor_required_lastname');
		$data['required_telephone'] 	= $this->config->get('vendor_required_telephone');
		$data['required_fax'] 		    = $this->config->get('vendor_required_fax');
		$data['required_company'] 		= $this->config->get('vendor_required_company');
		$data['required_address_1'] 	= $this->config->get('vendor_required_address_1');
		$data['required_address_2'] 	= $this->config->get('vendor_required_address_2');
		$data['required_city'] 			= $this->config->get('vendor_required_city');
		$data['required_country'] 		= $this->config->get('vendor_required_country');
		$data['required_zone'] 			= $this->config->get('vendor_required_zone');
		$data['required_about'] 		= $this->config->get('vendor_required_about');
		$data['chkpostcode'] 			=  $this->config->get('vendor_vpostcode');

		$data['required_metadesc'] 		= $this->config->get('vendor_required_meta_description');
		$data['required_description'] 	= $this->config->get('vendor_required_description');
		$data['required_shipping_policy'] 		= $this->config->get('vendor_required_shipping_policy');
		$data['required_return_policy'] 		= $this->config->get('vendor_required_return_policy');
		$data['required_meta_keyword'] 		= $this->config->get('vendor_required_meta_keyword');

		$data['required_bank_detail'] 	= $this->config->get('vendor_required_bank_detail');
		$data['required_storeabout'] 	= $this->config->get('vendor_required_storeabout');
		$data['required_mapurl'] 		= $this->config->get('vendor_required_mapurl');
		$data['required_tax_number'] 	= $this->config->get('vendor_required_tax_number');
		$data['required_shipping'] 		= $this->config->get('vendor_required_shipping_charge');
		$data['required_url'] 		    = $this->config->get('vendor_required_url');


		$data['status_displayname']		= $this->config->get('vendor_status_displayname');
		$data['status_lastname']		= $this->config->get('vendor_status_lastname');
		$data['status_telephone']		= $this->config->get('vendor_status_telephone');
		$data['status_fax']				= $this->config->get('vendor_status_fax');
		$data['status_company']			= $this->config->get('vendor_status_company');
		$data['status_address_1']		= $this->config->get('vendor_status_address_1');
		$data['status_address_2']		= $this->config->get('vendor_status_address_2');
		$data['status_city']			= $this->config->get('vendor_status_city');
		$data['status_country']			= $this->config->get('vendor_status_country');
		$data['status_zone']			= $this->config->get('vendor_status_zone');
		$data['status_postcode']		= $this->config->get('vendor_status_postcode');
		$data['status_about']			= $this->config->get('vendor_status_about');
		$data['status_image']			= $this->config->get('vendor_status_image');

		$data['status_metadesc']		= $this->config->get('vendor_status_meta_description');
		$data['status_description']		= $this->config->get('vendor_status_description');
		$data['status_shipping_policy']	= $this->config->get('vendor_status_shipping_policy');
		$data['status_return_policy']	= $this->config->get('vendor_status_return_policy');
		$data['status_meta_keyword']	= $this->config->get('vendor_status_meta_keyword');

		$data['status_bank_detail']		= $this->config->get('vendor_status_bank_detail');
		$data['status_storeabout']		= $this->config->get('vendor_status_storeabout');
		$data['status_mapurl']			= $this->config->get('vendor_status_mapurl');
		$data['status_tax_number']		= $this->config->get('vendor_status_tax_number');
		$data['status_shipping_charge']	= $this->config->get('vendor_status_shipping_charge');
		$data['status_url']				= $this->config->get('vendor_status_url');
		$data['status_logo']			= $this->config->get('vendor_status_logo');
		$data['status_banner']			= $this->config->get('vendor_status_banner');
		$data['status_paypal']			= $this->config->get('vendor_status_paypal');
		$data['status_bank']		    = $this->config->get('vendor_status_bank');
		/*advance settings*/

		if (isset($this->request->post['firstname'])) {
			$data['firstname'] = $this->request->post['firstname'];
		} else {
			$data['firstname'] = '';
		}

		if (isset($this->request->post['lastname'])) {
			$data['lastname'] = $this->request->post['lastname'];
		} else {
			$data['lastname'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} else {
			$data['telephone'] = '';
		}

		if (isset($this->request->post['fax'])) {
			$data['fax'] = $this->request->post['fax'];
		} else {
			$data['fax'] = '';
		}

		if (isset($this->request->post['company'])) {
			$data['company'] = $this->request->post['company'];
		} else {
			$data['company'] = '';
		}

		if (isset($this->request->post['address_1'])) {
			$data['address_1'] = $this->request->post['address_1'];
		} else {
			$data['address_1'] = '';
		}

		if (isset($this->request->post['address_2'])) {
			$data['address_2'] = $this->request->post['address_2'];
		} else {
			$data['address_2'] = '';
		}

		if (isset($this->request->post['postcode'])) {
			$data['postcode'] = $this->request->post['postcode'];
		} elseif (isset($this->session->data['shipping_address']['postcode'])) {
			$data['postcode'] = $this->session->data['shipping_address']['postcode'];
		} else {
			$data['postcode'] = '';
		}

		if (isset($this->request->post['city'])) {
			$data['city'] = $this->request->post['city'];
		} else {
			$data['city'] = '';
		}
		
		if (isset($this->request->post['pan'])) {
			$data['pan'] = $this->request->post['pan'];
		} else {
			$data['pan'] ='';
		}

		if (isset($this->request->post['payment_method'])) {
			$data['payment_method'] = $this->request->post['payment_method'];
		} else {
			$data['payment_method'] = 'paypal';
		}

		if (isset($this->request->post['paypal'])) {
			$data['paypal'] = $this->request->post['paypal'];
		} else {
			$data['paypal'] = '';
		}

		if (isset($this->request->post['bank_name'])) {
			$data['bank_name'] = $this->request->post['bank_name'];
		} else {
			$data['bank_name'] = '';
		}

		if (isset($this->request->post['bank_branch_number'])) {
			$data['bank_branch_number'] = $this->request->post['bank_branch_number'];
		} else {
			$data['bank_branch_number'] = '';
		}

		if (isset($this->request->post['bank_swift_code'])) {
			$data['bank_swift_code'] = $this->request->post['bank_swift_code'];
		} else {
			$data['bank_swift_code'] = '';
		}

		if (isset($this->request->post['bank_account_name'])) {
			$data['bank_account_name'] = $this->request->post['bank_account_name'];
		} else {
			$data['bank_account_name'] = '';
		}

		if (isset($this->request->post['bank_account_number'])) {
			$data['bank_account_number'] = $this->request->post['bank_account_number'];
		} else {
			$data['bank_account_number'] = '';
		}

		if (isset($this->request->post['country_id'])) {
			$data['country_id'] = (int)$this->request->post['country_id'];
		} elseif (isset($this->session->data['shipping_address']['country_id'])) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->request->post['zone_id'])) {
			$data['zone_id'] = (int)$this->request->post['zone_id'];
		} elseif (isset($this->session->data['shipping_address']['zone_id'])) {
			$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$data['zone_id'] = '';
		}



		$this->load->model('localisation/country');
		$data['countries'] = $this->model_localisation_country->getCountries();

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('tool/image');
		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($store_info) && is_file(DIR_IMAGE . $store_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($store_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		//// Seller Store///

		if (isset($this->request->post['bank_detail'])) {
			$data['bank_detail'] = $this->request->post['bank_detail'];
		} else {
			$data['bank_detail'] = '';
		}

		if (isset($this->request->post['store_about'])) {
			$data['store_about'] = $this->request->post['store_about'];
		} else {
			$data['store_about'] = '';
		}

		if (isset($this->request->post['tax_number'])) {
			$data['tax_number'] = $this->request->post['tax_number'];
		} else {
			$data['tax_number'] = '';
		}

		if (isset($this->request->post['shipping_charge'])) {
			$data['shipping_charge'] = $this->request->post['shipping_charge'];
		} else {
			$data['shipping_charge'] = '';
		}
		/* 05 02 2021 replace keyword*/
		if (isset($this->request->post['vendor_seo_url'])) {
			$data['vendor_seo_url'] = $this->request->post['vendor_seo_url'];
		} else {
			$data['vendor_seo_url'] = array();
		}

		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		$this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		/* 05 02 2021 */
		if (isset($this->request->post['map_url'])) {
			$data['map_url'] = $this->request->post['map_url'];
		} else {
			$data['map_url'] = '';
		}
		if (isset($this->request->post['about'])) {
			$data['about'] = $this->request->post['about'];
		} else {
			$data['about'] = '';
		}


		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} else {
			$data['image'] = '';
		}

		if (isset($this->request->post['logo'])) {
			$data['logo'] = $this->request->post['logo'];
		} else {
			$data['logo'] = '';
		}

		if (isset($this->request->post['store_description'])) {
			$data['store_description'] = $this->request->post['store_description'];
		} else {
			$data['store_description'] = array();
		}

		if (isset($this->request->post['banner'])) {
			$data['banner'] = $this->request->post['banner'];
		} else {
			$data['banner'] = '';
		}

		$this->load->model('tool/image');
		if (isset($this->request->post['logo']) && is_file(DIR_IMAGE . $this->request->post['logo'])) {
			$data['thumb_logo'] = $this->model_tool_image->resize($this->request->post['logo'], 100, 100);
		} elseif (!empty($store_info) && is_file(DIR_IMAGE . $store_info['logo'])) {
			$data['thumb_logo'] = $this->model_tool_image->resize($store_info['logo'], 100, 100);
		} else {
			$data['thumb_logo'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		if (isset($this->request->post['banner']) && is_file(DIR_IMAGE . $this->request->post['banner'])) {
			$data['thumb_banner'] = $this->model_tool_image->resize($this->request->post['banner'], 100, 100);
		} elseif (!empty($store_info) && is_file(DIR_IMAGE . $store_info['banner'])) {
			$data['thumb_banner'] = $this->model_tool_image->resize($store_info['banner'], 100, 100);
		} else {
			$data['thumb_banner'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		/* 10 04 2020 */
		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}
		
		$data['password'] = isset($vendor_info['password']) ? $vendor_info['password'] : '';

		if (isset($this->request->post['confirm'])) {
			$data['confirm'] = $this->request->post['confirm'];
		} else {
			$data['confirm'] = '';
		}

		$data['vendor_vprivacy'] = $this->config->get('vendor_vprivacy_id');

		$this->load->model('catalog/information');
		$information_info = $this->model_catalog_information->getInformation($this->config->get('vendor_vprivacy_id'));
		if ($information_info) {
			$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('vendor_vprivacy_id'), true), $information_info['title'], $information_info['title']);
		} else {
			$data['text_agree'] = '';
		}

		if (isset($this->request->post['agree'])) {
			$data['agree'] = $this->request->post['agree'];
		} else {
			$data['agree'] = false;
		}
		/* 10 04 2020 */


		$data['column_left'] 	= $this->load->controller('vendor/column_left');
		$data['column_right'] 	= $this->load->controller('common/column_right');
		$data['content_top'] 	= $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] 		= $this->load->controller('common/footer');
		$data['header'] 		= $this->load->controller('common/header');


    
//     $data['banner_images'] = [
//     [
        
//         'src' => $this->model_tool_image->resize('catalog/banners/seller_registration/A.jpg', 320, 520),
//         'title' => 'mixer'
//     ],
//     [
        
//         'src' => $this->model_tool_image->resize('catalog/banners/seller_registration/01.jpg', 320, 520),
        
//     ],
   
// ];
$data['banner_images'] = [
    'tab-user' => [
        'left' => $this->model_tool_image->resize('catalog/banners/seller_registration/01.jpg', 320, 520),
        'right' => $this->model_tool_image->resize('catalog/banners/seller_registration/A.jpg', 320, 520)
    ],
    'tab-address' => [
        'left' => $this->model_tool_image->resize('catalog/banners/seller_registration/02.jpg', 320, 520),
        'right' => $this->model_tool_image->resize('catalog/banners/seller_registration/B.jpg', 320, 520)
    ],
    'tab-login' => [
        'left' => $this->model_tool_image->resize('catalog/banners/seller_registration/03.jpg', 320, 520),
        'right' => $this->model_tool_image->resize('catalog/banners/seller_registration/C.jpg', 320, 520)
    ],
    'tab-company' => [
        'left' => $this->model_tool_image->resize('catalog/banners/seller_registration/04.jpg', 320, 520),
        'right' => $this->model_tool_image->resize('catalog/banners/seller_registration/D.jpg', 320, 520)
    ],
    'tab-business' => [
        'left' => $this->model_tool_image->resize('catalog/banners/seller_registration/05.jpg', 320, 520),
        'right' => $this->model_tool_image->resize('catalog/banners/seller_registration/E.jpg', 320, 520)
    ],
    'tab-bank' => [
        'left' => $this->model_tool_image->resize('catalog/banners/seller_registration/05.jpg', 320, 520),
        'right' => $this->model_tool_image->resize('catalog/banners/seller_registration/E.jpg', 320, 520)
    ],
];
$data['registration_video']= $this->load->controller('common/video_popup', ['video_url' => 'https://www.youtube.com/embed/Sn0fUwBwhBs']);

		$this->response->setOutput($this->load->view('vendor/vendor', $data));
	}

// 	private function validate() {

// 		$displayname =  $this->config->get('vendor_required_displayname');
// 		$displaynamestatus =  $this->config->get('vendor_status_displayname');
// 		if ($displaynamestatus == 1) {
// 			if ($displayname == 1) {
// 				if ((utf8_strlen(trim($this->request->post['display_name'])) < 3) || (utf8_strlen(trim($this->request->post['display_name'])) > 32)) {
// 					$this->error['display_name'] = $this->language->get('error_display_name');
// 				}
// 			}
// 		}

// 		if ((utf8_strlen(trim($this->request->post['firstname'])) < 2) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
// 			$this->error['firstname'] = $this->language->get('error_firstname');
// 		}

// // 		$lastnamestatus =  $this->config->get('vendor_status_lastname');
// // 		$lastname =  $this->config->get('vendor_required_lastname');
// // 		if ($lastnamestatus == 1) {
// // 			if ($lastname == 1) {
// // 				if ((utf8_strlen(trim($this->request->post['lastname'])) < 3) || (utf8_strlen(trim($this->request->post['lastname'])) > 12)) {
// // 					$this->error['lastname'] = $this->language->get('error_lastname');
// // 				}
// // 			}
// // 		}

// 		$email_info = $this->model_vendor_vendor->getVendorByEmail($this->request->post['email']);

// 		if (!isset($this->request->get['vendor_id'])) {
// 			if ($email_info) {
// 				$this->error['warning'] = $this->language->get('error_email_match');
// 			}
// 		} else {
// 			if ($email_info && ($this->request->get['vendor_id'] != $email_info['vendor_id'])) {
// 				$this->error['warning'] = $this->language->get('error_email_match');
// 			}
// 		}

// // 		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
// // 			$this->error['email'] = $this->language->get('error_email');
// // 		}

//         $telephone_info = $this->model_vendor_vendor->getVendorByTelephone($this->request->post['telephone']);

// 		if (!isset($this->request->get['vendor_id'])) {
// 			if ($telephone_info) {
// 				$this->error['warning'] = $this->language->get('error_telephone_match');
// 			}
// 		} else {
// 			if ($telephone_info && ($this->request->get['vendor_id'] != $telephone_info['vendor_id'])) {
// 				$this->error['warning'] = $this->language->get('error_telephone_match');
// 			}
// 		}

// 		$telephonestatus =  $this->config->get('vendor_status_telephone');
// 		$telephone =  $this->config->get('vendor_required_telephone');
// 		if ($telephonestatus == 1) {
// 			if ($telephone == 1) {
// 				if ((utf8_strlen($this->request->post['telephone']) < 10) || (utf8_strlen($this->request->post['telephone']) > 10)) {
// 					$this->error['telephone'] = $this->language->get('error_telephone');
// 				}
// 			}
// 		}

// 		$faxstatus =  $this->config->get('vendor_status_fax');
// 		$fax =  $this->config->get('vendor_required_fax');
// 		// if ($faxstatus == 1) {
// 		// 	if ($fax == 1) {
// 		// 		$gst_number = strtoupper($this->request->post['fax']);
// 		// 		if ((utf8_strlen($this->request->post['fax']) != 15)) {
// 		// 			$this->error['fax'] = $this->language->get('error_gstin');
// 		// 		}
// 		// 	}
// 		// }
		
// // 		$gstin_info = $this->model_vendor_vendor->getVendorBygstin($this->request->post['fax']);

// // 		if (!isset($this->request->get['vendor_id'])) {
// // 			if ($gstin_info) {
// // 				$this->error['warning'] = $this->language->get('error_gstin_match');
// // 			}
// // 		} else {
// // 			if ($gstin_info && ($this->request->get['vendor_id'] != $gstin_info['vendor_id'])) {
// // 				$this->error['warning'] = $this->language->get('error_gstin_match');
// // 			}
// // 		}

//         if (isset($this->request->post['fax']) && !empty($this->request->post['fax'])) {
		
//     		$gstin_info = $this->model_vendor_vendor->getVendorBygstin($this->request->post['fax']);
    
//     		if (!isset($this->request->get['vendor_id'])) {
//     			if ($gstin_info) {
//     				$this->error['warning'] = $this->language->get('error_gstin_match');
//     			}
//     		} else {
//     			if ($gstin_info && ($this->request->get['vendor_id'] != $gstin_info['vendor_id'])) {
//     				$this->error['warning'] = $this->language->get('error_gstin_match');
//     			}
//     		}
// 		}

// 		$company =  $this->config->get('vendor_required_company');
// 		$companystatus =  $this->config->get('vendor_status_company');
// 		if ($companystatus == 1) {
// 			if ($company == 1) {
// 				if ((utf8_strlen($this->request->post['company']) > 255)) {
// 					$this->error['company'] = $this->language->get('error_company');
// 				}
// 			}
// 		}

// // 		$address_1status =  $this->config->get('vendor_status_address_1');
// // 		$address_1 =  $this->config->get('vendor_required_address_1');
// // 		if ($address_1status == 1) {
// // 			if ($address_1 == 1) {
// // 				if ((utf8_strlen(trim($this->request->post['address_1'])) < 10) || (utf8_strlen(trim($this->request->post['address_1'])) > 40)) {
// // 					$this->error['address_1'] = $this->language->get('error_address_1');
// // 				}
// // 			}
// // 		}

// // 		$address_2status =  $this->config->get('vendor_status_address_2');
// // 		$address_2 =  $this->config->get('vendor_required_address_2');
// // 		if ($address_2status == 1) {
// // 			if ($address_2 == 1) {
// // 				if ((utf8_strlen(trim($this->request->post['address_2'])) > 50)) {
// // 					$this->error['address_2'] = $this->language->get('error_address_2');
// // 				}
// // 			}
// // 		}

// // 		$citystatus =  $this->config->get('vendor_status_city');
// // 		$city =  $this->config->get('vendor_required_city');
// // 		if ($citystatus == 1) {
// // 			if ($city == 1) {
// // 				if ((utf8_strlen(trim($this->request->post['city'])) < 3) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
// // 					$this->error['city'] = $this->language->get('error_city');
// // 				}
// // 			}
// // 		}

// 		$this->load->model('localisation/country');
// 		if (isset($this->request->post['country_id'])) {
// 			$country_id = $this->request->post['country_id'];
// 		} else {
// 			$country_id = '';
// 		}
// 		$country_info = $this->model_localisation_country->getCountry($country_id);


// 		// if ($this->request->post['country_id'] == '') {
// 		// 	$this->error['country'] = $this->language->get('error_country');
// 		// }

// 		$zonestatus =  $this->config->get('vendor_status_zone');
// 		$zone =  $this->config->get('vendor_required_zone');
// 		// if ($zonestatus == 1) {
// 		// 	if ($zone == 1) {
// 		// 		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
// 		// 			$this->error['zone'] = $this->language->get('error_zone');
// 		// 		}
// 		// 	}
// 		// }


// 		if ((utf8_strlen($this->request->post['password']) < 8) || (utf8_strlen($this->request->post['password']) > 20)) {
// 			$this->error['password'] = $this->language->get('error_password');
// 		}

// 		if ($this->request->post['confirm'] != $this->request->post['password']) {
// 			$this->error['confirm'] = $this->language->get('error_confirm');
// 		}

// 		$aboutstatus =  $this->config->get('vendor_status_about');
// 		$about =  $this->config->get('vendor_required_about');
// 		// if ($aboutstatus == 1) {
// 		// 	if ($about == 1) {
// 		// 		if ((utf8_strlen(trim($this->request->post['about'])) < 2) || (utf8_strlen(trim($this->request->post['about'])) > 1000)) {
// 		// 			$this->error['about'] = $this->language->get('error_about');
// 		// 		}
// 		// 	}
// 		// }


// 		foreach ($this->request->post['store_description'] as $language_id => $value) {

// 			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 255)) {
// 				$this->error['name'][$language_id] = $this->language->get('error_name');
// 			}

// 			$meta_description =  $this->config->get('vendor_required_meta_description');
// 			$meta_descriptionstatus =  $this->config->get('vendor_status_meta_description');
// 			// if ($meta_descriptionstatus == 1) {
// 			// 	if ($meta_description == 1) {
// 			// 		if ((utf8_strlen($value['meta_description']) < 3) || (utf8_strlen($value['meta_description']) > 500)) {
// 			// 			$this->error['meta_description'][$language_id] = $this->language->get('error_meta_description');
// 			// 		}
// 			// 	}
// 			// }

// 			$description =  $this->config->get('vendor_required_description');
// 			$descriptionstatus =  $this->config->get('vendor_status_description');
// 			// if ($descriptionstatus == 1) {
// 			// 	if ($description == 1) {
// 			// 		if ((utf8_strlen($value['description']) < 3) || (utf8_strlen($value['description']) > 500)) {
// 			// 			$this->error['description'][$language_id] = $this->language->get('error_description');
// 			// 		}
// 			// 	}
// 			// }

// 			$shipping_policy =  $this->config->get('vendor_required_shipping_policy');
// 			$shipping_policystatus =  $this->config->get('vendor_status_shipping_policy');
// 			// if ($shipping_policystatus == 1) {
// 			// 	if ($shipping_policy == 1) {
// 			// 		if ((utf8_strlen($value['shipping_policy']) < 3) || (utf8_strlen($value['shipping_policy']) > 500)) {
// 			// 			$this->error['shipping_policy'][$language_id] = $this->language->get('error_shipping_policy');
// 			// 		}
// 			// 	}
// 			// }

// 			$return_policystatus =  $this->config->get('vendor_status_return_policy');
// 			$return_policy =  $this->config->get('vendor_required_return_policy');
// 			// if ($return_policystatus == 1) {
// 			// 	if ($return_policy == 1) {
// 			// 		if ((utf8_strlen($value['return_policy']) < 3) || (utf8_strlen($value['return_policy']) > 500)) {
// 			// 			$this->error['return_policy'][$language_id] = $this->language->get('error_return_policy');
// 			// 		}
// 			// 	}
// 			// }

// 			$meta_keyword =  $this->config->get('vendor_required_meta_keyword');
// 			$meta_keywordstatus =  $this->config->get('vendor_status_meta_keyword');
// 			// if ($meta_keywordstatus == 1) {
// 			// 	if ($meta_keyword == 1) {
// 			// 		if ((utf8_strlen($value['meta_keyword']) < 3) || (utf8_strlen($value['meta_keyword']) > 500)) {
// 			// 			$this->error['meta_keyword'][$language_id] = $this->language->get('error_meta_keyword');
// 			// 		}
// 			// 	}
// 			// }
// 		}

// 		$bank_detail =  $this->config->get('vendor_required_bank_detail');
// 		$bank_detailstatus =  $this->config->get('vendor_status_bank_detail');
// 		if ($bank_detailstatus == 1) {
// 			if ($bank_detail == 1) {
// 				if ((utf8_strlen(trim($this->request->post['bank_detail'])) < 2) || (utf8_strlen(trim($this->request->post['bank_detail'])) > 1000)) {
// 					$this->error['bank_detail'] = $this->language->get('error_bank_detail');
// 				}
// 			}
// 		}

// 		$storeabout =  $this->config->get('vendor_required_storeabout');
// 		$storeaboutstatus =  $this->config->get('vendor_status_storeabout');
// 		// if ($storeaboutstatus == 1) {
// 		// 	if ($storeabout == 1) {
// 		// 		if ((utf8_strlen(trim($this->request->post['store_about'])) < 2) || (utf8_strlen(trim($this->request->post['store_about'])) > 1000)) {
// 		// 			$this->error['store_about'] = $this->language->get('error_store_about');
// 		// 		}
// 		// 	}
// 		// }

// 		$map_url =  $this->config->get('vendor_required_mapurl');
// 		$map_urlstatus =  $this->config->get('vendor_status_mapurl');
// 		// if ($map_urlstatus == 1) {
// 		// 	if ($map_url == 1) {
// 		// 		if ((utf8_strlen(trim($this->request->post['map_url'])) < 2) || (utf8_strlen(trim($this->request->post['map_url'])) > 1000)) {
// 		// 			$this->error['map_url'] = $this->language->get('error_map_url');
// 		// 		}
// 		// 	}
// 		// }


// 		$tax_number =  $this->config->get('vendor_required_tax_number');
// 		$tax_numberstatus =  $this->config->get('vendor_status_tax_number');
// 		// if ($tax_numberstatus == 1) {
// 		// 	if ($tax_number == 1) {
// 		// 		if ((utf8_strlen(trim($this->request->post['tax_number'])) < 2) || (utf8_strlen(trim($this->request->post['tax_number'])) > 128)) {
// 		// 			$this->error['tax_number'] = $this->language->get('error_tax_number');
// 		// 		}
// 		// 	}
// 		// }

// 		$shipping_charge =  $this->config->get('vendor_required_shipping_charge');
// 		$shipping_chargestatus =  $this->config->get('vendor_status_shipping_charge');
// 		// if ($shipping_chargestatus == 1) {
// 		// 	if ($shipping_charge == 1) {
// 		// 		if ((utf8_strlen(trim($this->request->post['tax_number'])) < 2) || (utf8_strlen(trim($this->request->post['shipping_charge'])) > 128)) {
// 		// 			$this->error['shipping_charge'] = $this->language->get('error_shipping_charge');
// 		// 		}
// 		// 	}
// 		// }


// 		$statuspaypal = $this->config->get('vendor_status_paypal');
// 		// if ($statuspaypal == 1) {
// 		// 	if ($this->request->post['payment_method'] == 'paypal') {
// 		// 		if ($this->request->post['paypal'] == '') {
// 		// 			$this->error['paypal'] = $this->language->get('error_paypal');
// 		// 		}
// 		// 	} elseif ($this->request->post['payment_method'] == 'banktransfer') {
// 		// 		if ($this->request->post['bank_account_name'] == '') {
// 		// 			$this->error['bank_account_name'] = $this->language->get('error_bank_account_name');
// 		// 		}

// 		// 		if ($this->request->post['bank_account_number'] == '') {
// 		// 			$this->error['bank_account_number'] = $this->language->get('error_bank_account_number');
// 		// 		}
// 		// 	}
// 		// }

// 		$chkpostcode =  $this->config->get('vendor_vpostcode');
// 		$chkpostcodestatus =  $this->config->get('vendor_status_postcode');
// 		if ($chkpostcodestatus == 1) {
// 			if ($chkpostcode == 1) {
// 				if (empty($this->request->post['postcode'])) {
// 					$this->error['postcode'] = $this->language->get('error_postcode');
// 				}
// 			}
// 		}
// 		/* 24 03 2020 */
// 		if ($this->error) {
// // 			var_dump($this->error);
// 			$this->error['filedwarning'] =  $this->language->get('error_filedwarning');
// 		}
// 		/* 24 03 2020 */

// 		/* 10 04 2020 */

// 		$vendor_vprivacy = $this->config->get('vendor_vprivacy_id');
// 		if ($vendor_vprivacy != 0) {
// 			if ($this->config->get('vendor_vprivacy_id')) {
// 				$this->load->model('catalog/information');

// 				$information_info = $this->model_catalog_information->getInformation($this->config->get('vendor_vprivacy_id'));

// 				if ($information_info && !isset($this->request->post['agree'])) {
// 					$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
// 				}
// 			}
// 		}
// 		/* 10 04 2020 */

// 		/* 05 02 2021 */
// 		$vendorstatusurl = $this->config->get('vendor_status_url');
// 		$vendorrequiredurl = $this->config->get('vendor_required_url');
// 		if ($vendorstatusurl == 1) {
// 			if ($vendorrequiredurl == 1) {
// 				if ($this->request->post['vendor_seo_url']) {

// 					$this->load->model('vendor/seo_url');

// 					foreach ($this->request->post['vendor_seo_url'] as $store_id => $language) {
// 						foreach ($language as $language_id => $keyword) {
// 							if (!empty($keyword)) {
// 								if (count(array_keys($language, $keyword)) > 1) {
// 									$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
// 								}

// 								$seo_urls = $this->model_vendor_seo_url->getSeoUrlsByKeyword($keyword);

// 								foreach ($seo_urls as $seo_url) {
// 									if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['vendor_id']) || (($seo_url['query'] != 'vendor_id=' . $this->request->get['vendor_id'])))) {
// 										$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
// 									}
// 								}
// 							}
// 							if (empty($keyword)) {
// 								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_srequired');
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}

// 		/* 05 02 2021 */

// 		return !$this->error;
// 	}

    private function validate() {
    $this->load->model('vendor/vendor');

		$displayname =  $this->config->get('vendor_required_displayname');
		$displaynamestatus =  $this->config->get('vendor_status_displayname');
		if ($displaynamestatus == 1) {
			if ($displayname == 1) {
				if ((utf8_strlen(trim($this->request->post['display_name'])) < 3) || (utf8_strlen(trim($this->request->post['display_name'])) > 32)) {
					$this->error['display_name'] = $this->language->get('error_display_name');
				}
			}
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 2) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

// 		$lastnamestatus =  $this->config->get('vendor_status_lastname');
// 		$lastname =  $this->config->get('vendor_required_lastname');
// 		if ($lastnamestatus == 1) {
// 			if ($lastname == 1) {
// 				if ((utf8_strlen(trim($this->request->post['lastname'])) < 3) || (utf8_strlen(trim($this->request->post['lastname'])) > 12)) {
// 					$this->error['lastname'] = $this->language->get('error_lastname');
// 				}
// 			}
// 		}

		$email_info = $this->model_vendor_vendor->getVendorByEmail($this->request->post['email']);

		if (!isset($this->request->get['vendor_id'])) {
			if ($email_info) {
				$this->error['warning'] = $this->language->get('error_email_match');
			}
		} else {
			if ($email_info && ($this->request->get['vendor_id'] != $email_info['vendor_id'])) {
				$this->error['warning'] = $this->language->get('error_email_match');
			}
		}

// 		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
// 			$this->error['email'] = $this->language->get('error_email');
// 		}

        $telephone_info = $this->model_vendor_vendor->getVendorByTelephone($this->request->post['telephone']);

		if (!isset($this->request->get['vendor_id'])) {
			if ($telephone_info) {
				$this->error['warning'] = $this->language->get('error_telephone_match');
			}
		} else {
			if ($telephone_info && ($this->request->get['vendor_id'] != $telephone_info['vendor_id'])) {
				$this->error['warning'] = $this->language->get('error_telephone_match');
			}
		}

		$telephonestatus =  $this->config->get('vendor_status_telephone');
		$telephone =  $this->config->get('vendor_required_telephone');
		if ($telephonestatus == 1) {
			if ($telephone == 1) {
				if ((utf8_strlen($this->request->post['telephone']) < 10) || (utf8_strlen($this->request->post['telephone']) > 10)) {
					$this->error['telephone'] = $this->language->get('error_telephone');
				}
			}
		}

		$faxstatus =  $this->config->get('vendor_status_fax');
		$fax =  $this->config->get('vendor_required_fax');
		// if ($faxstatus == 1) {
		// 	if ($fax == 1) {
		// 		$gst_number = strtoupper($this->request->post['fax']);
		// 		if ((utf8_strlen($this->request->post['fax']) != 15)) {
		// 			$this->error['fax'] = $this->language->get('error_gstin');
		// 		}
		// 	}
		// }
		
// 		$gstin_info = $this->model_vendor_vendor->getVendorBygstin($this->request->post['fax']);

// 		if (!isset($this->request->get['vendor_id'])) {
// 			if ($gstin_info) {
// 				$this->error['warning'] = $this->language->get('error_gstin_match');
// 			}
// 		} else {
// 			if ($gstin_info && ($this->request->get['vendor_id'] != $gstin_info['vendor_id'])) {
// 				$this->error['warning'] = $this->language->get('error_gstin_match');
// 			}
// 		}

        // Skip GSTIN validation for AJAX requests (vendor registration)
        if (!isset($this->request->server['HTTP_X_REQUESTED_WITH']) || 
            $this->request->server['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
            
            if (isset($this->request->post['fax']) && !empty($this->request->post['fax'])) {
                $gstin_info = $this->model_vendor_vendor->getVendorBygstin($this->request->post['fax']);
        
                if (!isset($this->request->get['vendor_id'])) {
                    if ($gstin_info) {
                        $this->error['warning'] = $this->language->get('error_gstin_match');
                    }
                } else {
                    if ($gstin_info && ($this->request->get['vendor_id'] != $gstin_info['vendor_id'])) {
                        $this->error['warning'] = $this->language->get('error_gstin_match');
                    }
                }
            }
        }

		$company =  $this->config->get('vendor_required_company');
		$companystatus =  $this->config->get('vendor_status_company');
		if ($companystatus == 1) {
			if ($company == 1) {
				if ((utf8_strlen($this->request->post['company']) > 255)) {
					$this->error['company'] = $this->language->get('error_company');
				}
			}
		}

// 		$address_1status =  $this->config->get('vendor_status_address_1');
// 		$address_1 =  $this->config->get('vendor_required_address_1');
// 		if ($address_1status == 1) {
// 			if ($address_1 == 1) {
// 				if ((utf8_strlen(trim($this->request->post['address_1'])) < 10) || (utf8_strlen(trim($this->request->post['address_1'])) > 40)) {
// 					$this->error['address_1'] = $this->language->get('error_address_1');
// 				}
// 			}
// 		}

// 		$address_2status =  $this->config->get('vendor_status_address_2');
// 		$address_2 =  $this->config->get('vendor_required_address_2');
// 		if ($address_2status == 1) {
// 			if ($address_2 == 1) {
// 				if ((utf8_strlen(trim($this->request->post['address_2'])) > 50)) {
// 					$this->error['address_2'] = $this->language->get('error_address_2');
// 				}
// 			}
// 		}

// 		$citystatus =  $this->config->get('vendor_status_city');
// 		$city =  $this->config->get('vendor_required_city');
// 		if ($citystatus == 1) {
// 			if ($city == 1) {
// 				if ((utf8_strlen(trim($this->request->post['city'])) < 3) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
// 					$this->error['city'] = $this->language->get('error_city');
// 				}
// 			}
// 		}

		$this->load->model('localisation/country');
		if (isset($this->request->post['country_id'])) {
			$country_id = $this->request->post['country_id'];
		} else {
			$country_id = '';
		}
		$country_info = $this->model_localisation_country->getCountry($country_id);


		// if ($this->request->post['country_id'] == '') {
		// 	$this->error['country'] = $this->language->get('error_country');
		// }

		$zonestatus =  $this->config->get('vendor_status_zone');
		$zone =  $this->config->get('vendor_required_zone');
		// if ($zonestatus == 1) {
		// 	if ($zone == 1) {
		// 		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
		// 			$this->error['zone'] = $this->language->get('error_zone');
		// 		}
		// 	}
		// }


		if ((utf8_strlen($this->request->post['password']) < 8) || (utf8_strlen($this->request->post['password']) > 20)) {
			$this->error['password'] = $this->language->get('error_password');
		}

		if ($this->request->post['confirm'] != $this->request->post['password']) {
			$this->error['confirm'] = $this->language->get('error_confirm');
		}

		$aboutstatus =  $this->config->get('vendor_status_about');
		$about =  $this->config->get('vendor_required_about');
		// if ($aboutstatus == 1) {
		// 	if ($about == 1) {
		// 		if ((utf8_strlen(trim($this->request->post['about'])) < 2) || (utf8_strlen(trim($this->request->post['about'])) > 1000)) {
		// 			$this->error['about'] = $this->language->get('error_about');
		// 		}
		// 	}
		// }


		foreach ($this->request->post['store_description'] as $language_id => $value) {

			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}

			$meta_description =  $this->config->get('vendor_required_meta_description');
			$meta_descriptionstatus =  $this->config->get('vendor_status_meta_description');
			// if ($meta_descriptionstatus == 1) {
			// 	if ($meta_description == 1) {
			// 		if ((utf8_strlen($value['meta_description']) < 3) || (utf8_strlen($value['meta_description']) > 500)) {
			// 			$this->error['meta_description'][$language_id] = $this->language->get('error_meta_description');
			// 		}
			// 	}
			// }

			$description =  $this->config->get('vendor_required_description');
			$descriptionstatus =  $this->config->get('vendor_status_description');
			// if ($descriptionstatus == 1) {
			// 	if ($description == 1) {
			// 		if ((utf8_strlen($value['description']) < 3) || (utf8_strlen($value['description']) > 500)) {
			// 			$this->error['description'][$language_id] = $this->language->get('error_description');
			// 		}
			// 	}
			// }

			$shipping_policy =  $this->config->get('vendor_required_shipping_policy');
			$shipping_policystatus =  $this->config->get('vendor_status_shipping_policy');
			// if ($shipping_policystatus == 1) {
			// 	if ($shipping_policy == 1) {
			// 		if ((utf8_strlen($value['shipping_policy']) < 3) || (utf8_strlen($value['shipping_policy']) > 500)) {
			// 			$this->error['shipping_policy'][$language_id] = $this->language->get('error_shipping_policy');
			// 		}
			// 	}
			// }

			$return_policystatus =  $this->config->get('vendor_status_return_policy');
			$return_policy =  $this->config->get('vendor_required_return_policy');
			// if ($return_policystatus == 1) {
			// 	if ($return_policy == 1) {
			// 		if ((utf8_strlen($value['return_policy']) < 3) || (utf8_strlen($value['return_policy']) > 500)) {
			// 			$this->error['return_policy'][$language_id] = $this->language->get('error_return_policy');
			// 		}
			// 	}
			// }

			$meta_keyword =  $this->config->get('vendor_required_meta_keyword');
			$meta_keywordstatus =  $this->config->get('vendor_status_meta_keyword');
			// if ($meta_keywordstatus == 1) {
			// 	if ($meta_keyword == 1) {
			// 		if ((utf8_strlen($value['meta_keyword']) < 3) || (utf8_strlen($value['meta_keyword']) > 500)) {
			// 			$this->error['meta_keyword'][$language_id] = $this->language->get('error_meta_keyword');
			// 		}
			// 	}
			// }
		}

		$bank_detail =  $this->config->get('vendor_required_bank_detail');
		$bank_detailstatus =  $this->config->get('vendor_status_bank_detail');
		if ($bank_detailstatus == 1) {
			if ($bank_detail == 1) {
				if ((utf8_strlen(trim($this->request->post['bank_detail'])) < 2) || (utf8_strlen(trim($this->request->post['bank_detail'])) > 1000)) {
					$this->error['bank_detail'] = $this->language->get('error_bank_detail');
				}
			}
		}

		$storeabout =  $this->config->get('vendor_required_storeabout');
		$storeaboutstatus =  $this->config->get('vendor_status_storeabout');
		// if ($storeaboutstatus == 1) {
		// 	if ($storeabout == 1) {
		// 		if ((utf8_strlen(trim($this->request->post['store_about'])) < 2) || (utf8_strlen(trim($this->request->post['store_about'])) > 1000)) {
		// 			$this->error['store_about'] = $this->language->get('error_store_about');
		// 		}
		// 	}
		// }

		$map_url =  $this->config->get('vendor_required_mapurl');
		$map_urlstatus =  $this->config->get('vendor_status_mapurl');
		// if ($map_urlstatus == 1) {
		// 	if ($map_url == 1) {
		// 		if ((utf8_strlen(trim($this->request->post['map_url'])) < 2) || (utf8_strlen(trim($this->request->post['map_url'])) > 1000)) {
		// 			$this->error['map_url'] = $this->language->get('error_map_url');
		// 		}
		// 	}
		// }


		$tax_number =  $this->config->get('vendor_required_tax_number');
		$tax_numberstatus =  $this->config->get('vendor_status_tax_number');
		// if ($tax_numberstatus == 1) {
		// 	if ($tax_number == 1) {
		// 		if ((utf8_strlen(trim($this->request->post['tax_number'])) < 2) || (utf8_strlen(trim($this->request->post['tax_number'])) > 128)) {
		// 			$this->error['tax_number'] = $this->language->get('error_tax_number');
		// 		}
		// 	}
		// }

		$shipping_charge =  $this->config->get('vendor_required_shipping_charge');
		$shipping_chargestatus =  $this->config->get('vendor_status_shipping_charge');
		// if ($shipping_chargestatus == 1) {
		// 	if ($shipping_charge == 1) {
		// 		if ((utf8_strlen(trim($this->request->post['tax_number'])) < 2) || (utf8_strlen(trim($this->request->post['shipping_charge'])) > 128)) {
		// 			$this->error['shipping_charge'] = $this->language->get('error_shipping_charge');
		// 		}
		// 	}
		// }


		$statuspaypal = $this->config->get('vendor_status_paypal');
		// if ($statuspaypal == 1) {
		// 	if ($this->request->post['payment_method'] == 'paypal') {
		// 		if ($this->request->post['paypal'] == '') {
		// 			$this->error['paypal'] = $this->language->get('error_paypal');
		// 		}
		// 	} elseif ($this->request->post['payment_method'] == 'banktransfer') {
		// 		if ($this->request->post['bank_account_name'] == '') {
		// 			$this->error['bank_account_name'] = $this->language->get('error_bank_account_name');
		// 		}

		// 		if ($this->request->post['bank_account_number'] == '') {
		// 			$this->error['bank_account_number'] = $this->language->get('error_bank_account_number');
		// 		}
		// 	}
		// }

		$chkpostcode =  $this->config->get('vendor_vpostcode');
		$chkpostcodestatus =  $this->config->get('vendor_status_postcode');
		if ($chkpostcodestatus == 1) {
			if ($chkpostcode == 1) {
				if (empty($this->request->post['postcode'])) {
					$this->error['postcode'] = $this->language->get('error_postcode');
				}
			}
		}
		/* 24 03 2020 */
		// Skip filedwarning for AJAX requests (vendor registration)
		if ($this->error && (!isset($this->request->server['HTTP_X_REQUESTED_WITH']) || 
		    $this->request->server['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')) {
// 			var_dump($this->error);
			$this->error['filedwarning'] =  $this->language->get('error_filedwarning');
		}
		/* 24 03 2020 */

		/* 10 04 2020 */

		$vendor_vprivacy = $this->config->get('vendor_vprivacy_id');
		if ($vendor_vprivacy != 0) {
			if ($this->config->get('vendor_vprivacy_id')) {
				$this->load->model('catalog/information');

				$information_info = $this->model_catalog_information->getInformation($this->config->get('vendor_vprivacy_id'));

				if ($information_info && !isset($this->request->post['agree'])) {
					$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
				}
			}
		}
		/* 10 04 2020 */

		/* 05 02 2021 */
		$vendorstatusurl = $this->config->get('vendor_status_url');
		$vendorrequiredurl = $this->config->get('vendor_required_url');
		if ($vendorstatusurl == 1) {
			if ($vendorrequiredurl == 1) {
				if ($this->request->post['vendor_seo_url']) {

					$this->load->model('vendor/seo_url');

					foreach ($this->request->post['vendor_seo_url'] as $store_id => $language) {
						foreach ($language as $language_id => $keyword) {
							if (!empty($keyword)) {
								if (count(array_keys($language, $keyword)) > 1) {
									$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
								}

								$seo_urls = $this->model_vendor_seo_url->getSeoUrlsByKeyword($keyword);

								foreach ($seo_urls as $seo_url) {
									if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['vendor_id']) || (($seo_url['query'] != 'vendor_id=' . $this->request->get['vendor_id'])))) {
										$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
									}
								}
							}
							if (empty($keyword)) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_srequired');
							}
						}
					}
				}
			}
		}

		/* 05 02 2021 */

		return !$this->error;
	}
	

	public function autocomplete() {

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'firstname';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		$this->load->model('vendor/vendor');

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			//'filter_name' => $filter_name,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		$results = $this->model_vendor_vendor->getVendors($filter_data);

		foreach ($results as $result) {

			$json[] = array(
				'vendor_id'  => $result['vendor_id'],
				'firstname'   => strip_tags(html_entity_decode($result['firstname'], ENT_QUOTES, 'UTF-8'))
			);
		}
		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['firstname'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function upload() {
		$this->load->language('tool/upload');
		$json = array();
		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
			// Sanitize the filename
			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));
			// Validate the filename length
			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
				$json['error'] = $this->language->get('error_filename');
			}
			// Allowed file extension types
			$allowed = array();
			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));
			$filetypes = explode("\n", $extension_allowed);
			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}
			if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}
			// Allowed file mime types
			$allowed = array();
			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));
			$filetypes = explode("\n", $mime_allowed);
			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}
			if (!in_array($this->request->files['file']['type'], $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}
			// Check to see if any PHP files are trying to be uploaded
			$content = file_get_contents($this->request->files['file']['tmp_name']);
			if (preg_match('/\<\?php/i', $content)) {
				$json['error'] = $this->language->get('error_filetype');
			}
			// Return any upload error
			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		$this->load->model('tool/image');
		if (!$json) {
			$targetDir = DIR_IMAGE . 'catalog/multivendor/' . $this->vendor->getId() . '/';
			$file = $filename;
			$location = $targetDir . $file;
			$location1 = 'catalog/multivendor/' . $this->vendor->getId() . '/' . $file;
			$location2 = 'catalog/multivendor/' . $this->vendor->getId() . '/' . $file;
			move_uploaded_file($this->request->files['file']['tmp_name'], $location);
			$json['filename'] = $filename;
			$json['location1'] = $location1;
			$json['location2'] = $this->model_tool_image->resize($location1, 150, 150);
			$json['success'] = $this->language->get('text_upload');
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function fetchInfo() {
        $this->response->addHeader('Content-Type: application/json');
        $pincode = $this->request->get['pincode'] ?? '';
    
        if (strlen($pincode) !== 6) {
            $this->response->setOutput(json_encode([
                'success' => false,
                'message' => 'Invalid Pincode Format'
            ]));
            return;
        }
    
        // Use fallback DB
        $query = $this->db->query("SELECT city, state FROM " . DB_PREFIX . "city_pincode WHERE pincode = '" . $this->db->escape($pincode) . "' LIMIT 1");
    
        if ($query->num_rows) {
            $this->response->setOutput(json_encode([
                'success' => true,
                'city'    => $query->row['city'],
                'state'   => $query->row['state'],
                'source'  => 'database'
            ]));
        } else {
            $this->response->setOutput(json_encode([
                'success' => false,
                'message' => 'City and State not found for this pincode'
            ]));
        }
    }
    


     // nikita google ads auto create logic start -12/10/2025--------
     


     public function processGoogleAdsIntegration($data) {
    // Call the microservice API instead of local function
    $googleAdsServiceUrl = 'https://gads.ipshopy.com/api/google-ads/autoSetupGoogleAds';
    
    $apiData = array(
        'vendorId' => $data['vendor_id'],
        'email' => $data['email'],
        'firstName' => $data['firstname'],
        'lastName' => $data['lastname'],
        'companyName' => $data['company'],
        'phone' => $data['telephone']
    );
    
    // Make API call to Google Ads service
    $ch = curl_init($googleAdsServiceUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apiData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $result = json_decode($response, true);
        if ($result && isset($result['success']) && $result['success']) {
            // Update vendor with Google Ads info
            $this->model_vendor_vendor->updateVendorGoogleAdsInfo(
                $data['vendor_id'],
                $result['data']['customerId'],
                'ACTIVE'
            );
            
            $this->log->write('Google Ads account created for vendor: ' . $data['email']);
            return true;
        }
    }
    
    $this->log->write('Failed to create Google Ads account for vendor: ' . $data['email']);
    return false;
}


     public function googleAdsAutoCreate() {
    $this->response->addHeader('Content-Type: application/json');
    
    if ($this->request->server['REQUEST_METHOD'] != 'POST') {
        $this->response->setOutput(json_encode([
            'success' => false,
            'error' => 'Invalid request method'
        ]));
        return;
    }
    
    $vendor_id = $this->request->post['vendor_id'] ?? null;
    $company_name = $this->request->post['company_name'] ?? 'Unknown Company';
    
    if (!$vendor_id) {
        $this->response->setOutput(json_encode([
            'success' => false,
            'error' => 'Vendor ID is required'
        ]));
        return;
    }
    
    try {
        // Load vendor model
        $this->load->model('vendor/vendor');
        
        // Fetch full vendor data (FIX: This was missing)
        $vendor_data = $this->model_vendor_vendor->getVendor($vendor_id);
        
        if (!$vendor_data) {
            throw new Exception('Vendor not found');
        }
        
        // Update vendor status to 'pending'
        $this->model_vendor_vendor->updateGoogleAdsInfo($vendor_id, null, 'pending');
        
        // Prepare payload for microservice (using fetched vendor_data)
        $api_url = 'https://gads.ipshopy.com/api/google-ads/autoSetupGoogleAds';
        
        $post_data = [
            'vendorId' => $vendor_id,
            'descriptiveName' => $company_name,
            'currency_code' => 'INR',
            'timeZone' => 'Asia/Kolkata',
            'email' => $vendor_data['email'],
            'firstName' => $vendor_data['firstname'],
            'lastName' => $vendor_data['lastname'],
            'phone' => $vendor_data['telephone'],
            'address' => [
                'address_1' => $vendor_data['address_1'],
                'city' => $vendor_data['city'],
                'postcode' => $vendor_data['postcode'],
                'country_id' => $vendor_data['country_id']
            ]
        ];
        
        // cURL setup
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($curl_error) {
            error_log("[GoogleAds] cURL Error for vendor_id $vendor_id: " . $curl_error);
            $this->model_vendor_vendor->updateGoogleAdsInfo($vendor_id, null, 'error');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Failed to connect to Ipshopy Ads service'
            ]));
            return;
        }
        
        // Decode JSON response
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("[GoogleAds] JSON Decode Error for vendor_id $vendor_id: " . json_last_error_msg());
            $this->model_vendor_vendor->updateGoogleAdsInfo($vendor_id, null, 'error');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Invalid response from Ipshopy Ads service'
            ]));
            return;
        }
        
        // Log response for debugging
        error_log("[GoogleAds] API Response for vendor_id $vendor_id: " . print_r($result, true));
        
        // Check API response
        if (!isset($result['success'])) {
            error_log("[GoogleAds] Missing 'success' in response for vendor_id $vendor_id");
            $this->model_vendor_vendor->updateGoogleAdsInfo($vendor_id, null, 'error');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Invalid response format from Ipshopy Ads service'
            ]));
            return;
        }
        
        if ($result['success'] === true) {
            // Get session ID for tracking
            $session_id = $result['data']['session_id'] ?? null;
            $status = $result['data']['status'] ?? 'processing';
            
            if ($session_id) {
                // Store session ID temporarily and set status to processing
                $this->model_vendor_vendor->updateGoogleAdsInfo($vendor_id, $session_id, 'processing');
                error_log("[GoogleAds] Setup started for vendor_id $vendor_id with session: $session_id");
                
                $this->response->setOutput(json_encode([
                    'success' => true,
                    'session_id' => $session_id,
                    'status' => $status,
                    'message' => 'Ipshopy Ads setup initiated successfully'
                ]));
            } else {
                error_log("[GoogleAds] No session ID in response for vendor_id $vendor_id");
                $this->model_vendor_vendor->updateGoogleAdsInfo($vendor_id, null, 'error');
                $this->response->setOutput(json_encode([
                    'success' => false,
                    'error' => 'Failed to get session ID from Ipshopy Ads service'
                ]));
            }
        } else {
            // API returned success: false
            $error_message = $result['error'] ?? 'Unknown error from Ipshopy Ads service';
            error_log("[GoogleAds] API Error for vendor_id $vendor_id: " . $error_message);
            $this->model_vendor_vendor->updateGoogleAdsInfo($vendor_id, null, 'error');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $error_message
            ]));
        }
        
    } catch (Exception $e) {
        error_log("[GoogleAds] Exception in googleAdsAutoCreate for vendor_id $vendor_id: " . $e->getMessage());
        $this->response->setOutput(json_encode([
            'success' => false,
            'error' => 'Internal server error: ' . $e->getMessage()
        ]));
    }
}

      public function googleAdsStatus() {
    // Suppress PHP warnings/notices
    error_reporting(0);
    ini_set('display_errors', 0);
    
    // Start output buffering
    ob_start();
    
    $this->response->addHeader('Content-Type: application/json');
    
    $session_id = $this->request->get['session_id'] ?? null;
    
    if (!$session_id) {
        ob_clean();
        $this->response->setOutput(json_encode([
            'success' => false,
            'error' => 'Session ID is required'
        ]));
        return;
    }
    
    try {
        // Call microservice status endpoint
        $api_url = 'https://gads.ipshopy.com/api/google-ads/status/' . $session_id;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            error_log("[GoogleAds] cURL Error in googleAdsStatus for session $session_id: " . $curl_error);
            ob_clean();
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Failed to connect to Ipshopy Ads service'
            ]));
            return;
        }
        
        // Decode response
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("[GoogleAds] JSON Decode Error in ipshopyAdsStatus for session $session_id: " . json_last_error_msg());
            ob_clean();
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Invalid response from Ipshopy Ads service'
            ]));
            return;
        }
        
        // Log for debugging
        error_log("[GoogleAds] Status response for session $session_id: " . print_r($result, true));
        
        if ($http_code === 200 && isset($result['success']) && $result['success'] && isset($result['data']['status'])) {
            $status = $result['data']['status'];
            
            // Load model to handle DB updates
            $this->load->model('vendor/vendor');
            
            // Find vendor_id by session_id
            $vendor_id = $this->model_vendor_vendor->getVendorIdByGoogleAdsSession($session_id);
            
            if ($vendor_id) {
                if ($status === 'completed') {
                    $customer_id = $result['data']['customer_id'] ?? null;
                    if ($customer_id) {
                        $this->model_vendor_vendor->updateGoogleAdsInfo($vendor_id, $customer_id, 'ACTIVE');
                        error_log("[GoogleAds] Updated vendor $vendor_id to connected with customer_id $customer_id");
                    }
                } elseif ($status === 'failed') {
                    $error_msg = $result['data']['error'] ?? 'Unknown failure';
                    $this->model_vendor_vendor->updateGoogleAdsInfo($vendor_id, null, 'failed');
                    error_log("[GoogleAds] Failed for vendor $vendor_id: $error_msg");
                }
            } else {
                error_log("[GoogleAds] No vendor found for session $session_id");
            }
            
            ob_clean();
            $this->response->setOutput(json_encode($result));
        } else {
            error_log("[GoogleAds] Invalid response for session $session_id: HTTP $http_code");
            ob_clean();
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => 'Failed to get status from Ipshopy Ads service'
            ]));
        }
        
    } catch (Exception $e) {
        error_log("[GoogleAds] Exception in Ipshopyads status for session $session_id: " . $e->getMessage());
        ob_clean();
        $this->response->setOutput(json_encode([
            'success' => false,
            'error' => 'Status check failed: ' . $e->getMessage()
        ]));
    }
}
         private function callVendorServiceRegistration($vendorData) {
		$vendorServiceUrl = 'https://api.ipshopy.com/vendors/register';
		
		// Prepare data for vendor-service microservice API
		$apiData = array(
			'firstname' => $vendorData['firstname'],
			'lastname' => $vendorData['lastname'],
			'email' => $vendorData['email'],
			'telephone' => $vendorData['telephone'],
			'company' => $vendorData['company'],
			'address_1' => $vendorData['address_1'],
			'address_2' => isset($vendorData['address_2']) ? $vendorData['address_2'] : '',
			'city' => $vendorData['city'],
			'postcode' => $vendorData['postcode'],
			'country_id' => $vendorData['country_id'],
			'zone_id' => $vendorData['zone_id'],
			'password' => $vendorData['password'],
			'fax' => isset($vendorData['fax']) ? $vendorData['fax'] : '', // GSTIN
			'pan' => isset($vendorData['pan']) ? $vendorData['pan'] : '',
			'payment_method' => isset($vendorData['payment_method']) ? $vendorData['payment_method'] : 'paypal',
			'paypal' => isset($vendorData['paypal']) ? $vendorData['paypal'] : '',
			'bank_name' => isset($vendorData['bank_name']) ? $vendorData['bank_name'] : '',
			'bank_account_name' => isset($vendorData['bank_account_name']) ? $vendorData['bank_account_name'] : '',
			'bank_account_number' => isset($vendorData['bank_account_number']) ? $vendorData['bank_account_number'] : '',
			'google_ads_customer_id' => isset($vendorData['google_ads_customer_id']) ? $vendorData['google_ads_customer_id'] : null,
			'store_description' => isset($vendorData['store_description']) ? $vendorData['store_description'] : array(),
			'auto_create_google_ads' => true, // Enable automatic Google Ads account creation
			'source' => 'vendor_php' // Track registration source
		);
		
		// Make cURL request to vendor-service microservice
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $vendorServiceUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apiData));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 45); // Increased timeout for Google Ads API calls
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Accept: application/json',
			'User-Agent: IPShopy-VendorPHP/1.0'
		));
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_error($ch);
		curl_close($ch);
		
		if ($error) {
			error_log("Vendor-service API call failed: " . $error);
			return false;
		}
		
		if ($httpCode !== 201) {
			error_log("Vendor-service API returned HTTP " . $httpCode . ": " . $response);
			return false;
		}
		
		$responseData = json_decode($response, true);
		
		// Log successful Google Ads integration
		if (isset($responseData['data']['google_ads_customer_id'])) {
			error_log("Ipshopy Ads account created/connected: " . $responseData['data']['google_ads_customer_id']);
		}
		
		return $responseData;
	}
	
     	private function sendVendorRegistrationEvent($vendorId, $email, $googleAdsStatus) {
		$kafkaUrl = 'https://api.ipshopy.com/kafka/send-event';
		
		$eventData = array(
			'topic' => 'vendor-events',
			'event' => 'vendor_registered_php',
			'vendor_id' => $vendorId,
			'email' => $email,
			'google_ads_status' => $googleAdsStatus,
			'source' => 'vendor.php',
			'timestamp' => date('c')
		);
		
		// Make async cURL request (fire and forget)
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $kafkaUrl);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($eventData));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Short timeout for async call
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json'
		));
		
		$response = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);
		
		if ($error) {
			error_log("Kafka event send failed: " . $error);
		}
	}

public function checkGstinExist() {
    $this->load->model('vendor/vendor');
    
    $json = [];

    if (isset($this->request->get['fax']) && !empty($this->request->get['fax'])) {
        $gstin = $this->request->get['fax'];
        $vendor = $this->model_vendor_vendor->getVendorBygstin($gstin);

        if ($vendor) {
            $json['exists'] = true;
        } else {
            $json['exists'] = false;
        }
    } else {
        $json['exists'] = false;
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}    



// nikita added for token and session of auto logout

//  Add method to generate authentication token for vendor
private function generateVendorAuthToken($vendor_id) {
    // Load the model to interact with the database
    $this->load->model('vendor/vendor');
    
    // Generate a secure token
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store token in database
    $this->model_vendor_vendor->storeVendorAuthToken($vendor_id, $token, $expires_at);
    
    // Store in session
    $this->session->data['vendor_auth_token'] = $token;
    $this->session->data['vendor_id'] = $vendor_id;
    
    return $token;
}

// Add method to verify vendor authentication token
public function verifyVendorAuthToken($token) {
    $this->load->model('vendor/vendor');
    
    // Check if token exists and is valid
    $token_data = $this->model_vendor_vendor->getVendorAuthToken($token);
    
    if (!$token_data) {
        return false;
    }
    
    // Check if token is expired
    if (strtotime($token_data['expires_at']) < time()) {
        // Remove expired token
        $this->model_vendor_vendor->removeVendorAuthToken($token);
        return false;
    }
    
    return true;
}

// Add method to check if vendor is authenticated
public function isVendorAuthenticated() {
    // Check if vendor is logged in
    if (!$this->vendor->isLogged()) {
        return false;
    }
    
    // Check if authentication token exists in session or cookie
    $token = null;
    if (isset($this->session->data['vendor_auth_token'])) {
        $token = $this->session->data['vendor_auth_token'];
    } elseif (isset($_COOKIE['vendor_auth_token'])) {
        $token = $_COOKIE['vendor_auth_token'];
    }
    
    if (!$token) {
        return false;
    }
    
    // Verify token
    return $this->verifyVendorAuthToken($token);
}

// Add method to validate vendor access to specific resources
public function validateVendorAccess($requested_vendor_id) {
    // Check if vendor is authenticated
    if (!$this->isVendorAuthenticated()) {
        return false;
    }
    
    // Check if the requested vendor ID matches the authenticated vendor ID
    $authenticated_vendor_id = $this->session->data['vendor_id'];
    
    if ($requested_vendor_id != $authenticated_vendor_id) {
        // Log unauthorized access attempt
        error_log("Unauthorized access attempt: Vendor $authenticated_vendor_id trying to access vendor $requested_vendor_id");
        return false;
    }
    
    return true;
}

// Add method to generate JWT token for Google Ads integration
public function generateGoogleAdsAuthToken($vendor_id) {
    // Load required libraries
    $this->load->model('vendor/vendor');
    
    // Get vendor information
    $vendor_info = $this->model_vendor_vendor->getVendor($vendor_id);
    
    if (!$vendor_info) {
        return false;
    }
    
    // Create payload for JWT token
    $payload = array(
        'vendor_id' => $vendor_id,
        'email' => $vendor_info['email'],
        'exp' => time() + 3600, // Token expires in 1 hour
        'iat' => time(),
        'scope' => 'google-ads-api'
    );
    
    // Generate JWT token (using a simple approach since we don't have the JWT library)
    $header = base64_encode(json_encode(array('typ' => 'JWT', 'alg' => 'HS256')));
    $payload_encoded = base64_encode(json_encode($payload));
    $signature = base64_encode(hash_hmac('sha256', $header . "." . $payload_encoded, 'JWT_SECRET_KEY', true));
    
    $jwt_token = $header . "." . $payload_encoded . "." . $signature;
    
    // Store token in database
    $this->model_vendor_vendor->updateVendorGoogleAdsInfo($vendor_id, null, 'active', $jwt_token);
    
    return $jwt_token;
}

// Add method to verify Google Ads JWT token
public function verifyGoogleAdsAuthToken($token) {
    // Split the token
    $token_parts = explode('.', $token);
    
    if (count($token_parts) != 3) {
        return false;
    }
    
    // Verify signature
    $header = $token_parts[0];
    $payload = $token_parts[1];
    $signature = $token_parts[2];
    
    $expected_signature = base64_encode(hash_hmac('sha256', $header . "." . $payload, 'JWT_SECRET_KEY', true));
    
    if ($signature !== $expected_signature) {
        return false;
    }
    
    // Decode payload
    $payload_data = json_decode(base64_decode($payload), true);
    
    // Check expiration
    if (isset($payload_data['exp']) && $payload_data['exp'] < time()) {
        return false;
    }
    
    // Verify vendor exists and token matches
    $this->load->model('vendor/vendor');
    $vendor_info = $this->model_vendor_vendor->getVendor($payload_data['vendor_id']);
    
    if (!$vendor_info || !isset($vendor_info['google_ads_jwt_token']) || $vendor_info['google_ads_jwt_token'] !== $token) {
        return false;
    }
    
    return $payload_data;
}

// Add method to invalidate Google Ads token
public function invalidateGoogleAdsAuthToken($vendor_id) {
    $this->load->model('vendor/vendor');
    $this->model_vendor_vendor->updateVendorGoogleAdsInfo($vendor_id, null, 'inactive', null);
}

// Add method to handle secure logout
public function secureLogout() {
    // Get vendor ID before logout
    $vendor_id = $this->vendor->getId();
    
    // Invalidate Google Ads token if exists
    if ($vendor_id) {
        $this->invalidateGoogleAdsAuthToken($vendor_id);
    }
    
    // Remove token from session
    if (isset($this->session->data['vendor_auth_token'])) {
        $token = $this->session->data['vendor_auth_token'];
        
        // Remove token from database
        $this->load->model('vendor/vendor');
        $this->model_vendor_vendor->removeVendorAuthToken($token);
        
        // Remove from session
        unset($this->session->data['vendor_auth_token']);
    }
    
    // Remove cookie
    if (isset($_COOKIE['vendor_auth_token'])) {
        setcookie('vendor_auth_token', '', time() - 3600, '/', '.ipshopy.com', true, true);
        unset($_COOKIE['vendor_auth_token']);
    }
    
    // Logout vendor
    $this->vendor->logout();
    
    // Redirect to login page
    $this->response->redirect($this->url->link('vendor/login', '', true));
}    

    
// ___________________________________________________

public function checkEmailExist() {
    // prevent caching
    $this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    $this->response->addHeader('Pragma: no-cache');
    $this->response->addHeader('Content-Type: application/json');

    $raw = $this->request->post['value'] ?? $this->request->get['value'] ?? '';
    $email = strtolower(trim($raw)); // normalize

    $exists = false;
    if ($email !== '') {
        $this->load->model('vendor/vendor');
      
        $row = $this->model_vendor_vendor->getVendorByEmail($email);
        $exists = !empty($row);
    }

    $this->response->setOutput(json_encode([
        'exists'  => $exists,
        'message' => $exists ? 'Already exists' : ''
    ]));
}


public function checkPhoneExist() {
    $this->response->addHeader('Content-Type: application/json');
    $phone = $this->request->post['value'] ?? $this->request->get['value'] ?? '';
    $exists = false;
    if ($phone !== '') {
        $this->load->model('vendor/vendor');
        $row = $this->model_vendor_vendor->getVendorByTelephone($phone);
        $exists = !!$row;
    }
    $this->response->setOutput(json_encode([
        'exists' => $exists,
        'message' => $exists ? 'Already exists' : ''
    ]));
}

public function checkDisplayNameExist() {
    $this->response->addHeader('Content-Type: application/json');
    $val = $this->request->post['value'] ?? $this->request->get['value'] ?? '';
    $exists = false;
    if ($val !== '' && method_exists($this->model_vendor_vendor, 'getVendorByDisplayName')) {
        $this->load->model('vendor/vendor');
        $row = $this->model_vendor_vendor->getVendorByDisplayName($val);
        $exists = !!$row;
    }
    $this->response->setOutput(json_encode([
        'exists' => $exists,
        'message' => $exists ? 'Already exists' : ''
    ]));
}

public function checkPanExist() {
    // headers (optional but nice)
    $this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    $this->response->addHeader('Pragma: no-cache');
    $this->response->addHeader('Content-Type: application/json');

    // read value from POST or GET
    $val = $this->request->post['value'] ?? $this->request->get['value'] ?? '';
    $val = strtoupper(trim($val));   // normalize like JS
    $exists = false;

    if ($val !== '') {
        $this->load->model('vendor/vendor'); // <-- load FIRST

       
        if (method_exists($this->model_vendor_vendor, 'getVendorByPan')) {
            $row = $this->model_vendor_vendor->getVendorByPan($val);
            $exists = !empty($row);
        } else {
          
            $query = $this->db->query("SELECT vendor_id FROM " . DB_PREFIX . "vendor WHERE pan = '" . $this->db->escape($val) . "' LIMIT 1");
            $exists = (bool)$query->num_rows;
        }
    }

    $this->response->setOutput(json_encode([
        'exists'  => $exists,
        'message' => $exists ? 'Already exists' : ''
    ]));
}

// -----------------------------------------------


}
