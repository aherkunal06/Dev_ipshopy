<?php


class ControllerExtensionModulewebVitals extends Controller {
	
	private $lib = DIR_SYSTEM . 'library' . DIRECTORY_SEPARATOR  . 'webvitals' . DIRECTORY_SEPARATOR;
	private $error = array();

	public function index() {

        //speedup01_Install_core_files();

        $this->document->addStyle('view/javascript/semantic/semantic.min.css');
        $this->document->addScript('view/javascript/semantic/semantic.min.js');

		$this->load->language('extension/module/webVitals');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		
		$data['template'] = $this->config->get('config_theme');

		$data['fatal'] = '';
		$data['warn'] = '';


		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			
			if ((isset($this->request->post['webVitals_status'])) && $this->request->post['webVitals_status'] == 1) {
				$license = $this->get_licence_info();
				if ($license === true) {
					$this->PatchOCIndex_file(true);
				} else {
					$data['warn'] = 'Can not get license. Reason: ' . $license;
					$this->request->post['webVitals_status'] = 0;
				}
				
			} else {
				$this->PatchOCIndex_file(false);
			}
			
			$this->model_setting_setting->editSetting('webVitals', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if (!$data['warn'] && isset($this->session->data['token'])) $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_edit_secure'] = $this->language->get('text_edit_secure');
		$data['text_edit_extra'] = $this->language->get('text_edit_extra');
		$data['text_edit_captcha'] = $this->language->get('text_edit_captcha');
		$data['text_success'] = $this->language->get('text_success');
		$data['text_extension'] = $this->language->get('text_extension');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_instructions'] = $this->language->get('text_instructions');
		$data['text_disabled'] = $this->language->get('text_disabled');
		

		
		if (PHP_MAJOR_VERSION == 4) {
			$data['fatal'] = 'Not supported PHP version';
		}

		if (!$data['fatal'] && is_int(PHP_MAJOR_VERSION) && is_int(PHP_MINOR_VERSION) && PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION == 2) {
			$data['fatal'] = 'PHP 5.2.x is Not supported anymore, due to security issues.';
		}
		
		$extensions = get_loaded_extensions();
		if (!$data['fatal'] && !$this->check_ioncube_version()) {
			$data['fatal'] = 'IonCube Loader version 10 or higher is required for this plugin.';
		}


		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

        $on_file = dirname(DIR_APPLICATION) . '/webanalyze/firewall/firewall.speedup.on';
        if (!file_exists($on_file)) {
            $data['is_speedup_enabled'] = 0;
        } else {
            $data['is_speedup_enabled'] = 1;
        }
		
		if (isset($this->request->post['webVitals_status'])) {
			$data['webVitals_status'] = $this->request->post['webVitals_status'];
		} else {
			$data['webVitals_status'] = $this->config->get('webVitals_status');
		}
		
		if (is_file($this->lib . 'terminated.flag'))
		{
			$data['warn'] = 'Something is wrong with your license or it has expired. Enable improvement again to update license info or purchase our paid license.';
		}
		
		$php_version = $this->get_php_version();
		
		if (!is_file($this->lib . 'license.json') || !is_file($this->lib . 'core.web.vitals.main.'.$php_version.'.php') || !$data['webVitals_status']) {
			$this->request->post['webVitals_status'] = 0;
			$this->model_setting_setting->editSetting('webVitals', $this->request->post);
			$this->PatchOCIndex_file(false);
			@unlink($this->lib . 'license.json');
			@unlink($this->lib . 'core.web.vitals.main.'.$php_version.'.php');
		}
		
		$data['exp_date'] = $this->get_exp_date();

		
		$data['upgrade_link'] = 'https://www.siteguarding.com/en/buy-service/google-core-web-vitals-fix-services';
		$data['website_url'] = HTTPS_CATALOG;
		$data['status'] = $data['webVitals_status'] ? '<span class="green ui label">Enabled</span>' : '<span class="red ui label">Disabled</span>';
		$data['status_css'] = $data['webVitals_status'] ? '' : 'disabled';


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module' . '&tab_id=', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/webVitals', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/webVitals', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/webVitals', $data));
	}

	protected function validate() {
		 
		if (!$this->user->hasPermission('modify', 'extension/module/webVitals')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	
		return !$this->error;
	}




	public function uninstall(){

	}	

	public function install(){

	}	



	public function PatchOCIndex_file($action = true)   // true - insert, false - remove
	{
		$php_version = $this->get_php_version();
		
		$file = $this->lib . 'core.web.vitals.main.'.$php_version.'.php';

		$integration_code = '<?php /* Siteguarding Block 8DF343FDFGPR-START */ if (file_exists("'.$file.'"))include_once("'.$file.'");/* Siteguarding Block 8DF343FDFGPR-END */?>';
		
			
			$scan_path = dirname(DIR_APPLICATION);
			
			$filename = $scan_path.DIRECTORY_SEPARATOR.'index.php';

			$handle = fopen($filename, "r");
			if ($handle === false) return false;
			$contents = fread($handle, filesize($filename));
			if ($contents === false) return false;
			fclose($handle);
			
			$pos_code = stripos($contents, '8DF343FDFGPR');
			
			if ($action === false)
			{
				// Remove block
				$contents = str_replace($integration_code, "", $contents);
			}
			else {
				// Insert block
				if ( $pos_code !== false/* && $pos_code == 0*/)
				{
					// Skip double code injection
					return true;
				}
				else {
					// Insert
					

					$contents = $integration_code.$contents;
				}
			}

			$handle = fopen($filename, 'w');
			if ($handle === false) 
			{
				// 2nd try , change file permssion to 666
				$status = chmod($filename, 0666);
				if ($status === false) return false;
				
				$handle = fopen($filename, 'w');
				if ($handle === false) return false;
			}
			
			$status = fwrite($handle, $contents);
			if ($status === false) return false;
			fclose($handle);

			
			return true;
	}

	public function get_licence_info()
	{

		$website_url = HTTPS_CATALOG;
		$domain = $this->PrepareDomain($website_url);

		$scan_path = dirname(DIR_APPLICATION);
		
		if (!is_dir($scan_path . '/webanalyze')) mkdir($scan_path . '/webanalyze');

		file_put_contents( $scan_path . '/webanalyze/verification.txt', md5($domain));
		
		$url = "https://www.siteguarding.com/ext/vitals/index.php";
		
		$data = array(
				'action' => 'get_license',
				'domain' => $domain,
				'verification_link' => $website_url . '/webanalyze/verification.txt',
				'cms' => 'opencart',
			);
			
		$response = $this->get_url_response($url, true, $data);
		

		
		$licence = @json_decode($response, true);

		if ($licence['status'] == 'ok') {
			
			$php_version = substr(PHP_VERSION, 0, 3);
			
			if ( (float) $php_version >= 7.2) $php_version = '7.2';
			
			$downloadLink = "https://www.siteguarding.com/ext/vitals/files/core.web.vitals.$php_version.bin";
			
			$this->CreateRemote_file_contents($downloadLink, $this->lib . 'core.web.vitals.main.'.$php_version.'.php');
			
			unset($licence['status'], $licence['reason']);
			$json = json_encode($licence);

			file_put_contents( $this->lib . 'license.json', json_encode($licence));
			@unlink( $this->lib . 'terminated.flag');
			return true;
		} else {
			return $licence['reason'];
		}
	}

	public function PrepareDomain($domain)
	{
		$host_info = parse_url($domain);
		if ($host_info == NULL) return false;
		$domain = $host_info['host'];
		if ($domain[0] == "w" && $domain[1] == "w" && $domain[2] == "w" && $domain[3] == ".") $domain = str_replace("www.", "", $domain);
		//$domain = str_replace("www.", "", $domain);
		
		return $domain;
	}

	public function get_exp_date() {
		if(is_file($this->lib . 'license.json')) {
			$license_info = @json_decode(file_get_contents($this->lib . 'license.json'), true);
			if ($license_info && isset($license_info['exp_date'])) return $license_info['exp_date'];
		}
		return false;
	}

	public function get_php_version() 
	{
		$php_version = substr(PHP_VERSION, 0, 3);
			
		if ( (float) $php_version >= 7.2) $php_version = '7.2';
		
		return $php_version;
	}

	public function get_status() {
		$params = plggcwvf_Get_Params();
		return (bool) $params['plggcwvf_status'];
	}

	public function get_main_file(){
		
		$php_version = $this->get_php_version();
		
		$file = $this->lib . 'core.web.vitals.main.' .  $php_version . '.php';
		
		if (is_file($file)) {
			return $file;
		} elseif (!is_file($this->lib . 'terminated.flag' )) {
			foreach (glob($this->lib . 'core.web.vitals.main.*.php') as $f) {
				@unlink($f);
			}
			$downloadLink = "https://www.siteguarding.com/ext/vitals/files/core.web.vitals.$php_version.bin";
			if ($this->CreateRemote_file_contents($downloadLink, $file) !== false) return $file;
		}
		$this->PatchOCIndex_file(false);
		return false;
	}

	public function check_ioncube_version()
	{
			ob_start();
		phpinfo(INFO_GENERAL);
		$aux = str_replace('&nbsp;', ' ', ob_get_clean());
		if($aux !== false)
		{
			$pos = mb_stripos($aux, 'ionCube PHP Loader');
			if($pos !== false)
			{
				$aux = mb_substr($aux, $pos + 18);
				$aux = mb_substr($aux, mb_stripos($aux, ' v') + 2);

				$version = '';
				$c = 0;
				$char = mb_substr($aux, $c++, 1);
				while(mb_strpos('0123456789.', $char) !== false)
				{
					$version .= $char;
					$char = mb_substr($aux, $c++, 1);
				}

				return ($version >= 10);
			}
		}

		return false;
		
	}

	

	public function CreateRemote_file_contents($url, $dst)
	{
		if (extension_loaded('curl')) 
		{
			$dst = fopen($dst, 'w');
			
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $url );
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36");
			curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
			curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3600000);
			curl_setopt($ch, CURLOPT_FILE, $dst);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 sec
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 10000); // 10 sec
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			
			$a = curl_exec($ch);
			if ($a === false)  return false;
			
			$info = curl_getinfo($ch);
			
			curl_close($ch);
			fflush($dst);
			fclose($dst);
			
			return $info['size_download'];
		}
		else return false;
	}

	public function get_url_response($url, $post = false, $data = false) {
		
		if (function_exists('curl_init')) {
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0'); 
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_URL, $url); 
			if ($post && is_array($data)) {
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
			}
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			$code = curl_exec($curl); 
			curl_close($curl); 
		} elseif (function_exists('file_get_contents')) {
			if ($post && is_array($data)) {
				$opts = array('http' =>
					array(
						'method'  => 'POST',
						'header'  => 'Content-type: application/x-www-form-urlencoded',
						'content' => http_build_query($data)
					)
				);
				$context  = stream_context_create($opts);
				$code = file_get_contents($url, false, $context);
			} else {
				$code = file_get_contents($url);
			}
		} else {
			$code = false;
		}
			return $code;
	}
		
	
}