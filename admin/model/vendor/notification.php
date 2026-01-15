<?php
class ModelVendorNotification extends Model {
	
	public function addNotification($data) {
		$sql="INSERT INTO " . DB_PREFIX . "vendor_notification set type='".$data['type']."',date='".$this->db->escape($data['date'])."',date_added=now()";
		$this->db->query($sql);
		
		$notification_id = $this->db->getLastId();
				
		if (isset($data['notification_message'])) {
			foreach ($data['notification_message'] as $language_id => $value) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_notification_message SET notification_id='".(int)$notification_id."',language_id = '" . (int)$language_id ."',message='".$this->db->escape($value['message'])."'"); 
			}
		}
		if (isset($data['notification_customer'])) {
			foreach ($data['notification_customer'] as $customer_id) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_notification_customer SET notification_id='".(int)$notification_id."',customer_id = '" . (int)$customer_id ."'"); 
			}
		}
		if (isset($data['notification_seller'])) {
			foreach ($data['notification_seller'] as $vendor_id) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_notification_seller SET notification_id='".(int)$notification_id."',vendor_id = '" . (int)$vendor_id ."'"); 
			}
		}
		return $notification_id;
	}
	
	public function editNotification($notification_id, $data) {
		$sql="update " . DB_PREFIX . "vendor_notification set type='".$data['type']."',date='".$this->db->escape($data['date'])."',date_modified=now() where notification_id = '" . (int)$notification_id . "'";
	 	$this->db->query($sql);
				
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_notification_message WHERE notification_id = '" . (int)$notification_id . "'");
		
		if (isset($data['notification_message'])) {
			foreach ($data['notification_message'] as $language_id => $value) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_notification_message SET notification_id='".(int)$notification_id."',language_id = '" . (int)$language_id ."',message='".$this->db->escape($value['message'])."'"); 
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_notification_customer WHERE notification_id = '" . (int)$notification_id . "'");
		if (isset($data['notification_customer'])) {
			foreach ($data['notification_customer'] as $customer_id) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_notification_customer SET notification_id='".(int)$notification_id."',customer_id = '" . (int)$customer_id ."'"); 
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_notification_seller WHERE notification_id = '" . (int)$notification_id . "'");
		if (isset($data['notification_seller'])) {
			foreach ($data['notification_seller'] as $vendor_id) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_notification_seller SET notification_id='".(int)$notification_id."',vendor_id = '" . (int)$vendor_id ."'"); 
			}
		}
		
	}
	
	public function deleteNotification($notification_id) {
		$sql="delete  from " . DB_PREFIX . "vendor_notification where notification_id='".(int)$notification_id."'";
		$query=$this->db->query($sql);
		$sql="delete  from " . DB_PREFIX . "vendor_notification_message where notification_id='".(int)$notification_id."'";
		$query=$this->db->query($sql);
		$sql="delete  from " . DB_PREFIX . "vendor_notification_customer where notification_id='".(int)$notification_id."'";
		$query=$this->db->query($sql);
		$sql="delete  from " . DB_PREFIX . "vendor_notification_seller where notification_id='".(int)$notification_id."'";
		$query=$this->db->query($sql);
	}

	public function getNotification($notification_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_notification where notification_id='".(int)$notification_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getNotificationMessage($notification_id) {
		$notification_message = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX ."vendor_notification_message WHERE notification_id = '" . (int)$notification_id . "'");
		foreach ($query->rows as $result) {
			$notification_message[$result['language_id']] = array(
				'message'=> $result['message'],
			);
	 	}
		return $notification_message;
	}
	
	public function getNotificationCustomer($notification_id) {
		$notification_customer = array();
		$cus_query = $this->db->query("SELECT * FROM " . DB_PREFIX ."vendor_notification_customer WHERE notification_id = '" . (int)$notification_id . "'");
		foreach ($cus_query->rows as $result) {
			$notification_customer[] = $result['customer_id'];
	 	}
		return $notification_customer;
	}
	
	public function getNotificationSeller($notification_id) {
		$notification_seller = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX ."vendor_notification_seller WHERE notification_id = '" . (int)$notification_id . "'");
		foreach ($query->rows as $result) {
			$notification_seller[] = $result['vendor_id'];
	 	}
		return $notification_seller;
	}
	
	public function getNotifications($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_notification vn LEFT JOIN " . DB_PREFIX . "vendor_notification_message vnm ON (vn.notification_id = vnm.notification_id) WHERE vnm.language_id = '" . (int)$this->config->get('config_language_id') . "' and vn.notification_id<>0";
				
		$sort_data = array(
			'notification_id',
			'type'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY type";
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
	
	public function getTotalNotifications($data) {
		$sql ="SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_notification where notification_id<>0";
				
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

}
