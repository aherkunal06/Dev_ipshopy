<?php
class ModelVendorVendor extends Model {
	public function addVendor($data) {
		/* 05 02 2021 Add  bank_detail */
		
		// added code for show the username on 21-05-2025
		//jayashri
		$registered_by_username = isset($data['registered_by']) ? $data['registered_by'] : '';
		
		// update following query to show username 21-05-2025
		$sql="INSERT INTO " . DB_PREFIX . "vendor set display_name='".$this->db->escape($data['display_name'])."',firstname='".$this->db->escape($data['firstname'])."',lastname='".$this->db->escape($data['lastname'])."',email='".$this->db->escape($data['email'])."',image='".$this->db->escape($data['image'])."',telephone='".$this->db->escape($data['telephone'])."',gstin='".$this->db->escape($data['gstin'])."',about='".$this->db->escape($data['about'])."',company='".$this->db->escape($data['company'])."',address_1='".$this->db->escape($data['address_1'])."',address_2='".$this->db->escape($data['address_2'])."',status='".(int)$data['status']."',product_status='".(int)$data['product_status']."',city='".$this->db->escape($data['city'])."',postcode='".$this->db->escape($data['postcode'])."',country_id='".$this->db->escape($data['country_id'])."',zone_id='".$this->db->escape($data['zone_id'])."',salt = '" . $this->db->escape($salt = token(9)) . "',password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "',logo='".$data['logo']."',store_about='".$this->db->escape($data['store_about'])."',bank_detail='".$this->db->escape($data['bank_detail'])."',banner='".$data['banner']."',payment_method='".$this->db->escape($data['payment_method'])."',paypal = '" . $this->db->escape($data['paypal']) . "',
		bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "',tax_number='".$this->db->escape($data['tax_number'])."',shipping_charge='".$this->db->escape($data['shipping_charge'])."',commission='".$this->db->escape($data['commission'])."',facebook_url = '" . $this->db->escape($data['facebook_url']) . "',google_url = '" . $this->db->escape($data['google_url']) . "',
		registered_by = '" . $this->db->escape($registered_by_username) . "', date_added=now()";
		$this->db->query($sql);
		
		$vendor_id = $this->db->getLastId();
				
		if (isset($data['store_description'])) {
			foreach ($data['store_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_description SET vendor_id='".(int)$vendor_id."',language_id = '" . (int)$language_id ."',name='".$this->db->escape($value['name'])."',description='".$this->db->escape($value['description'])."',meta_description='".$this->db->escape($value['meta_description'])."',meta_keyword='".$this->db->escape($value['meta_keyword'])."',shipping_policy='".$this->db->escape($value['shipping_policy'])."',return_policy='".$this->db->escape($value['return_policy'])."'"); 
			}
		}

	
		if (isset($data['chatstatus'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_chatsystem set message = '" . $this->db->escape($data['message']) . "',vendor_id='".(int)$vendor_id."',chatstatus='".$data['chatstatus']."'");
		}


		return $vendor_id;
	}

	public function editVendor($vendor_id, $data) {
		/* 05 02 2021 Add  bank_detail */
		$sql="update " . DB_PREFIX . "vendor set display_name='".$this->db->escape($data['display_name'])."', email='".$this->db->escape($data['email'])."',firstname='".$this->db->escape($data['firstname'])."',lastname='".$this->db->escape($data['lastname'])."',image='".$this->db->escape($data['image'])."',postcode='".$this->db->escape($data['postcode'])."',telephone='".$this->db->escape($data['telephone'])."',gstin='".$this->db->escape($data['gstin'])."',about='".$this->db->escape($data['about'])."',company='".$this->db->escape($data['company'])."',address_1='".$this->db->escape($data['address_1'])."',address_2='".$this->db->escape($data['address_2'])."',status='".(int)$data['status']."',product_status='".(int)$data['product_status']."',city='".$this->db->escape($data['city'])."',country_id='".$this->db->escape($data['country_id'])."',zone_id='".$this->db->escape($data['zone_id'])."',logo='".$data['logo']."',store_about='".$this->db->escape($data['store_about'])."', bank_detail='".$this->db->escape($data['bank_detail'])."',banner='".$data['banner']."',payment_method='".$this->db->escape($data['payment_method'])."',paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "',tax_number='".$this->db->escape($data['tax_number'])."',shipping_charge='".$this->db->escape($data['shipping_charge'])."',commission='".$this->db->escape($data['commission'])."',facebook_url = '" . $this->db->escape($data['facebook_url']) . "',google_url = '" . $this->db->escape($data['google_url']) . "',map_url = '" . $this->db->escape($data['map_url']) . "',
		date_modified=now() where vendor_id='".(int)$vendor_id."'";
	 	$this->db->query($sql);
		
		if ($data['password']) {
			$this->db->query("UPDATE " . DB_PREFIX . "vendor SET salt = '" . $this->db->escape($salt = token(9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE vendor_id = '" . (int)$vendor_id . "'");
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_description WHERE vendor_id = '" . (int)$vendor_id . "'");
		
		if (isset($data['store_description'])) {
			foreach ($data['store_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_description SET vendor_id='".(int)$vendor_id."',language_id = '" . (int)$language_id ."',name='".$this->db->escape($value['name'])."',description='".$this->db->escape($value['description'])."',meta_description='".$this->db->escape($value['meta_description'])."',meta_keyword='".$this->db->escape($value['meta_keyword'])."',shipping_policy='".$this->db->escape($value['shipping_policy'])."',return_policy='".$this->db->escape($value['return_policy'])."'"); 
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'vendor_id=" . (int)$vendor_id . "'");
		
		if (isset($data['vendor_seo_url'])) {
			foreach ($data['vendor_seo_url']as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'vendor_id=" . (int)$vendor_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_chatsystem WHERE vendor_id = '" . (int)$vendor_id . "'");
		
		if (isset($data['chatstatus'])) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_chatsystem set message = '" . $this->db->escape($data['message']) . "',vendor_id='".(int)$vendor_id."',chatstatus='".$data['chatstatus']."'");
		}
	
		
	}

	public function approve($vendor_id){
		
		$this->db->query("UPDATE " . DB_PREFIX . "vendor SET approved = '1' WHERE vendor_id = '" . (int)$vendor_id . "'");
		
     
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX ."vendor_to_product WHERE vendor_id='".(int)$vendor_id."'");
		foreach ($query->rows as $result) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET status = '1' WHERE product_id = '" . (int)$result['product_id'] . "'");
	 	}
		
		/// Seller Approve To Mail ///
		
		$this->load->model('vendor/mail');
		$sellertype = 'seller_approve_mail';
		
		$mailinfo = $this->model_vendor_mail->getMailInfo($sellertype);
		$seller_info = $this->model_vendor_vendor->getVendor($vendor_id);
		
		/*Status Enabled*/
		if(isset($mailinfo['sellertype'])){
			$find = array(
				'{vendorname}',										
				'{emails}',										
				'{loginlink}'										
			);
			
			if(isset($seller_info['display_name'])) {
				$dname = $seller_info['display_name'];
			} else {
				$dname='';
			}
			
			if(isset($seller_info['email'])) {
				$emails = $seller_info['email'];
			} else {
				$emails='';
			}
			
			$replace = array(
				'vendorname'=> $dname,
				'emails'	=> $emails,
				'loginlink' => HTTP_CATALOG.'index.php?route=vendor/login' . "\n\n"
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
	}
	public function Disapprove($vendor_id){
		
		$this->db->query("UPDATE " . DB_PREFIX . "vendor SET approved = '0' WHERE vendor_id = '" . (int)$vendor_id . "'");
		
      
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX ."vendor_to_product WHERE vendor_id='".(int)$vendor_id."'");
		foreach ($query->rows as $result) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET status = '0' WHERE product_id = '" . (int)$result['product_id'] . "'");
	 	}
		
		/// Seller Approve To Mail ///
		
		$this->load->model('vendor/mail');
		$sellertype = 'seller_disapprove_mail';
		
		$mailinfo = $this->model_vendor_mail->getMailInfo($sellertype);
		$seller_info = $this->model_vendor_vendor->getVendor($vendor_id);
		
		/*Status Enabled*/
		if(isset($mailinfo['sellertype'])){
			$find = array(			
				'{vendorname}',					
				'{email}',										
				'{loginlink}'										
			);
			
			if(isset($seller_info['displa_name'])) {
				$disname = $seller_info['display_name'];
			} else {
				$disname='';
			}
			
			if(isset($seller_info['email'])) {
				$email = $seller_info['email'];
			} else {
				$email='';
			}
			
			$replace = array(
				'vendorname'		=> $disname,
				'email'	=> $email,
				'loginlink' => HTTP_CATALOG.'index.php?route=vendor/login' . "\n\n"
			);
			
			if(!empty($mailinfo['message'])){
				$messages = $mailinfo['message'];
			} else {
				$messages='';
			}
			
			$subject = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['subject']))));

			$message = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $messages))));
			
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($email);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setHtml(html_entity_decode($message));
			$mail->send();
					
		}
	}
	
	public function deleteVendor($vendor_id) {
		$this->getVendoproductdelete($vendor_id);
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor WHERE vendor_id = '" . (int)$vendor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_description WHERE vendor_id = '" . (int)$vendor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_chatsystem WHERE vendor_id = '" . (int)$vendor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id = '" . (int)$vendor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_review WHERE vendor_id = '" . (int)$vendor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_inquiry WHERE vendor_id = '" . (int)$vendor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "shipping WHERE vendor_id = '" . (int)$vendor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'vendor_id=" . (int)$vendor_id . "'");
	}
    
    public function getVendoproductdelete($vendor_id) {
		$delproduct_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX ."vendor_to_product WHERE vendor_id='".(int)$vendor_id."'");
		foreach ($query->rows as $result) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET status = '0' WHERE product_id = '" . (int)$result['product_id'] . "'");
	 	}
	}

	
	public function getVendor($vendor_id){
		
		$sql = "SELECT DISTINCT *,v.vendor_id as vendor_id, CONCAT(v.firstname, ' ', v.lastname) AS vname FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v.vendor_id = vd.vendor_id) WHERE v.vendor_id='".(int)$vendor_id."' AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query=$this->db->query($sql);
		
		return $query->row;
	}

	
	public function getVendorStoreDescriptions($vendor_id) {
		$store_descriptio_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX ."vendor_description WHERE vendor_id = '" . (int)$vendor_id . "'");
		foreach ($query->rows as $result) {
			$store_descriptio_data[$result['language_id']] = array(
				'name'=> $result['name'],
				'meta_keyword'=> $result['meta_keyword'],
				'description'=> $result['description'],
				'meta_description'=> $result['meta_description'],
				'shipping_policy'=> $result['shipping_policy'],
				'return_policy'=> $result['return_policy'],
			);
	 	}
		return $store_descriptio_data;
	}

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

	public function getTotalVendor($data) {
		$sql ="SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor where vendor_id<>0";
		// added logic to show the count of assign sellers to the perticular user
		 $allowed_users = [2,34,33,36,35,32,31,30,29,28,27,26,25,24,23,22,21,20,19,18,17,16,15,37,38,39,40,41,42];
	    
		if (!empty($data['user_id']) && !in_array((int)$data['user_id'], $allowed_users)) {
			$sql .= " AND v.vendor_id IN (
				SELECT seller_id 
				FROM " . DB_PREFIX . "assignment 
				WHERE user_id = '" . (int)$data['user_id'] . "'
				AND status = 1
			)";
		}
		//------------------ end here ----------------------
		/* 05 02 2020 */
		if (!empty($data['filter_firstname'])){
			$sql .=" and CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_firstname'])."%'";
		}
		if (!empty($data['filter_name'])){
		 	$sql .=" and CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_name'])."%'";
		}
		
		if (isset($data['filter_approved'])){
		 	$sql .=" and approved like '".$this->db->escape($data['filter_approved'])."%'";
		}
		
		/* 05 02 2020 */
		if (isset($data['filter_status'])){
		 	$sql .=" and status like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		
		if (!empty($data['filter_date'])){
		 	$sql .=" and date_added like '".$this->db->escape($data['filter_date'])."%'";
		}
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getVendorByEmail($email) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		return $query->row;
	}
	
	public function getVendorSeoUrls($vendor_id) {
		$vendor_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'vendor_id=" . (int)$vendor_id . "'");

		foreach ($query->rows as $result) {
			$vendor_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $vendor_seo_url_data;
	}


	public function getmsg($vendor_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_chatsystem WHERE vendor_id='".(int)$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}	
	
	/* 03 10 2019 s */
	public function getVendorid($product_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_to_product WHERE product_id='".(int)$product_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	/* 03 10 2019 e */
	
	
	public function getVendorsStore($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_description where vendor_id<>0 AND language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY name";
		
		$query = $this->db->query($sql);

		return $query->rows;
	}
	/* 11 02 2020 */
	
		public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}
	public function getProductOptionValue($product_id, $product_option_value_id) {
		$query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}
	
	
	public function getCustomers($data = array()) {
		$sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id)";
		
		if (!empty($data['filter_affiliate'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "customer_affiliate ca ON (c.customer_id = ca.customer_id)";
		}		
		
		$sql .= " WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		$implode = array();

		if (!empty($data['filter_customer'])) {
			$implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if ($implode) {
			$sql .= " AND " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.ip',
			'c.date_added'
		);

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
		
	public function getVendorProducts($vendor_id) {
		$sql ="SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_to_product WHERE vendor_id='".(int)$vendor_id."'";
		
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getVendorsStorename($vendor_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_description WHERE vendor_id='".(int)$vendor_id."' AND language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY name";
		
		$query = $this->db->query($sql);

		return $query->row;
	}
	/* 11 02 2020 */
	
	public function getAllVendorsForDropdown() {
        $query = $this->db->query("SELECT vendor_id, CONCAT(firstname, ' ', lastname) AS vendor_name FROM " . DB_PREFIX . "vendor ORDER BY vendor_name ASC");
        return $query->rows;
    }
}
