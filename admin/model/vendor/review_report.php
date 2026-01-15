<?php
class ModelVendorReviewreport extends Model {
 	public function deleteReviewReport($review_id){		
		$sql="delete  from " . DB_PREFIX . "vendor_order_product where review_id='".(int)$review_id."'";
		$query=$this->db->query($sql);
 	}
	
 	public function getReviewReports($data=array()){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_review WHERE review_id<>0";
		

		if (isset($data['filter_seller'])){
		 	$sql .=" and vendor_id like '".$this->db->escape($data['filter_seller'])."%'";
		}

		if (isset($data['filter_customer'])){
		 	$sql .=" and customer_id like '".$this->db->escape($data['filter_customer'])."%'";
		}

		$sort_data = array(
			'review_id'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY review_id";
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
 	public function getTotalReviewReport($data=array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_review WHERE review_id<>0";
		
		if (isset($data['filter_seller'])){
		 	$sql .=" and vendor_id like '".$this->db->escape($data['filter_seller'])."%'";
		}

		if (isset($data['filter_customer'])){
		 	$sql .=" and customer_id like '".$this->db->escape($data['filter_customer'])."%'";
		}

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getField($review_id,$vendor_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_review_field_submit vrfs LEFT JOIN " . DB_PREFIX . "vendor_review_field_description vrfd ON (vrfs.rf_id = vrfd.rf_id) WHERE vrfs.vendor_id='".(int)$vendor_id."' and vrfd.language_id ='". (int)$this->config->get('config_language_id')."' and vrfs.review_id='".(int)$review_id."'";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getFieldById($review_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_review_field_submit WHERE review_id='".(int)$review_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getReviewfeild($rf_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_review_field_description WHERE rf_id='".(int)$rf_id."' AND language_id ='". (int)$this->config->get('config_language_id')."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
}
?>