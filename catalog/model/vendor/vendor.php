<?php

// require_once(DIR_SYSTEM . 'library/custom_mailer.php');

class ModelVendorVendor extends Model
{
	public function addVendor($data)
	{
		// $query = $this->db->query("SELECT country_id, zone_id FROM " . DB_PREFIX . "zone WHERE name = '". $this->db->escape($data['name']) . "' LIMIT 1");
		$query = $this->db->query("SELECT country_id, zone_id FROM " . DB_PREFIX . "zone WHERE name = '" . $this->db->escape($data['zone']). "' LIMIT 1");

		// Check if the query was successful and if we have a result
		if ($query->num_rows) {
			$country_id = $query->row['country_id'];
			$zone_id = $query->row['zone_id'];
		} else {
			// Handle the case where no matching zone was found (optional)
			$country_id = 0;  // Default or error handling
			$zone_id = 0; 
		}
		
		// $query = $this->db->query("SELECT zone_id FROM " . DB_PREFIX . "zone WHERE name LIKE '%" . $this->db->escape($data['name']) . "%' LIMIT 1");

		// // Check if the query was successful and if we have a result
		// if ($query->num_rows) {
		// 	$zone_id = $query->row['zone_id'];
		// } else {
		// 	// Handle the case where no matching zone was found (optional)
		// 	$zone_id = 0;  // Default or error handling
		// }
		
        // added code changes for self registered seller on 28/06/2025
        $registered_by = isset($data['registered_by']) ? $data['registered_by'] : 'self registered';
        
		$autoapprovedseller =  $this->config->get('vendor_vendorautoapprove');
        
        // update the query to insert the registered by which user on 28/06/2025
		$sql = "INSERT INTO " . DB_PREFIX . "vendor SET display_name = '" . $this->db->escape($data['display_name']) . "',firstname = '" . $this->db->escape($data['firstname']) . "',lastname = '" . $this->db->escape($data['lastname']) . "',email = '" . $this->db->escape($data['email']) . "',telephone = '" . $this->db->escape($data['telephone']) . "',gstin = '" . $this->db->escape($data['fax'] ?? '') . "',uin = '" . $this->db->escape($data['uin'] ?? '') . "',company = '" . $this->db->escape($data['company']) . "',address_1 = '" . $this->db->escape($data['address_1']) . "',address_2 = '" . $this->db->escape($data['address_2']) . "',map_url = '" . $this->db->escape($data['map_url'] ?? '') . "',city = '" . $this->db->escape($data['city']) . "',country_id = '" . (int)$country_id . "',zone_id = '" . (int)$zone_id . "',salt = '" . $this->db->escape($salt = token(9)) . "',password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "',status = '1',approved = '" . $autoapprovedseller . "',about = '" . $this->db->escape($data['about'] ?? '') . "',image='" . ($data['image'] ?? '') . "',logo='" . ($data['logo'] ?? '') . "',store_about='" . $this->db->escape($data['store_about'] ?? '') . "',bank_detail='" . $this->db->escape($data['bank_detail'] ?? '') . "',banner='" . ($data['banner'] ?? '') . "',payment_method='" . $this->db->escape($data['payment_method'] ?? '') . "',paypal = '" . $this->db->escape($data['paypal'] ?? '') . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "',tax_number='" . $this->db->escape($data['tax_number'] ?? '') . "',shipping_charge='" . $this->db->escape($data['shipping_charge'] ?? '') . "',date_added = NOW(), postcode = '" . $this->db->escape($data['postcode']) . "', registered_by = '" . $this->db->escape($registered_by) . "', pan = '" . $this->db->escape($data['pan'] ?? '') . "'";
		$this->db->query($sql);
		$vendor_id = $this->db->getLastId();

		if (isset($data['store_description'])) {
			foreach ($data['store_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_description SET vendor_id='" . (int)$vendor_id . "',language_id = '" . (int)$language_id . "',name='" . $this->db->escape($value['name']) . "',description='" . $this->db->escape($value['description']) . "',meta_description='" . $this->db->escape($value['meta_description']) . "',meta_keyword='" . $this->db->escape($value['meta_keyword']) . "',shipping_policy='" . $this->db->escape($value['shipping_policy']) . "',return_policy='" . $this->db->escape($value['return_policy']) . "'");
			}
		}

		// SEO URL
		if (isset($data['vendor_seo_url'])) {
			foreach ($data['vendor_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'vendor_id=" . (int)$vendor_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		if (isset($data['chatstatus'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_chatsystem set message = '" . $this->db->escape($data['message']) . "',vendor_id='" . (int)$vendor_id . "',chatstatus='" . $data['chatstatus'] . "'");
		}

		/// Seller Signup To Mail ///
		$this->load->model('vendor/mail');
		$sellertype = 'seller_signup_mail';

		$mailinfo = $this->model_vendor_mail->getMailInfo($sellertype);

		/*Status Enabled*/
		if (!empty($mailinfo['status'])) {
			$this->load->model('localisation/country');
			$this->load->model('localisation/zone');
			if (isset($data['country_id'])) {
                $country_info = $this->model_localisation_country->getCountry($data['country_id']);
                $countryname = isset($country_info['name']) ? $country_info['name'] : '';
            } else {
                $countryname = '';
            }

			if (isset($data['zone_id'])) {
                $zone_info = $this->model_localisation_zone->getZone($data['zone_id']);
                $zonename = isset($zone_info['name']) ? $zone_info['name'] : '';
            } else {
                $zonename = '';
            }

			$find = array(
				'{vendorname}',
				'{email}',
				'{telephone}',
				'{address_1}',
				'{company}',
				'{countryname}',
				'{zonename}',
				'{city}',
				'{loginlink}'
			);
			$replace = array(
				'vendorname'	=> $data['display_name'],
				'email' 		=> $data['email'],
				'telephone' 	=> $data['telephone'],
				'address_1' 	=> $data['address_1'],
				'company' 		=> $data['company'],
				'countryname' 	=> $countryname,
				'zonename' 		=> $zonename,
				'city' 		    => $data['city'],
				'loginlink' 	=> $this->url->link('vendor/login', '', true) . "\n\n"
			);



			$subject = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['subject']))));

