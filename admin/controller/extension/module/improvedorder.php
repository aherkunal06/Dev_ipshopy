<?php
class ControllerExtensionModuleImprovedOrder extends Controller {
	private $error = array();
	private $modelpath;
	private $modulepath;
	private $classname;
	private $languagepath;
	private $sslvalue;
	private $tokenname;
	private $returnpath;
	private $opencartversion;
	private $customerpath;
	private $postarray = array();
	private $data = array();

	public function __construct($registry) {
		parent::__construct($registry);
		$this->classname = "improvedorder";
		$this->config->load("cartbinder/".$this->classname);
		$this->modelpath = $this->config->get("modelpath");
		$this->modulepath = $this->config->get("modulepath");
		$this->languagepath = $this->config->get("languagepath");
		$this->sslvalue = $this->config->get("sslvalue");
		$this->tokenname = $this->config->get("tokenname");
		$this->returnpath = $this->config->get("returnpath");
		$this->postarray = $this->config->get("postarray");
		$this->opencartversion = str_replace(".","",VERSION);

		$this->load->language($this->languagepath);
		$languagetexts = $this->load->language($this->languagepath);
		foreach ($languagetexts as $key => $value) {
			$this->data[$key] = $value;
		}
		$this->load->model($this->modelpath);
		$this->load->model('setting/setting');
		$this->load->model('setting/store');

	}

