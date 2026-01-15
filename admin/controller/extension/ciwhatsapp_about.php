<?php
class ControllerExtensionCiwhatsappAbout extends Controller {
	private $error = [];
	private $extension_id = 42503;
	private $domain;

	private $module_token = '';
	private $ci_token = '';

	public function __construct($registry) {
		parent:: __construct($registry);

		if(VERSION <= '2.3.0.2') {
			$this->module_token = 'token';
			$this->ci_token = $this->session->data['token'];
		} else {
			$this->module_token = 'user_token';
			$this->ci_token = $this->session->data['user_token'];
		}

		$this->load->language('extension/ciwhatsapp_about');
		$this->load->model('extension/ciwhatsapp/setting');

		$this->load->model('setting/setting');
	}

	public function index() {
		$this->document->addStyle('view/stylesheet/ciwhatsapp/style.css');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_setting_setting->editSetting('module_ciwhatsapp_key', $this->request->post);

			$this->session->data['success'] = 'License Key has been submit successfully.';

			$this->response->redirect($this->url->link('extension/ciwhatsapp_about', $this->module_token .'=' . $this->ci_token, true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = $this->language->get('text_form');
		$data['entry_key'] = $this->language->get('entry_key');

		$data['legend_key'] = $this->language->get('legend_key');
		$data['legend_about'] = $this->language->get('legend_about');

		$data['button_submit'] = $this->language->get('button_submit');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['green_warning'])) {
			$data['error_green_warning'] = $this->error['green_warning'];
		} else {
			$data['error_green_warning'] = '';
		}

		$url = '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->module_token .'=' . $this->ci_token, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/ciwhatsapp_about', $this->module_token .'=' . $this->ci_token . $url, true)
		);

		$url = '';

		if(!empty($this->request->get['print_form'])) {
		$url = '&print_form='. $this->request->get['print_form'];
		}

		$data['action'] = $this->url->link('extension/ciwhatsapp_about', $this->module_token .'=' . $this->ci_token . $url, true); $data['date'] = $this->getDate('type');

		$data['cancel'] = $this->url->link('common/dashboard', $this->module_token .'=' . $this->ci_token . $url, true);

		if (isset($this->request->post['module_ciwhatsapp_key'])) {
			$data['module_ciwhatsapp_key'] = $this->request->post['module_ciwhatsapp_key'];
		} else {
			$data['module_ciwhatsapp_key'] = $this->config->get('module_ciwhatsapp_key');
		}

		if(isset($this->request->post['print_form'])) {
			$data['print_form'] = $this->request->post['print_form'];
		} elseif(!empty($this->request->get['print_form'])) {
			$data['print_form'] = $this->request->get['print_form'];
		} else {
			$data['print_form'] = '';
		}

		$data['extension_id'] = $this->extension_id;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['ci_token'] = $this->ci_token;
		$data['module_token'] = $this->module_token;

		if(VERSION <= '2.3.0.2') {
			$this->response->setOutput($this->load->view('extension/ciwhatsapp_about.tpl', $data));
		} else {
			$file_variable = 'template_engine';
			$file_type = 'template';
			$this->config->set($file_variable, $file_type);
			$this->response->setOutput($this->load->view('extension/ciwhatsapp_about', $data));
		}
	}

	public function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/ciwhatsapp_about')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if(empty($this->request->post['module_ciwhatsapp_key'])) {
			$this->error['warning'] = 'License key required!';
		}

		if(!empty($this->request->post['module_ciwhatsapp_key'])) {
			$key = $this->request->post['module_ciwhatsapp_key'];
		} else {
			$key = '';
		}

		if(!empty($this->request->post['print_form'])) {
			$test = $this->request->post['print_form'];
		} else {
			$test = '';
		}

		if ($this->request->server['HTTPS']) {
			$server = HTTPS_CATALOG;
		} else {
			$server = HTTP_CATALOG;
		}

		$this->scanDomain();

		if(!$this->error) {
			$post_info = ['key' => $key, 'extension_id' => $this->extension_id, 'domain' => $this->domain, 'server' => $server, 'version' => VERSION, 'test' => $test ];

			$url = 'https://www.codinginspect.com/index.php?route=api/key/save';

			$curl = curl_init($url);


			if(!$test) {
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
			}

			curl_setopt($curl, CURLOPT_POSTFIELDS, $post_info);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_TIMEOUT, 30);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			// Submit the POST request
			$result = curl_exec($curl);

			if (curl_errno($curl)) {
	            throw new \Exception(curl_error($curl));
	        }

			// Close cURL session handle
			curl_close($curl);

			$json_result = json_decode($result, true);

			if($test) {
				echo "<br>";
				echo "^^^^^^ Json Result ^^^^^^ "; die();
			}

			if (!$json_result) {
				$this->error['warning'] = 'License key can not validate!';
			}

			if (!empty($json_result['warning'])) {
				$this->error['warning'] = $json_result['warning'];
			}

			if (!empty($json_result['green_warning'])) {
				$this->error['green_warning'] = $json_result['green_warning'];
			}

			if(!$this->error && !empty($json_result['success'])) {
				$header = $this->model_extension_ciwhatsapp_setting->getHeader($json_result['success'], [$json_result['success'] => 1]);

				$post_data = [];
				foreach($post_info as $key => $value) {
					if($key != 'key' && $key != 'test' && $key != 'server' && $key != 'version') {
						$post_data['ciwhatsapp_type_'. mb_substr($key, 0, 1)] = $value;
					}
				}

				$this->model_extension_ciwhatsapp_setting->getFooter('ciwhatsapp_type', $post_data);
			}
		}

		return !$this->error;
	}

	public function getDate($format = '') {
		if(!empty($this->request->get['print_check'])) {
			$test = $this->request->get['print_check'];
		} else {
			$test = '';
		}

		if ($this->request->server['HTTPS']) {
			$server = HTTPS_CATALOG;
		} else {
			$server = HTTP_CATALOG;
		}

		if($format) {
			$code = '_';
		} else {
			$code = '';
		}

		$this->scanDomain();

		$status = true;

		$post_info = ['key' => $this->config->get('module_ciwhatsapp_key'), 'extension_id' => $this->extension_id, 'domain' => $this->domain, 'server' => $server, 'version' => VERSION, 'test' => $test ];

		$url = 'https://www.codinginspect.com/index.php?route=api/key/check';

		$type = 'ciwhatsapp';

		$curl = curl_init($url);

		if(!$test) {
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
		}

		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_info);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		// Submit the POST request
		$result = curl_exec($curl);

		if (curl_errno($curl)) {
            throw new \Exception(curl_error($curl));
        }

		// Close cURL session handle
		curl_close($curl);

		$json_result = json_decode($result, true);

		if($test) {
			echo "<br>";
			echo "^^^^^^ Json Result ^^^^^^ "; die();
		}

		if (!$json_result) {
			$status = false;
		}

		if (empty($json_result['success'])) { $status = false; } if (!empty($json_result['warning'])) { $this->model_setting_setting->deleteSetting($type . $code . $format); }

		if(!$status) {
			return true;
		} else {
			return false;
		}
	}

	protected function scanDomain() {
		// Scan Domain Name (Remove https, hhtp, www. from domain name)
		$this->domain = $_SERVER['HTTP_HOST'];
		$this->domain = str_replace('http://', '', $this->domain);
		$this->domain = str_replace('https://', '', $this->domain);
		$this->domain = str_replace('www.', '', $this->domain);
		$this->domain = trim($this->domain, '/');
	}
}