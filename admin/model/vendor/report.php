<?php
class ModelVendorReport extends Model {
	
	public function addTracks($order_product_id, $data) {
		$sql="update " . DB_PREFIX . "vendor_order_product set
		tracking='".(int)$data['tracking']."',
		date_modified=now() where order_product_id='".(int)$order_product_id."'";
		$this->db->query($sql);
	}
	
	public function addOrdeStatus($order_id,$data) {
		$sql="update " . DB_PREFIX . "vendor_order_product set
		order_status_id='".(int)$data['order_status_id']."',
		date_modified=now() where order_id='".(int)$order_id."'";
		$this->db->query($sql);
		$order_product_id=0;
		$sql = "SELECT order_id FROM " . DB_PREFIX . "vendor_order_product where order_product_id='".(int)$order_product_id."'";
		$query1 = $this->db->query($sql);
		
		
			$sql2 = "SELECT * FROM " . DB_PREFIX . "vendor_order_product where order_product_id='".(int)$order_product_id."'";
			$query2 = $this->db->query($sql2);
			$vendorinfo= $query2->row;
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_vendorhistory SET order_id = '" . (int)$data['order_id'] . "', order_status_id = '".(int)$data['order_status_id']."', vendor_id = '".(int)$vendorinfo['vendor_id']."', order_product_id = '".(int)$vendorinfo['order_product_id']."', date_added = NOW()");
			
		$this->load->model('vendor/mail');
		$this->load->model('vendor/vendor');
		$this->load->model('vendor/report');
		$sellertype = 'seller_order_status_update_email';
		
		$mailinfo = $this->model_vendor_report->getMailInfo($sellertype);
		
		$sellerorder_info = $this->model_vendor_report->getSellerOrder($order_id);
		$seller_info = $this->model_vendor_vendor->getVendor($sellerorder_info['vendor_id']);
		
		/*Status Enabled*/
		if(isset($mailinfo['status'])){
			$find = array(
				'{emails}',										
			);
			
			if(isset($seller_info['email'])) {
				$emails = $seller_info['email'];
			} else {
				$emails='';
			}
			
			$replace = array(
				'email' 	=> $emails,
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
	
	public function getSellerOrder($order_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product where order_id='".(int)$order_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getOrderProduct($order_id){
		$sql="SELECT * FROM " . DB_PREFIX . "vendor_order_product where order_id='".(int)$order_id."' AND vendor_id<>0";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public  function getMailInfo($sellertype){
		$query=$this->db->query("select * from " . DB_PREFIX . "vendor_mail vm LEFT JOIN " . DB_PREFIX . "vendor_mail_language vml on(vm.mail  =vml.mail_id) where vm.sellertype='" .$sellertype."' and vml.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		return $query->row;
	}
	
 	public function deleteReport($order_product_id){		
		$sql="delete  from " . DB_PREFIX . "vendor_order_product where order_product_id='".(int)$order_product_id."'";
		$query=$this->db->query($sql);
 	} 
	
	public function getOrder($order_id){
		$sql="select * from `" . DB_PREFIX . "order` where order_id='".(int)$order_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getOrderStatus($order_status_id){
		$sql="select * from " . DB_PREFIX . "order_status where order_status_id='".(int)$order_status_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	
	public function getOrderTotals($order_id){
		$sql="select * from " . DB_PREFIX . "order_total where order_id='".(int)$order_id."'";
		$query = $this->db->query($sql);
		return $query->rows;
	}
		
 	public function getReports($data){		

		$sql = "SELECT *,vop. order_status_id,CONCAT(o.firstname, ' ', o.lastname) AS cname, vop.date_added as date_added FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) ";
		
		
		if (!empty($data['filter_order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "vop.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
			$sql .= " WHERE vop.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE vop.order_status_id > '0'";
		}
		
		
		if (!empty($data['filter_vendor'])){
		 	$sql .=" and vop.vendor_id='".$this->db->escape($data['filter_vendor'])."'";
		}
		
		if (!empty($data['filter_customer'])){
		 	$sql .=" and o.customer_id='".$this->db->escape($data['filter_customer'])."'";
		}
		
		
		if (!empty($data['filter_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "vop.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " AND (" . implode(" OR ", $implode) . ")";
			}
			
		} elseif (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND vop.order_status_id = '" . (int)$data['filter_status'] . "'";
		} else {
			$sql .= " AND vop.order_status_id > '0'";
		}
		
		
		if (isset($data['filter_order_id'])){
		 	$sql .=" and o.order_id like '".$this->db->escape($data['filter_order_id'])."%'";
		}
		
		if (isset($data['filter_date'])){
		 	$sql .=" and vop.date_added like '".$this->db->escape($data['filter_date'])."%'";
		}
		
		$sort_data = array(
			'vop.order_product_id',
			'vop.name',
			'vop.date_added',
			'o.order_id',			
			'vop.total',			
			'o.firstname'			
		);
		
		$sql .= " AND vop.vendor_id!=0";
		$sql .= " AND o.order_status_id!=0 ";
		$sql .= " GROUP by vop.order_id";
	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY o.order_id";
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
 	public function getTotalReport($data) {
		
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id)";
		
		if (!empty($data['filter_order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "vop.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
			$sql .= " WHERE vop.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE vop.order_status_id > '0'";
		}
		
		
		if (!empty($data['filter_vendor'])){
		 	$sql .=" and vop.vendor_id='".$this->db->escape($data['filter_vendor'])."'";
		}
		
		if (!empty($data['filter_customer'])){
		 	$sql .=" and o.customer_id='".$this->db->escape($data['filter_customer'])."'";
		}
		
		
		if (!empty($data['filter_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "vop.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " AND (" . implode(" OR ", $implode) . ")";
			}
			
		} elseif (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND vop.order_status_id = '" . (int)$data['filter_status'] . "'";
		} else {
			$sql .= " AND vop.order_status_id > '0'";
		}
		
		
		if (isset($data['filter_order_id'])){
		 	$sql .=" and o.order_id like '".$this->db->escape($data['filter_order_id'])."%'";
		}
		
		if (isset($data['filter_date'])){
		 	$sql .=" and vop.date_added like '".$this->db->escape($data['filter_date'])."%'";
		}
		
	
		$sql .= " AND vop.vendor_id!=0";
		$sql .= " AND o.order_status_id!=0 ";
		$sql .= " GROUP by vop.order_id";
		
		
		$query = $this->db->query($sql);
		
		return $query->num_rows;
		
	}
	
	public function getSellerProduct($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_order_product WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}
	
	public function deleteOrderReport($vendor_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id = '" . (int)$vendor_id . "'");
	}
	
	
	public function getorderproductid($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->row;
	}
	
	public function getOrderHistories($order_id) {
		$query = $this->db->query("SELECT date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added");

		return $query->rows;
	}
	
	public function getVendorOrderHistories($order_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT *, os.name AS status FROM " . DB_PREFIX . "order_vendorhistory ovh LEFT JOIN " . DB_PREFIX . "order_status os ON ovh.order_status_id = os.order_status_id WHERE ovh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ovh.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}
	
	public function getTotalOrderHistories($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_vendorhistory WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}
	
	
	public function getOrderProductsName($order_id,$vendor_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_order_product WHERE order_id = '" . (int)$order_id . "'AND vendor_id = '" . (int)$vendor_id . "'");
		return $query->row;
	}
	
	public function getCustomerOrderStatus($order_status_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "'");
		return $query->row;
	}
	
	public function getOrderProductsNames($order_id,$vendor_id) {
		$query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "vendor_order_product WHERE order_id = '" . (int)$order_id . "' AND vendor_id<>0");
		return $query->rows;
	}
	
	public function getVendorName($order_product_id) {
		$query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN ".DB_PREFIX."vendor v on vop.vendor_id = v.vendor_id WHERE vop.order_product_id = '" . (int)$order_product_id . "'");
		return $query->row;
	}
	
	public function getOrderProductstatus($order_product_id) {		
		$query = $this->db->query("SELECT *, os.name AS status FROM " . DB_PREFIX . "order_vendorhistory ov LEFT JOIN ". DB_PREFIX ."order_status os on(ov.order_status_id = os.order_status_id) WHERE ov.order_product_id = '" . (int)$order_product_id . "' order by order_vendorhistory_id DESC limit 0,1");
		return $query->row;
	}
		
	public function getVendorStoreName($order_product_id) {
		$query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN ".DB_PREFIX."vendor_description vd on vop.vendor_id = vd.vendor_id WHERE vop.order_product_id = '" . (int)$order_product_id . "' AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		return $query->row;
	}
	
	public function getReport($order_id,$vendor_id){		
		$sql = "SELECT *,vop. order_status_id,CONCAT(o.firstname, ' ', o.lastname) AS cname, vop.date_added as date_added FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) WHERE  vop.vendor_id = '" . (int)$vendor_id . "' AND o.order_id = '" . (int)$order_id . "'  AND  o.order_status_id!=0 ";		
	
		$query = $this->db->query($sql);
		
		return $query->row;	
 	}
	
	/* 13 04 2020 */
	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}	
	/* 13 04 2020 */
}
?>