	public function index() {

		$this->model_extension_module_improvedorder->createOrderStatusColor();
		
		$data = $this->data;
		
		$this->document->setTitle($this->language->get('heading_title'));
				
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {

			foreach ($this->request->post as $key => $value) {
	    		$temp[$key] = $this->request->post[$key];
	    	}
			
 			$this->model_setting_setting->editSetting('module_improvedorder', $temp);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'], true));
		}

		$data['user_token'] = $this->session->data['user_token'];		
		$data['action'] = $this->url->link($this->modulepath, $this->tokenname.'=' . $this->session->data[$this->tokenname], $this->sslvalue);
		$data['orderpage'] = $this->url->link("sale/order", $this->tokenname.'=' . $this->session->data[$this->tokenname], $this->sslvalue);
		$data['cancel'] = $this->url->link('common/home', $this->tokenname.'=' . $this->session->data[$this->tokenname], $this->sslvalue);
		$this->document->addScript('view/javascript/jquery/improvedorderfilter.js');

		foreach ($this->postarray as $key => $value) {
    		if(isset($this->request->post[$value])) {
    			$data[$value] = $this->request->post[$value];
    		} else {
    			$data[$value] = $this->config->get($value);
    		}
    	}

		if($data['module_improvedorder_removecolumnn']) {
		  $data['removecolumns'] = explode(",",$data['module_improvedorder_removecolumnn']);
		} else {
		  $data['removecolumns'] = "";
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', $this->tokenname.'=' . $this->session->data[$this->tokenname], $this->sslvalue),     		
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link($this->modulepath, $this->tokenname.'=' . $this->session->data[$this->tokenname], $this->sslvalue),
      		'separator' => ' :: '
   		);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		//codestart

		$data['oc_licensing_home'] = 'https://www.cartbinder.com/store/'; $data['extension_id'] = 22611; $admin_support_email = 'support@cartbinder.com'; $data['license_purchase_thanks'] = sprintf($this->language->get('license_purchase_thanks'), $admin_support_email); if(isset($this->request->get['emailmal'])){ $data['emailmal'] = $this->sslvalue; } if(isset($this->request->get['regerror'])){ if($this->request->get['regerror']=='emailmal'){ $this->error['warning'] = $this->language->get('regerror_email'); }elseif($this->request->get['regerror']=='orderidmal'){ $this->error['warning'] = $this->language->get('regerror_orderid'); }elseif($this->request->get['regerror']=='noreferer'){ $this->error['warning'] = $this->language->get('regerror_noreferer'); }elseif($this->request->get['regerror']=='localhost'){ $this->error['warning'] = $this->language->get('regerror_localhost'); }elseif($this->request->get['regerror']=='licensedupe'){ $this->error['warning'] = $this->language->get('regerror_licensedupe'); } } $domainssl = explode("//", HTTPS_SERVER); $domainnonssl = explode("//", HTTP_SERVER); $domain = ($domainssl[1] != '' ? $domainssl[1] : $domainnonssl[1]);$data['aurl'] = (HTTPS_SERVER !='' ? HTTPS_SERVER : HTTP_SERVER);$data['auri'] = (HTTPS_CATALOG !='' ? HTTPS_CATALOG : HTTP_CATALOG) . substr($_SERVER['REQUEST_URI'], 1); $data['domain'] = $domain; $data['licensed'] = @file_get_contents($data['oc_licensing_home'] . 'licensed.php?domain=' . $domain . '&extension=' . $data['extension_id']); if(!$data['licensed'] || $data['licensed'] == ''){ if(extension_loaded('curl')) { $post_data = array('domain' => $domain, 'extension' => $data['extension_id']); $curl = curl_init(); curl_setopt($curl, CURLOPT_HEADER, false); curl_setopt($curl, CURLINFO_HEADER_OUT, $this->sslvalue); curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17'); $follow_allowed = ( ini_get('open_basedir') || ini_get('safe_mode')) ? false : $this->sslvalue; if ($follow_allowed) { curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); } curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 9); curl_setopt($curl, CURLOPT_TIMEOUT, 60); curl_setopt($curl, CURLOPT_AUTOREFERER, $this->sslvalue); curl_setopt($curl, CURLOPT_VERBOSE, 1); curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); curl_setopt($curl, CURLOPT_FORBID_REUSE, false); curl_setopt($curl, CURLOPT_RETURNTRANSFER, $this->sslvalue); curl_setopt($curl, CURLOPT_URL, $data['oc_licensing_home'] . 'licensed.php'); curl_setopt($curl, CURLOPT_POST, $this->sslvalue); curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data)); $data['licensed'] = curl_exec($curl); curl_close($curl); }else{ $data['licensed'] = 'curl'; } } $data['licensed_md5'] = md5($data['licensed']); $data['entry_free_support'] = $this->language->get('entry_free_support'); $order_details = @file_get_contents($data['oc_licensing_home'] . 'order_details.php?domain=' . $domain . '&extension=' . $data['extension_id']); $order_data = json_decode($order_details, $this->sslvalue); if(!is_array($order_data) || $order_data == ''){ if(extension_loaded('curl')) { $post_data2 = array('domain' => $domain, 'extension' => $data['extension_id']); $curl2 = curl_init(); curl_setopt($curl2, CURLOPT_HEADER, false); curl_setopt($curl2, CURLINFO_HEADER_OUT, $this->sslvalue); curl_setopt($curl2, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17'); $follow_allowed2 = ( ini_get('open_basedir') || ini_get('safe_mode')) ? false : $this->sslvalue; if ($follow_allowed2) { curl_setopt($curl2, CURLOPT_FOLLOWLOCATION, 1); } curl_setopt($curl2, CURLOPT_CONNECTTIMEOUT, 9); curl_setopt($curl2, CURLOPT_TIMEOUT, 60); curl_setopt($curl2, CURLOPT_AUTOREFERER, $this->sslvalue); curl_setopt($curl2, CURLOPT_VERBOSE, 1); curl_setopt($curl2, CURLOPT_SSL_VERIFYHOST, false); curl_setopt($curl2, CURLOPT_SSL_VERIFYPEER, false); curl_setopt($curl2, CURLOPT_FORBID_REUSE, false); curl_setopt($curl2, CURLOPT_RETURNTRANSFER, $this->sslvalue); curl_setopt($curl2, CURLOPT_URL, $data['oc_licensing_home'] . 'order_details.php'); curl_setopt($curl2, CURLOPT_POST, $this->sslvalue); curl_setopt($curl2, CURLOPT_POSTFIELDS, http_build_query($post_data2)); $order_data = json_decode(curl_exec($curl2), $this->sslvalue); curl_close($curl2); }else{ $order_data['status'] = 'disabled'; } } if(isset($order_data['status']) && $order_data['status'] == 'enabled'){ $isSecure = false; if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) { $isSecure = $this->sslvalue; } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') { $isSecure = $this->sslvalue; } $data['support_status'] = 'enabled'; $data['support_order_id'] = $order_data['order_id']; $data['support_extension_name'] = $order_data['extension_name']; $data['support_domain'] = $order_data['domain']; $data['support_username'] = $order_data['username']; $data['support_email'] = $order_data['email']; $data['support_registered_date'] = date('Y-m-d', $order_data['registered_date']); $data['support_order_date'] = date('Y-m-d', ($order_data['order_date'] + 31536000)); if((time() - $order_data['order_date']) > 31536000){ $data['text_free_support_remaining'] = sprintf($this->language->get('text_free_support_expired'), 1, ($isSecure ? 1 : 0), urlencode($domain) , $data['extension_id'] , $this->session->data['user_token']); }else{ $data['text_free_support_remaining'] = sprintf($this->language->get('text_free_support_remaining'), 366 - ceil((time() - $order_data['order_date']) / 86400)); } }else{ $data['support_status'] = 'disabled'; $data['text_free_support_remaining'] = sprintf($this->language->get('text_free_support_remaining'), 'unknown'); }

		//codeend

