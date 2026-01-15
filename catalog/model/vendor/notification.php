<?php
class ModelVendorNotification extends Model {
	
	public function getSellerMessages() {
		$date=date('Y-m-d');
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_notification vn LEFT JOIN " . DB_PREFIX . "vendor_notification_message vnm ON (vn.notification_id = vnm.notification_id) WHERE vnm.language_id = '" . (int)$this->config->get('config_language_id') . "' and vn. date>0";
				
		$query=$this->db->query($sql);
		return $query->rows;
	}
	
	public function getSellerNotification() {
		$date=date('Y-m-d');
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_notification_seller vns LEFT JOIN " . DB_PREFIX . "vendor_notification vn ON (vns.notification_id = vn.notification_id) LEFT JOIN " . DB_PREFIX . "vendor_notification_message vnm ON (vns.notification_id = vnm.notification_id) WHERE vnm.language_id = '" . (int)$this->config->get('config_language_id') . "' AND vn.date>0";
				
		$query=$this->db->query($sql);
		return $query->rows;
	}
	
			
}