            $message = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['message']))));

            $mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($data['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setHtml(html_entity_decode($message));
			$mail->setText(strip_tags(html_entity_decode($message))); // Fallback
			$mail->send();
			
			// WhatsApp notification template config - 03/05/2025 //
			
			$mobile = $data['telephone']; // without country code
			$name = $data['firstname'];
    		$notification = "🎉 Congratulations! 🎉\n\nGreetings, *$name*\nWe are excited to let you know that you have successfully registered as a seller on *ipshopy.com*.\nYou can now start listing your products and reach customers from all around. We are here to help you every step of the way!\nAdditionally, we have sent you an email notification regarding your registration. We kindly ask you to check your inbox, and if you don't see it there, (_please have a look in your spam folder_).\nIf you have any questions or need assistance, feel free to reach out to us.\n\nWelcome aboard and happy selling!🎊\n\nWarm Regards,\n*Ipshopy Team*";
    		$this->sendWhatsAppMessage($mobile, $notification);

		return $vendor_id;
		}
    		// Send to main admin email if new vendor email is enabled		
    		$this->load->language('vendor/seller_order');
    
    		if (!empty($mailinfo['subject'])) {
    			$vendorsubject = $mailinfo['subject'];
    		} else {
    			$vendorsubject = $this->language->get('text_signup1');
    		}

		$message  = $this->language->get('text_signup1') . "\n\n";
		$message .= $this->language->get('text_website') . ' ' . html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8') . "\n";
		$message .= $this->language->get('text_firstname') . ' ' . $data['firstname'] . "\n";
		$message .= $this->language->get('text_lastname') . ' ' . $data['lastname'] . "\n";

		$message .= $this->language->get('text_email') . ' '  .  $data['email'] . "\n";
		$message .= $this->language->get('text_telephone') . ' ' . $data['telephone'] . "\n";
		$subject1 = sprintf(html_entity_decode($vendorsubject, ENT_QUOTES, 'UTF-8'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$mail->setTo($this->config->get('config_email'));
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject($subject1);
		$mail->setText(html_entity_decode($message));
		$mail->send();

		return $vendor_id;

		/// Seller Signup To Mail ///		
		
	}
	
	public function sendWhatsAppMessage($telephone, $message) { 

		// API credentials
		$instance_id = '6811E85A26FEC';
		$access_token = '68106bf521ccc';
	
		// Format telephone (if needed, like adding country code)
		$formatted_number = '91' . ltrim($telephone, '0');  // assuming Indian numbers and removing leading 0
	
		// Build the API URL  . ltrim($telephone, '0')
		$vendor_api_url = 'https://waclient.com/api/send?' . http_build_query([
			'number'       => $formatted_number,
			'type'         => 'text',
			'message'      => $message,
			'instance_id'  => $instance_id,
			'access_token' => $access_token,
		]);
	
		// Initialize cURL
		$ch = curl_init($vendor_api_url);
	
		// Setup cURL options
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
		// Execute cURL request
		$response = curl_exec($ch);
	
		// Check for errors
		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
			error_log('WhatsApp API error: ' . $error_msg);
		}
	
		// Close cURL
		curl_close($ch);
	
		// Optional: Log response
		// error_log('WhatsApp API response: ' . $response);
	
		return $response;
	}
	

	public function editVendor($vendor_id, $data)
	{

        // update the following query add the signature column on 08-04-2025----------
		$sql = "update " . DB_PREFIX . "vendor set display_name = '" . $this->db->escape($data['display_name']) . "',firstname = '" . $this->db->escape($data['firstname']) . "',lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "',telephone = '" . $this->db->escape($data['telephone']) . "',gstin = '" . $this->db->escape($data['gstin']) . "',company = '" . $this->db->escape($data['company']) . "',address_1 = '" . $this->db->escape($data['address_1']) . "',address_2 = '" . $this->db->escape($data['address_2']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', map_url = '" . $this->db->escape($data['map_url']) . "',facebook_url = '" . $this->db->escape($data['facebook_url']) . "',google_url = '" . $this->db->escape($data['google_url']) . "',city = '" . $this->db->escape($data['city']) . "',country_id = '" . (int)$data['country_id'] . "',zone_id = '" . (int)$data['zone_id'] . "',about = '" . $this->db->escape($data['about']) . "',image='" . $data['image'] . "',logo='" . $data['logo'] . "',store_about='" . $this->db->escape($data['store_about']) . "',bank_detail='" . $this->db->escape($data['bank_detail']) . "',banner='" . $data['banner'] . "',payment_method='" . $this->db->escape($data['payment_method']) . "',paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "',tax_number='" . $this->db->escape($data['tax_number']) . "',shipping_charge='" . $this->db->escape($data['shipping_charge']) . "',store_logowidth='" . $this->db->escape($data['store_logowidth']) . "', store_logoheight='" . $this->db->escape($data['store_logoheight']) . "', store_bannerwidth='" . $this->db->escape($data['store_bannerwidth']) . "',  store_bannerheight='" . $this->db->escape($data['store_bannerheight']) . "', status = '1',approved = '1', signature = '" . $data[ 'sign_image' ] . "' where vendor_id='" . (int)$this->vendor->getId() . "'";

		$this->db->query($sql);

		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_description WHERE vendor_id = '" . (int)$vendor_id . "'");
		if (isset($data['store_description'])) {
			foreach ($data['store_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_description SET vendor_id='" . (int)$vendor_id . "',language_id = '" . (int)$language_id . "',name='" . $this->db->escape($value['name']) . "',description='" . $this->db->escape($value['description']) . "',meta_description='" . $this->db->escape($value['meta_description']) . "',meta_keyword='" . $this->db->escape($value['meta_keyword']) . "',shipping_policy='" . $this->db->escape($value['shipping_policy']) . "',return_policy='" . $this->db->escape($value['return_policy']) . "'");
			}
		}

		// SEO URL

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'vendor_id=" . (int)$vendor_id . "'");

		if (isset($data['vendor_seo_url'])) {
			foreach ($data['vendor_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'vendor_id=" . (int)$vendor_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_chatsystem WHERE vendor_id = '" . (int)$vendor_id . "'");
		if (isset($data['chatstatus'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_chatsystem set message = '" . $this->db->escape($data['message']) . "',vendor_id='" . (int)$vendor_id . "',chatstatus='" . $data['chatstatus'] . "'");
		}
	}

	public function verifyPassword($vendor_id, $data)
	{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor WHERE vendor_id = '" . (int)$this->vendor->getId() . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($data['oldpassword']) . "'))))) OR password = '" . $this->db->escape(md5($data['oldpassword'])) . "')");

		return $query->row['total'];
	}

	public function editPassword($vendor_id, $data)
	{
		$sql = "update " . DB_PREFIX . "vendor SET salt = '" . $this->db->escape($salt = token(9)) . "',password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "',date_modified=now() WHERE vendor_id = '" . (int)$this->vendor->getId() . "'";
		$query = $this->db->query($sql);
	}

	public function getVendorStoreDescriptions($vendor_id)
	{
		$store_descriptio_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_description WHERE vendor_id = '" . (int)$this->vendor->getId() . "'");
		foreach ($query->rows as $result) {
			$store_descriptio_data[$result['language_id']] = array(
				'name' => $result['name'],
				'meta_keyword' => $result['meta_keyword'],
				'description' => $result['description'],
				'meta_description' => $result['meta_description'],
				'shipping_policy' => $result['shipping_policy'],
				'return_policy' => $result['return_policy'],
			);
		}
		return $store_descriptio_data;
	}

	public function getOrderStatus($order_status_id)
	{
		$sql = "select * from " . DB_PREFIX . "order_status where order_status_id='" . (int)$order_status_id . "' and language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}


	public function getCustomerOrder($order_status_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_vendorhistory WHERE order_status_id = '" . (int)$order_status_id . "'");
		return $query->row;
	}


	public function getCustomerOrderStatus($order_status_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "'");

		return $query->row;
	}

	public function getVendor($vendor_id = 0)
	{
		$sql = "SELECT *,v.vendor_id as vendor_id FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v.vendor_id = vd.vendor_id) LEFT JOIN " . DB_PREFIX . "vendor_review vr ON(v.vendor_id = vr.vendor_id) WHERE v.vendor_id='" . (int)$vendor_id . "' AND v.approved!=0 AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql);

		return $query->row;
	}

	public function getStorename($vendor_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v.vendor_id = vd.vendor_id) WHERE v.vendor_id='" . (int)$vendor_id . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getVendorLogo($vendor_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v.vendor_id = vd.vendor_id) WHERE v.vendor_id='" . (int)$this->vendor->getId() . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getVendors($data)
	{
		$sql = "select * from " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd on(v.vendor_id = vd.vendor_id) where v.vendor_id<>0  AND v.approved!=0  AND v.status!=0  AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "' order by v.vendor_id desc ";

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

	public function getVendorByEmail($email)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		return $query->row;
	}

	public function getTotalVendorByEmail($email)
	{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		return $query->row['total'];
	}

	public function getTotalReviews($vendor_id)
	{
		$sql = "select count(*) as total from " . DB_PREFIX . "vendor_review where vendor_id='" . (int)$this->vendor->getId() . "' AND status=1";

		if (isset($data['vendor_id'])) {
			$sql .= " and vendor_id='" . (int)$vendor_id . "'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotalOrders($vendor_id)
	{

		$implode = array();


		$vendorstatus = $this->config->get('vendor_showorder_status');
		$defaultstatus = $this->config->get('config_complete_status');

		if (!empty($vendorstatus)) {
			foreach ($vendorstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
			}
		} else {
			foreach ($defaultstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
			}
		}

		$sql = "select count(*) as total from " . DB_PREFIX . "vendor_order_product vop  LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) WHERE vop.vendor_id='" . $this->vendor->getId() . "' AND vop.order_status_id IN(" . implode(",", $implode) . ")";

		if (isset($data['vendor_id'])) {
			$sql .= " and vop.vendor_id='" . (int)$vendor_id . "'";
		}
		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getOrder($order_id)
	{

		$sql = "select * from `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "vendor_order_product vop ON (o.order_id = vop.order_id)  where o.order_id='" . (int)$order_id . "' AND vop.vendor_id!=0";

		$query = $this->db->query($sql);

		return $query->row;
	}

	public function getOrderProduct($order_product_id)
	{
		$sql = "select * from " . DB_PREFIX . "vendor_order_product where order_product_id='" . (int)$order_product_id . "'";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getOrderTotals($order_id)
	{
		$sql = "select * from " . DB_PREFIX . "order_total where order_id='" . (int)$order_id . "'";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getOrders($data)
	{

		$implode = array();

		$vendorstatus = $this->config->get('vendor_showorder_status');
		$defaultstatus = $this->config->get('config_complete_status');

		if (!empty($vendorstatus)) {
			foreach ($vendorstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
			}
		} else {
			foreach ($defaultstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
			}
		}


		$sql = "SELECT *, CONCAT(o.firstname, ' ', o.lastname) AS name, vop.order_status_id, vop.total as vtotal, vop.total as ptotal FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) WHERE vop.vendor_id!=0 AND  vop.order_status_id IN(" . implode(",", $implode) . ")";


		if (isset($data['vendor_id'])) {
			$sql .= " and vop.vendor_id='" . (int)$data['vendor_id'] . "'";
		}

		$sql .= " GROUP by vop.order_id";

		$sql .= " ORDER BY  vop.order_id DESC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getAboutStore($vendor_id)
	{
		$sql = "select * from " . DB_PREFIX . "vendor where vendor_id='" . (int)$vendor_id . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getReviewField($review_id)
	{
		$sql = "select * from " . DB_PREFIX . "vendor_review_field_submit where review_id='" . (int)$review_id . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getReviews($data)
	{
		$sql = "select * from " . DB_PREFIX . "vendor_review where review_id<>0";

		if (isset($data['vendor_id'])) {
			$sql .= " and vendor_id='" . (int)$data['vendor_id'] . "'";
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

	// Seller Review Start ///	
	public function getFieldReviews($vendor_id)
	{
		$sql = "select * from " . DB_PREFIX . "vendor_review WHERE vendor_id='" . (int)$vendor_id . "' AND status =1";

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

	public function getField($review_id, $vendor_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_review_field_submit vrfs LEFT JOIN " . DB_PREFIX . "vendor_review_field_description vrfd ON (vrfs.rf_id = vrfd.rf_id) LEFT JOIN " . DB_PREFIX . "vendor_review vr ON (vrfs.review_id = vr.review_id) WHERE vrfs.vendor_id = '" . (int)$vendor_id . "' AND vrfd.language_id = '" . (int)$this->config->get('config_language_id') . "' and vrfs.review_id='" . (int)$review_id . "'";

		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getProductReview($vendor_id)
	{
		$sql = "select * from " . DB_PREFIX . "vendor_to_review where vendor_id='" . (int)$this->vendor->getId() . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getProductReviews($vendor_id)
	{
		$sql = "select * from " . DB_PREFIX . "vendor_to_review where vendor_id='" . (int)$vendor_id . "'";

		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getReviewFields($data)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_review_field vrf LEFT JOIN " . DB_PREFIX . "vendor_review_field_description vrfd ON (vrf.rf_id = vrfd.rf_id) WHERE vrfd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($data['filter_status'])) {
			$sql .= " and vrf.status like '" . $this->db->escape($data['filter_status']) . "%'";
		}

		if (isset($data['filter_name'])) {
			$sql .= " and vrfd.field_name like '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'vrfd.field_name',
			'vrf.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY vrfd.field_name";
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$sql .= " ASC";
		} else {
			$sql .= " DESC";
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

	public function getProduct($product_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' and p.product_id='" . (int)$product_id . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function addReview($data, $vendor_id)
	{
		$sql = "INSERT INTO " . DB_PREFIX . "vendor_review set text='" . $this->db->escape($data['text']) . "',vendor_id='" . (int)$vendor_id . "',customer_id='" . (int)$this->customer->getId() . "',status=1,date_added=now()";
		$this->db->query($sql);
		$review_id = $this->db->getLastId();

		if (isset($data['reviewfield'])) {
			foreach ($data['reviewfield'] as $key => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_review_field_submit SET 
				review_id ='" . (int)$review_id . "',
				rf_id ='" . (int)$key . "',
				vendor_id='" . (int)$vendor_id . "',
				customer_id='" . (int)$this->customer->getId() . "',
				value='" . $this->db->escape($value) . "'
				");
			}
		}

		$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_to_review SET review_id = '" . (int)$review_id . "', vendor_id = '" . (int)$this->vendor->getId() . "'");
	}

	public function getFieldSubmits($review_id)
	{
		$form_field_data = array();

		$form_field_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_review_field_submit where review_id='" . (int)$review_id . "'");

		foreach ($form_field_query->rows as $key => $form_field) {

			$form_field_data[] = array(
				'rf_id' 		=> $form_field['rf_id'],
				'review_id' 		=> $form_field['review_id'],
				'value' 		=> $form_field['value']
			);
		}
		return $form_field_data;
	}

	public function getTotalSellerReview($vendor_id)
	{
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_review WHERE vendor_id='" . (int)$vendor_id . "' AND status=1 AND review_id<>0";

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotalCollections($vendor_id)
	{

		$sql = "SELECT count(*) as total FROM " . DB_PREFIX . "vendor_to_product v2p LEFT JOIN " . DB_PREFIX . "product p ON (v2p.product_id = p.product_id) WHERE v2p.vendor_id='" . (int)$vendor_id . "' and status=1";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}


	public function getProRev($product_id)
	{
		$sql = "select * from " . DB_PREFIX . "review where product_id='" . (int)$product_id . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getWriteReview($vendor_id)
	{

		$sql = "select * from " . DB_PREFIX . "vendor_review where customer_id='" . (int)$this->customer->getId() . "' AND vendor_id='" . (int)$vendor_id . "'";

		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getVendorProduct($data)
	{

		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_to_product v2p LEFT JOIN " . DB_PREFIX . "product_to_category v2c ON (v2p.product_id = v2c.product_id) WHERE v2p.vendor_id<>0";

		if (isset($data['vendor_id'])) {
			$sql .= " and vendor_id='" . (int)$data['vendor_id'] . "'";
		}

		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getSellerProduct($product_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_to_product WHERE product_id='" . (int)$product_id . "'";

		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getSellerInfo($vendor_id)
	{
		$sql = "select *, c.name as countryname From " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "country c on(v.country_id = c.country_id) where v.vendor_id='" . (int)$vendor_id . "'";

		$query = $this->db->query($sql);

		return $query->row;
	}


	public function addTracks($order_id, $data)
	{
		$sql = "update " . DB_PREFIX . "vendor_order_product set
		tracking='" . $this->db->escape($data['tracking']) . "',
		date_modified=now() where order_id='" . (int)$order_id . "' and vendor_id ='" . (int)$this->vendor->getId() . "'";
		$this->db->query($sql);
	}

	public function addOrdeStatus($order_id, $data, $vendor_id)
	{

		$sql = "update " . DB_PREFIX . "vendor_order_product set
		order_status_id='" . (int)$data['order_status_id'] . "',
		date_modified=now() where order_id='" . (int)$order_id . "' AND vendor_id='" . (int)$this->vendor->getId() . "'";
		$this->db->query($sql);

		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product where order_id='" . (int)$order_id . "' AND vendor_id ='" . (int)$this->vendor->getId() . "'";
		$query1 = $this->db->query($sql);
		foreach ($query1->rows as $row) {

			$vendorinfo = $row;

			$storename = $this->getStoreNames($this->vendor->getId());

			if (!empty($storename)) {
				$storename = $storename['name'];
			} else {
				$storename = '';
			}


			$this->db->query("INSERT INTO " . DB_PREFIX . "order_vendorhistory SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$data['order_status_id'] . "', vendor_id = '" . (int)$row['vendor_id'] . "',  order_product_id = '" . (int)$row['order_product_id'] . "', comment = '" . $this->db->escape($data['comment']) . "', updateby='" . $storename . "', date_added = NOW()");
		}
		$this->load->model('vendor/mail');
		$this->load->model('vendor/vendor');
		/* Mail to customer update status */
		$this->load->model('checkout/order');

		$sellertype = 'seller_order_status_update_email';

		$mailinfo = $this->model_vendor_mail->getMailInfo($sellertype);

		$sellerorder_info = $this->model_vendor_vendor->getSellerOrder($order_id);
		$seller_info = $this->model_vendor_vendor->getVendor($sellerorder_info['vendor_id']);

		$customer_info = $this->model_checkout_order->getOrder($sellerorder_info['order_id']);
		$status_info = $this->getOrderStatus($sellerorder_info['order_status_id']);


		if (isset($mailinfo['status'])) {

			$find = array(
				'{emails}',
				'{customername}',
				'{statusname}',
				'{trackingno}',
				'{sellername}',
				'{order_id}',

			);


			if (!empty($data['tracking'])) {
				$trackingno = $data['tracking'];
			} else {
				$trackingno = '';
			}

			if (!empty($seller_info['firstname'])) {
				$sellername = $seller_info['firstname'] . ' ' . $seller_info['lastname'];
			} else {
				$sellername = '';
			}

			if (!empty($sellerorder_info['order_id'])) {
				$order_id = $sellerorder_info['order_id'];
			} else {
				$order_id = '';
			}



			if (isset($customer_info['email'])) {
				$emails = $customer_info['email'];
			} else {
				$emails = '';
			}

			if (isset($status_info['name'])) {
				$statusname = $status_info['name'];
			} else {
				$statusname = '';
			}

			if (isset($customer_info['firstname'])) {
				$customernames = $customer_info['firstname'] . ' ' . $customer_info['lastname'];
			} else {
				$customernames = '';
			}

			$replace = array(
				'email' 	=> $emails,
				'customername' 	=> $customernames,
				'statusname' 	=> $statusname,
				'trackingno' 	=> $trackingno,
				'sellername' 	=> $sellername,
				'order_id'   	=> $order_id,

			);

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
		/* Mail to customer update status */

		/* Mail to Admin update status */

		$sellertypeadmin = 'seller_order_status_update_email_admin';

		$mailinfoadmin = $this->model_vendor_mail->getMailInfo($sellertypeadmin);

		$sellerorder_info = $this->model_vendor_vendor->getSellerOrder($order_id);
		$seller_info = $this->model_vendor_vendor->getVendor($sellerorder_info['vendor_id']);

		$customer_info = $this->model_checkout_order->getOrder($sellerorder_info['order_id']);
		$status_info = $this->getOrderStatus($sellerorder_info['order_status_id']);


		if (isset($mailinfoadmin['status'])) {
			$find = array(
				'{sellername}',
				'{emails}',
				'{customername}',
				'{statusname}',
			);

			if (isset($customer_info['email'])) {
				$emails = $customer_info['email'];
			} else {
				$emails = '';
			}
			if (isset($customer_info['firstname'])) {
				$customernames = $customer_info['firstname'] . ' ' . $customer_info['lastname'];
			} else {
				$customernames = '';
			}


			if (isset($seller_info['firstname'])) {
				$sellername = $seller_info['firstname'] . ' ' . $seller_info['lastname'];
			} else {
				$sellername = '';
			}


			if (isset($status_info['name'])) {
				$statusname = $status_info['name'];
			} else {
				$statusname = '';
			}


			$replace = array(
				'sellername' 	=> $sellername,
				'email' 	=> $emails,
				'customername' 	=> $customernames,
				'statusname' 	=> $statusname

			);
			if (!empty($mailinfoadmin['subject'])) {
				$subjectadmin = $mailinfoadmin['subject'];
			} else {
				$subjectadmin = '';
			}

			$adminsubject = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $subjectadmin))));

			$adminmessage = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfoadmin['message']))));

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');


			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($adminsubject);
			$mail->setHtml(html_entity_decode($adminmessage));
			$mail->send();
			/* Mail to Admin update status */
		}
		return $order_id;
	}

	public function getSellerOrder($order_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product where order_id='" . (int)$order_id . "' AND vendor_id='" . (int)$this->vendor->getId() . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getSellerOrders($order_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product where order_id='" . (int)$order_id . "' AND vendor_id='" . (int)$this->vendor->getId() . "'";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getLayouts($data = array())
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "layout";

		$sort_data = array('name');

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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

	public function getTotalCategory($vendor_id, $category_id)
	{
		$sql = "select COUNT(*) as total from " . DB_PREFIX . "product_to_category pc LEFT JOIN " . DB_PREFIX . "vendor_to_product vp on(pc.product_id = vp.product_id) WHERE pc.category_id ='" . (int)$category_id . "' AND vp.vendor_id='" . (int)$vendor_id . "'";

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotalProducts($vendor_id)
	{

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_to_product v2p LEFT JOIN " . DB_PREFIX . "product p ON(v2p.product_id = p.product_id) WHERE v2p.vendor_id='" . (int)$vendor_id . "'";

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getProReview($vendor_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "review pr LEFT JOIN " . DB_PREFIX . "vendor_to_product v2r ON (pr.product_id = v2r.product_id) WHERE v2r.vendor_id='" . (int)$vendor_id . "' and status='1'";

		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getTotalProductReview($vendor_id)
	{
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review pr LEFT JOIN " . DB_PREFIX . "vendor_to_product v2r ON (pr.product_id = v2r.product_id) WHERE v2r.vendor_id='" . (int)$vendor_id . "' and status='1'";

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotalProduct($vendor_id)
	{
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "vendor_to_product v2p ON (p.product_id = v2p.product_id) WHERE v2p.vendor_id='" . (int)$vendor_id . "' and status=1";

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function addFollow($vendor_id, $data)
	{
		$sql = "INSERT INTO " . DB_PREFIX . "vendor_follow set
		customer_id='" . (int)$this->customer->getId() . "',
		vendor_id='" . (int)$data['vendor_id'] . "',
		date_added=now()";
		$this->db->query($sql);
	}

	public function getFollow($vendor_id)
	{
		$sql = "select * from " . DB_PREFIX . "vendor_follow where vendor_id='" . (int)$vendor_id . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	public function getFollows($vendor_id)
	{
		$sql = "select * from " . DB_PREFIX . "vendor_follow where vendor_id='" . (int)$vendor_id . "'";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getDelete($vendor_id)
	{
		$sql = "delete  from " . DB_PREFIX . "vendor_follow where vendor_id='" . (int)$vendor_id . "'";
		$query = $this->db->query($sql);
	}

	public function getTotalFollowers($vendor_id)
	{
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_follow WHERE vendor_id='" . (int)$vendor_id . "'";

		$query = $this->db->query($sql);
		return $query->row['total'];
	}


	public function getVendorSeoUrls($vendor_id)
	{
		$vendor_seo_url_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'vendor_id=" . (int)$vendor_id . "'");

		foreach ($query->rows as $result) {
			$vendor_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $vendor_seo_url_data;
	}



	public function getCustomer($customer_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "customer where customer_id='" . (int)$customer_id . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getCustomerlog($customer_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "customer where customer_id='" . (int)$this->customer->getId() . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getVendorProductFeature($product_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_to_product where product_id='" . (int)$product_id . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getTotalVendors($vendor_id)
	{

		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor WHERE vendor_id = '" . (int)$vendor_id . "' AND approved!=0");

		return $query->row['total'];
	}

	public function getVendorSumValue($vendor_id)
	{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_review_field_submit WHERE vendor_id = '" . (int)$vendor_id . "'");

		$query1 = $this->db->query("SELECT SUM(value) AS total FROM " . DB_PREFIX . "vendor_review_field_submit WHERE vendor_id = '" . (int)$vendor_id . "'");

		if (!empty($query->row['total'])) {
			return $query1->row['total'] / $query->row['total'];
		}
	}

	public function editPasswordemail($email, $password)
	{
		$sql = "update " . DB_PREFIX . "vendor SET salt = '" . $this->db->escape($salt = token(9)) . "',password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($password)))) . "',date_modified=now() WHERE email = '" . $email . "'";
		$query = $this->db->query($sql);
	}


	public function getmsg($vendor_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_chatsystem WHERE vendor_id='" . (int)$this->vendor->getId() . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getChatid($vendor_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_chatsystem where vendor_id='" . (int)$vendor_id . "' and chatstatus=1";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getvendorpro($vendor_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_chatsystem where vendor_id='" . (int)$vendor_id . "' and chatstatus=1";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function Addmessage($data, $vendor_id)
	{
		$sql = "INSERT INTO " . DB_PREFIX . "vendor_chat SET email = '" . $this->db->escape($data['email']) . "',subject = '" . $this->db->escape($data['subject']) . "',message = '" . $this->db->escape($data['message']) . "', attachimage = '" . $this->db->escape($data['attachimage']) . "', vendor_id = '" . (int)$vendor_id . "', customer_id='" . (int)$this->customer->getId() . "', date_added = NOW()";
		$this->db->query($sql);
		$chat_id = $this->db->getLastId();
		$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_adminchat SET customer_id='" . $this->customer->getId() . "', vendor_id='" . (int)$vendor_id . "', subject = '" . $this->db->escape($data['subject']) . "',message = '" . $this->db->escape($data['message']) . "', attachimage = '" . $this->db->escape($data['attachimage']) . "', chat_id = '" . (int)(int)$chat_id . "',  user='customer', type='inbox', date_added = NOW()");
		$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_customerchat SET customer_id='" . (int)$this->customer->getId() . "', vendor_id='" . (int)$vendor_id . "', subject = '" . $this->db->escape($data['subject']) . "',message = '" . $this->db->escape($data['message']) . "', attachimage = '" . $this->db->escape($data['attachimage']) . "', chat_id = '" . (int)$chat_id . "', user='customer', type='outbox', date_added = NOW()");
		$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_vendorchat SET customer_id='" . (int)$this->customer->getId() . "', vendor_id='" . (int)$vendor_id . "', subject = '" . $this->db->escape($data['subject']) . "',message = '" . $this->db->escape($data['message']) . "', attachimage = '" . $this->db->escape($data['attachimage']) . "', chat_id = '" . (int)$chat_id . "', user='customer', type='inbox', date_added = NOW()");


		/// Seller contact Mail ///
		$this->load->model('vendor/mail');
		$sellertype = 'seller_and_customer_contact_email';

		$mailinfo = $this->model_vendor_mail->getMailInfo($sellertype);

		$querymail = $this->db->query("SELECT *  FROM " . DB_PREFIX . "vendor WHERE vendor_id = '" . (int)$vendor_id . "'");
		$vendor_mail = $querymail->row['email'];

		$querycust = $this->db->query("SELECT *  FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$this->customer->getId() . "'");
		$custname = $querycust->row['firstname'];

		/*Status Enabled*/
		if (isset($mailinfo['status'])) {


			$find = array(
				'{name}',
				'{email}',
				'{subject}',
				'{message}'

			);
			$replace = array(
				'name'	=> $custname,
				'email'	=> $data['email'],
				'subject'	=> $data['subject'],
				'message' 		=> $data['message']

			);


			$subject = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['subject']))));

			$message = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['message']))));

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($vendor_mail);
			$mail->setFrom($data['email']);
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setHtml(html_entity_decode($message));
			$mail->send();
		}
	}


	public function getorderproductid($order_id, $vendor_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_order_product WHERE order_id = '" . (int)$order_id . "'  AND vendor_id = '" . (int)$vendor_id . "'");

		return $query->row;
	}

	public function getVendorOrderHistories($order_id, $vendor_id, $start = 0, $limit = 10)
	{
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT *, os.name AS status FROM " . DB_PREFIX . "order_vendorhistory ovh LEFT JOIN " . DB_PREFIX . "order_status os ON ovh.order_status_id = os.order_status_id WHERE ovh.order_id = '" . (int)$order_id . "' AND ovh.vendor_id = '" . (int)$vendor_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ovh.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalOrderHistories($order_id)
	{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_vendorhistory WHERE order_id = '" . (int)$order_id . "' AND vendor_id='" . (int)$this->vendor->getId() . "'");
		return $query->row['total'];
	}


	public function getSellerChat($vendor_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_to_product WHERE vendor_id='" . (int)$vendor_id . "'";

		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getOrderProducts($order_id)
	{
		$query = $this->db->query("
			SELECT op.name, p.image 
			FROM " . DB_PREFIX . "order_product op
			LEFT JOIN " . DB_PREFIX . "product p ON op.product_id = p.product_id
			WHERE op.order_id = '" . (int)$order_id . "'
		");

		return $query->rows;
	}

	public function getOrderProductsName($order_product_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_product_id = '" . (int)$order_product_id . "'");
		return $query->row;
	}

	public function getOrderProductsNames($order_id)
	{
		$query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		return $query->rows;
	}

	public function getOrderProductstatus($order_product_id)
	{
		$query = $this->db->query("SELECT *, os.name AS status FROM " . DB_PREFIX . "order_vendorhistory ov LEFT JOIN " . DB_PREFIX . "order_status os on(ov.order_status_id = os.order_status_id) WHERE ov.order_product_id = '" . (int)$order_product_id . "' order by order_vendorhistory_id DESC limit 0,1");
		return $query->row;
	}

	public function getOrderStoreName($order_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_vendorhistory ovh LEFT JOIN " . DB_PREFIX . "vendor_description vd on (ovh.vendor_id =  vd.vendor_id) WHERE ovh.order_id = '" . (int)$order_id . "'");

		return $query->row;
	}
	public function getStoreNames($vendor_id)
	{
		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "vendor_description WHERE vendor_id = '" . (int)$vendor_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getVOrderHistories($order_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_vendorhistory ovh LEFT JOIN " . DB_PREFIX . "order_status os ON ovh.order_status_id = os.order_status_id WHERE ovh.order_id = '" . (int)$order_id . "'AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ovh.date_added");

		return $query->rows;
	}

	public function getOrderHistories($order_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_vendorhistory ov LEFT JOIN " . DB_PREFIX . "order_history oh ON ov.order_id = oh.order_id WHERE ov.order_id = '" . (int)$order_id . "'");

		return $query->row;
	}

	public function getTotalOrderProductsByOrderId($order_id, $vendor_id)
	{

		$query = $this->db->query("SELECT sum(quantity) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_id = '" . (int)$order_id . "' AND vendor_id = '" . (int)$vendor_id . "'");

		return $query->row['total'];
	}

	public function getTrackingCodeInfo($vendor_id, $order_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id='" . (int)$vendor_id . "' AND order_id='" . (int)$order_id . "' ";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getvendorOrdertotal($vendor_id, $order_id)
	{

		$ordersinfo = $this->getOrder($order_id);

		$sql = "SELECT SUM(total) AS total,SUM(tmdshippingcost) AS shipingcost,sum(tax*quantity) as tax FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id='" . (int)$vendor_id . "' AND order_id='" . (int)$order_id . "'";

		$query = $this->db->query($sql);

		$query->row['total'] += $query->row['tax'] + $query->row['shipingcost'];
		return $query->row;
	}


	public function getOrderProductname($order_id)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->row;
	}

	public function getInProductSellerName($product_id, $vendor_id)
	{
		$sql = "SELECT *,vd.name as storename FROM " . DB_PREFIX . "vendor_to_product vp LEFT JOIN  " . DB_PREFIX . "vendor v ON vp.vendor_id=v.vendor_id LEFT JOIN  " . DB_PREFIX . "vendor_description vd ON v.vendor_id=vd.vendor_id WHERE vp.product_id='" . (int)$product_id . "' AND vp.vendor_id ='" . (int)$vendor_id . "' AND  vd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getSellerOrdertrack($order_id)
	{
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product where order_id='" . (int)$order_id . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getTotalDashboardReviews($vendor_id)
	{
		$sql = "select count(*) as total from " . DB_PREFIX . "vendor_review where vendor_id='" . (int)$this->vendor->getId() . "'";

		if (isset($data['vendor_id'])) {
			$sql .= " and vendor_id='" . (int)$vendor_id . "'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getVendorData($vendor_id = null)
	{
		$sql = "SELECT vendor_id, firstname, lastname, display_name, email, telephone, gstin, company, postcode, address_1, address_2, country_id, zone_id, city FROM " . DB_PREFIX . "vendor";

		// If a vendor ID is provided, filter by that ID
		if ($vendor_id !== null) {
			$sql .= " WHERE vendor_id = '" . (int)$vendor_id . "'";
		}

		$query = $this->db->query($sql);

		if ($query->num_rows) {
			return $vendor_id ? $query->row : $query->rows;  // Return a single row if ID provided, otherwise all rows
		} else {
			return null;
		}
	}

	// send to warehouse data to shipway

	public function getVendorDataByVendorId($vendor_id)
	{
		$query = $this->db->query("SELECT vendor_id, firstname, lastname, display_name, email, telephone, gstin, company, postcode, address_1, address_2, country_id, zone_id, city 
								  FROM " . DB_PREFIX . "vendor WHERE vendor_id = '" . (int)$vendor_id . "'");

		if ($query->num_rows) {
			return $query->row;
		} else {
			return null;
		}
	}

	public function updateShipwayWarehouseId($vendor_id, $warehouse_id)
	{
		$this->db->query("UPDATE " . DB_PREFIX . "vendor 
						  SET shipway_warehouse_id = '" . $this->db->escape($warehouse_id) . "' 
						  WHERE vendor_id = '" . (int)$vendor_id . "'");
	}
	
	public function getVendorFirstName($data) {
    $query = $this->db->query("SELECT `firstname` FROM `" . DB_PREFIX . "vendor` WHERE `vendor_id` = '" . (int)$data['vendor_id'] . "'");
    return $query->row ? $query->row['firstname'] : "seller";
    }
    
    public function getVendorByTelephone($telephone)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor WHERE LOWER(telephone) = '" . $this->db->escape(utf8_strtolower($telephone)) . "'");
		return $query->row;
	}

	public function getTotalVendorByTelephone($telephone)
	{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor WHERE LOWER(telephone) = '" . $this->db->escape(utf8_strtolower($telephone)) . "'");
		return $query->row['total'];
	}
	
	public function getVendorBygstin($gstin)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor WHERE LOWER(gstin) = '" . $this->db->escape(utf8_strtolower($gstin)) . "'");
		return $query->row;
	}

	public function getTotalVendorBygstin($gstin)
	{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor WHERE LOWER(gstin) = '" . $this->db->escape(utf8_strtolower($gstin)) . "'");
		return $query->row['total'];
	}

public function getVendorByPan($pan, $exclude_vendor_id = 0) {
    $pan = strtoupper(trim($pan));
    $sql = "SELECT vendor_id
              FROM " . DB_PREFIX . "vendor
             WHERE pan = '" . $this->db->escape($pan) . "'";

    if ((int)$exclude_vendor_id > 0) {
        $sql .= " AND vendor_id <> " . (int)$exclude_vendor_id;
    }

    $sql .= " LIMIT 1";
    return $this->db->query($sql)->row;
	
}
 

}