		$this->response->setOutput($this->load->view('extension/module/improvedorder', $data));
	}

	public function install() {
		foreach ($this->postarray as $key => $value) {
    		$temp[$value] = $this->config->get($value);;
    	}
    	$temp['module_improvedorder_status'] = 1; 
		$this->model_setting_setting->editSetting('module_improvedorder', $temp);
	}

	public function uninstall() {
		foreach ($this->postarray as $key => $value) {
    		$temp[$value] = $this->config->get($value);;
    	}
    	$temp['module_improvedorder_status'] = 0; 
		$this->model_setting_setting->editSetting('module_improvedorder', $temp);
	}

	public function editPhoneNo() {
		$json = array();
		$phone_no  = $this->request->get['phone_no'];
		$order_id = $this->request->get['order_id'];
		if ($this->user->hasPermission('modify', 'sale/order') && $order_id && $phone_no && $this->config->get("module_improvedorder_editphoneno")) {
			$this->model_extension_module_improvedorder->savePhoneNo($order_id,$phone_no);
			$json = 1;
		} else {
			$json = 0;
		}
		$this->response->setOutput(json_encode($json));
	}

	public function shippingaddressform() {
		$json = array();
		$this->load->model("sale/order");
		$order_id = $this->request->get['order_id'];
		if ($this->user->hasPermission('modify', 'sale/order') && $order_id && $phone_no && $this->config->get("module_improvedorder_editshippingaddress")) {
			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

			$data['shipping_firstname'] = $data['firstname'] =  $order_info['shipping_firstname'];
			$data['shipping_lastname'] = $data['lastname'] = $order_info['shipping_lastname'];
			$data['shipping_company'] = $data['company'] = $order_info['shipping_company'];
			$data['shipping_address_1'] = $data['address_1'] = $order_info['shipping_address_1'];
			$data['shipping_address_2'] = $data['address_2'] = $order_info['shipping_address_2'];
			$data['shipping_city'] = $data['city'] = $order_info['shipping_city'];
			$data['shipping_postcode'] = $data['postcode'] = $order_info['shipping_postcode'];
			$data['shipping_country_id'] = $data['country_id'] = $order_info['shipping_country_id'];
			$data['shipping_zone_id'] = $data['zone_id'] = $order_info['shipping_zone_id'];
			$data['shipping_custom_field'] = $order_info['shipping_custom_field'];
			$data['shipping_method'] = $order_info['shipping_method'];
			$data['shipping_code'] = $order_info['shipping_code'];
			$data['shipping_zone'] = $order_info['shipping_zone'];
			$data['shipping_country'] = $order_info['shipping_country'];

			$data['zones'] = $this->model_localisation_zone->getZonesByCountryId($data['shipping_country_id']);

			$data['type'] = "shipping";

			$data['shippingaddresscontroller'] = $this->load->view('extension/module/orderentry_ad', $data);

         	$data['shipping_custom_fields'] = array();

	        foreach ($custom_fields as $custom_field) {
	          if ($custom_field['location'] == 'address' && isset($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
	            if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {

	               if($this->opencartversion < 2100) {
		         		$custom_field_value_info = $this->model_sale_custom_field->getCustomFieldValue($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);
					} else {
		         		$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);
					}


	              if ($custom_field_value_info) {
	                $data['shipping_custom_fields'][] = array(
	                  'name'  => $custom_field['name'],
	                  'value' => $custom_field_value_info['name'],
	                  'sort_order' => $custom_field['sort_order']
	                );
	              }
	            }

	            if ($custom_field['type'] == 'checkbox' && is_array($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
	              foreach ($order_info['shipping_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
	                if($this->opencartversion < 2100) {
		         		$custom_field_value_info = $this->model_sale_custom_field->getCustomFieldValue($custom_field_value_id);

					} else {
		         		$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);
					}

	                if ($custom_field_value_info) {
	                  $data['shipping_custom_fields'][] = array(
	                    'name'  => $custom_field['name'],
	                    'value' => $custom_field_value_info['name'],
	                    'sort_order' => $custom_field['sort_order']
	                  );
	                }
	              }
	            }

	            if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
	              $data['shipping_custom_fields'][] = array(
	                'name'  => $custom_field['name'],
	                'value' => $order_info['shipping_custom_field'][$custom_field['custom_field_id']],
	                'sort_order' => $custom_field['sort_order']
	              );
	            }

	            if ($custom_field['type'] == 'file') {
	              $upload_info = $this->model_tool_upload->getUploadByCode($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

	              if ($upload_info) {
	                $data['shipping_custom_fields'][] = array(
	                  'name'  => $custom_field['name'],
	                  'value' => $upload_info['name'],
	                  'sort_order' => $custom_field['sort_order']
	                );
	              }
	            }
	          }
	        }


		} else {
			
		}
		$this->response->setOutput(json_encode($json));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/improvedorder')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}


}