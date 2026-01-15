<?php
class ModelExtensionModuleSmsAlert extends Model {
	/**added on 06-05-2019 */
	const PATTERN_PHONE	= '/^(\+)?(country_code)?0?\d+$/';
	
	public function add_notify_request($data = array()) {
	$this->db->query("INSERT INTO " . DB_PREFIX . "smsalert_notify SET phone = '".$this->db->escape($data['phone'])."', product_id = '".$this->db->escape($data['product_id'])."', customer_id = '".$this->db->escape($data['customer_id'])."', language_id = '" . (int)$this->config->get('config_language_id')."', store_id = '" . (int)$this->config->get('config_store_id'). "', date_added = NOW()");
		
		$this->load->model('catalog/product');
		$product = $this->model_catalog_product->getProduct($data['product_id']);
		if($product)
		{
			$store_url = HTTPS_SERVER;
			
		$replace = [
				$product['name'],
				$this->config->get('config_name'),
				$store_url."index.php?route=product/product&product_id=". $data['product_id'],
			];
		$templates = $this->getTemplates('product_subscription', $this->config->get('config_store_id'),1);
		    $template_data = false;
		    foreach ($templates as $template) {
				$template_data = $template;
		    }
		
			if ($template_data && !empty($template_data) && $template_data['status']=='1') {
				$message = strip_tags(str_replace(array('{item_name}','{store_name}','{item_url}'), $replace, html_entity_decode($template_data['message'])));
				$this->sendSMS($data['phone'], $message);
			}
		}
	}
	public function isCustomerExists($email,$pwd)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($pwd) . "'))))) OR password = '" . $this->db->escape(md5($pwd)) . "') AND status = '1'");
		

		return $query->row;
	}

	public static function getCountryPattern($countryCode=NULL)
    {
		$c = self::$countries;
		$pattern ='';
			foreach($c as $list)
			{
				if($list['countryCode']==$countryCode){
					
					if(array_key_exists('pattern',$list)){
						$pattern = $list['pattern'];
						break;
					}
				}
			}			
		
		return $pattern;
    }
	
	
	public function getPhonePattern()
	{
		$country_code=$this->config->get('smsalert_country');
		$pattern = ($this->config->get('smsalert_mobile_pattern')!='') ? $this->config->get('smsalert_mobile_pattern'):self::PATTERN_PHONE;
		
		$country_code = str_replace('+', '', $country_code);
		$pattern_phone = str_replace("country_code",$country_code,$pattern);
		return $pattern_phone;
	}
		
	/** closed added on 06-05-2019 */
	public function getTemplates($type, $store_id,$customer_notify=1,$language_id='') { 
	   $language = $this->getLanguageByCode($this->config->get('config_admin_language'));
	   $lang_id = ($language_id!='')?$language_id:$language['language_id'];
	   
	  $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "sms_template st LEFT JOIN " . DB_PREFIX . "sms_template_message stm ON st.sms_template_id = stm.sms_template_id WHERE type = '" . $this->db->escape($type) . "' AND store_id = '" . (int)$store_id . "' AND language_id = '" . $lang_id . "' AND customer_notify = '" . $customer_notify . "'");
		
		return $query->rows;
	}
	public function getOtpTemplates($type,$store_id)
	{
		$templates = $this->getTemplates($type, $store_id);
		$template = array_shift($templates);
		if(!empty($template['message']) && $template['status']=='1')
		{
			return $template['message'];
		}
		return false;
	}
	public function getLanguageByCode($code) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}
	public function getSearch($type) {
		if ($type == 'register') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{telephone}',
				'{store_name}',
				'{password}'
			);
		} elseif ($type == 'contact_form') {
			$search = array(
				'{name}',
				'{phone}',
				'{email}',
				'{enquiry}',
				'{store_name}'
			);
		} elseif ($type == 'affiliate') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{store_name}',
				'{telephone}'
			);
		} elseif ($type == 'affiliate_transaction') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{commission}',
				'{store_name}',
				'{total_commission}'
			);
		} elseif ($type == 'affiliate_approve') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{store_name}',
				'{email}'
			);
		} elseif ($type == 'forgotten') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{store_name}',
				'{password}'
			);
		} elseif ($type == 'order') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{telephone}',
				'{order_id}',
				'{date_added}',
				'{payment_method}',
				'{shipping_method}',
				'{ip}',
				'{order_comment}',
				'{payment_address}',
				'{shipping_address}',
				'{products}',
				'{store_name}',
				'{order_amount}'
			);
		} elseif ($type == 'reward') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{points}',
				'{store_name}',
				'{total_points}'
			);
		} elseif ($type == 'account_approve') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{store_name}',
				'{email}'
			);
		} elseif ($type == 'account_deny') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{store_name}',
				'{email}'
			);
		} elseif ($type == 'account_transaction') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{credits}',
				'{store_name}',
				'{total_credits}'
			);
		} else {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{telephone}',
				'{order_id}',
				'{date_added}',
				'{payment_method}',
				'{shipping_method}',
				'{ip}',
				'{order_comment}',
				'{payment_address}',
				'{shipping_address}',
				'{products}',
				'{store_name}',
				'{store_url}',
				'{order_amount}',
				'{aftership_tracking_provider_name}',
				'{aftership_tracking_number}',
			);
		}
	
		return $search;
	}
	
	public function parseSMS($type, $store_id, $number, $replace,$vendor_phone='',$language_id='') {
		$number = preg_replace('/[^0-9]/', '', $number);
		$templates = $this->getTemplates($type, $store_id,1,$language_id);
		$template_data = false;
		foreach ($templates as $template) {
				$template_data = $template;
			
		}
	    
		if ($template_data && !empty($template_data) && $template_data['status']=='1') {
			$search = $this->getSearch($type);
			
			$message = strip_tags(str_replace($search, $replace, html_entity_decode($template_data['message'])));
			$this->sendSMS($number, $message);
		}
        $admin_templates = $this->getTemplates($type, $store_id,0,$language_id);
		$template_data = false;
		foreach ($admin_templates as $admin_template) {
				$template_data = $admin_template;
			
		}
		$admin_numbers = $this->config->get('smsalert_admin_number');
		$numbers = explode(',', $admin_numbers);
		if ($template_data && !empty($template_data) && $template_data['status']=='1' && !empty($numbers) && array_key_exists('message',$template_data)) {
			$search = $this->getSearch($type);
			$message = strip_tags(str_replace($search, $replace, html_entity_decode($template_data['message'])));
			foreach ($numbers as $number) {
			if ($number=='vendor' && $vendor_phone!='') {
				   $this->sendSMS($vendor_phone, $message);
			}
			else if($vendor_phone==''){
				$this->sendSMS($number, $message);
			}
		}
		}		
	}
	
	private function sendCurl($url, $post_data) {
		$extra_params = array('plugin'=>'opencart3', 'website'=>$_SERVER['SERVER_NAME']);
		$post_data = (!is_null($post_data)) ? array_merge($post_data, $extra_params) : $extra_params; 
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_USERAGENT, 'OpenCart Two Factor Authentication');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));

		$response = curl_exec($curl);	
		if (curl_errno($curl)) {
			$curl_error = 'SmsAlert cURL Error ' . curl_errno($curl) . ': ' . curl_error($curl);
		} else {
			$curl_error = '';
		}
		
		if ($curl_error) {
			$this->log->write($curl_error);
		}

		curl_close($curl);
		
		return json_decode($response, true);
	}
	function notifyStock($product,$store_id)
	{
		$templates = $this->getTemplates('out_of_stock', $store_id,0);
		$template_data = false;
		foreach ($templates as $template) {
				$template_data = $template;
			
		}
	    if ($template_data && !empty($template_data) && $template_data['status']=='1') {
			$message = strip_tags(str_replace(array('{store_name}','{item_name}','{item_qty}'), array($this->config->get('config_name'),$product['name'],$product['quantity']), html_entity_decode($template_data['message'])));
			$admin_numbers = $this->config->get('smsalert_admin_number');
			$numbers = explode(',', $admin_numbers);
			if(!empty($numbers))
			{
				foreach ($numbers as $number) {
					$this->sendSMS($number, $message);
				}
			}
		}
	}
	public function sendSMS($receiver, $message, $schedule='') {
		if (!$this->config->get('smsalert_auth_key') || !$this->config->get('smsalert_auth_secret')) {
			return;
		}
		$enable_short_url=$this->config->get('smsalert_enable_short_url');	
		/*added on 06-05-2019 */
		$country_code=$this->config->get('smsalert_country');
		$no = preg_replace('/[^0-9]/', '', $receiver);
		$no = ltrim($no, '0');
		$no = (substr($no,0,strlen($country_code))!=$country_code) ? $country_code.$no : $no;
		$match = preg_match($this->getPhonePattern(),$no);
		/*closed added on 06-05-2019 */
		if($match)
		{	
			$post_data = array(
				'user'			=> $this->config->get('smsalert_auth_key'),
				'pwd'			=> $this->config->get('smsalert_auth_secret'),
				'sender'		=> $this->config->get('smsalert_default_senderid'),
				'mobileno'		=> $no,
				'text'			=> $message
			);
			if($schedule!=''){
				$post_data['schedule']=$schedule;
			}
			if($enable_short_url=='1'){
				$post_data['shortenurl']=1;
			}
			$response_arr = $this->sendCurl(base64_decode('aHR0cHM6Ly93d3cuc21zYWxlcnQuY28uaW4vYXBpL3B1c2guanNvbg=='), $post_data);
			if($response_arr['status']=='error') {
			$error = (is_array($response_arr['description'])) ? $response_arr['description']['desc'] : $response_arr['description'];
			if($error == "Invalid Template Match")
			{
				$this->sendtemplatemismatchemail($message);
			}
			return $response_arr;
		}
	}
	}
	
	public function sendOtp($receiver,$template=NULL) {
		$error='';$response_arr=array();
		if (!$this->config->get('smsalert_auth_key') || !$this->config->get('smsalert_auth_secret')) {
			$response_arr['error']='There was an error in sending the OTP to the given Phone Number. Please Try Again or contact site Admin. If you are the website admin, please browse <a href="https://kb.smsalert.co.in/knowledgebase/unable-to-send-otp-from-opencart3-smsalert-extension/" target="_blank"> here</a> for steps to resolve this error.';
					return $response_arr;
		}
		$template = strip_tags(str_replace(array('[url]'), array(HTTPS_SERVER), html_entity_decode($template)));
     	/*added on 06-05-2019 */
		$country_code=$this->config->get('smsalert_country');
		$no = preg_replace('/[^0-9]/', '', $receiver);
		$no = ltrim($no, '0');
		$no = trim($no, ' ');
		$match = preg_match($this->getPhonePattern(),$no);
		/*closed added on 06-05-2019 */
		if($match)
		{
			$no = (substr($no,0,strlen($country_code))!=$country_code) ? $country_code.$no : $no;
			$cookie_value = $this->get_smsalert_cookie($no);
		    $max_otp_resend_allowed = $this->config->get('smsalert_resend_allowed')!=''?$this->config->get('smsalert_resend_allowed'):'4';
			if($this->get_smsalert_cookie($no)>$max_otp_resend_allowed)
			{
				$data=array();
				$data['error']= 'You have reached to max allowed resend, please try after 15 minutes';
				return $data;
			}
			$post_data = array(
				'user'			=> $this->config->get('smsalert_auth_key'),
				'pwd'			=> $this->config->get('smsalert_auth_secret'),
				'sender'		=> $this->config->get('smsalert_default_senderid'),
				'mobileno'		=> $no,
				'template'			=> $template
			);
			$response_arr = $this->sendCurl(base64_decode('aHR0cHM6Ly93d3cuc21zYWxlcnQuY28uaW4vYXBpL212ZXJpZnkuanNvbg=='), $post_data);
			if($response_arr['status']=='error') {
			$error = (is_array($response_arr['description'])) ? $response_arr['description']['desc'] : $response_arr['description'];
			if($error == "Invalid Template Match")
			{
				$this->sendtemplatemismatchemail($template);
				$error = 'There was an error in sending the OTP to the given Phone Number. Please Try Again or contact site Admin. If you are the website admin, please browse <a href="https://kb.smsalert.co.in/knowledgebase/unable-to-send-otp-from-opencart3-smsalert-extension/" target="_blank"> here</a> for steps to resolve this error.';
			}
		   }
		   else{
			   $this->create_smsalert_cookie($no,$cookie_value+1);
		   }
		}
		else{
			$error = 'Invalid mobile number.';
		}
		 $response_arr['error']=$error;
					return $response_arr;
	}

    public function sendtemplatemismatchemail($template)
	{
		$username = $this->config->get('smsalert_auth_key');
		$To_mail=$this->config->get('smsalert_alert_email');
		$emailid = explode(',',$To_mail);
		if(!empty($emailid))
		{
		//Email template with content
		$data = array(
                'template' => nl2br($template),
                'username' => $username,
                'server_name' => $_SERVER['SERVER_NAME']
        );
		$mail = new Mail();
		$mail->protocol = $this->config->get('config_mail_protocol');
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($To_mail);
		$mail->setFrom('support@cozyvision.com');
		$mail->setSender(html_entity_decode('SMS Alert', ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode('❗ ✱ SMS Alert ✱ Template Mismatch', ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($this->load->view('extension/module/smsalert_mismatch_template', $data));
		$mail->send();
		}
	}

	public function verifyOtp($receiver,$code) {
		
		if (!$this->config->get('smsalert_auth_key') || !$this->config->get('smsalert_auth_secret')) {
			return;
		}
		
		
		/*added on 06-05-2019 */
		$country_code=$this->config->get('smsalert_country');
	    $no = preg_replace('/[^0-9]/', '', $receiver);
		$no = ltrim($no, '0');
		$no = trim($no, ' ');
		$no = (substr($no,0,strlen($country_code))!=$country_code) ? $country_code.$no : $no;
		$match = preg_match($this->getPhonePattern(),$no);
		/*closed added on 06-05-2019 */
		if($match)
		{
			$post_data = array(
				'user'			=> $this->config->get('smsalert_auth_key'),
				'pwd'			=> $this->config->get('smsalert_auth_secret'),
				'sender'		=> $this->config->get('smsalert_default_senderid'),
				'mobileno'		=> $no,
				'code'=>$code
				//'text'			=> $message
			);
			$content = $this->sendCurl(base64_decode('aHR0cHM6Ly93d3cuc21zYWxlcnQuY28uaW4vYXBpL212ZXJpZnkuanNvbg=='), $post_data);
			if(isset($content['description']['desc']) && strcasecmp($content['description']['desc'], 'Code Matched successfully.') == 0) {
			  $this->clear_smsalert_cookie($no);
			}
			return $content;
		}
	}
 function create_smsalert_cookie($cookie_key,$cookie_value)
{
	ob_start();
	setcookie($cookie_key,$cookie_value, time()+(15 * 60));
	ob_get_clean();
}
	
function clear_smsalert_cookie($cookie_key)
{	
	if(isset($_COOKIE[$cookie_key])){
		unset($_COOKIE[$cookie_key]);
		setcookie( $cookie_key, '', time() - ( 15 * 60 ) );
	}
}

function get_smsalert_cookie($cookie_key)
{
	if(!isset($_COOKIE[$cookie_key])) {
	  return false;
	} else {
	  return $_COOKIE[$cookie_key];
	}
} 	
}