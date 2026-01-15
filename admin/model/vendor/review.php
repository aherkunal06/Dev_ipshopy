<?php
class ModelVendorReview extends Model {
	public function addReview($data) {
		//print_r($data);die();
		$sql="INSERT INTO " . DB_PREFIX . "vendor_review set
		text='".$this->db->escape($data['text'])."',
		customer_id='".(int)$data['customer_id']."',
		vendor_id='".(int)$data['vendor_id']."',
		status='".(int)$data['status']."',
		date_added=now()";
		$this->db->query($sql);
		$review_id = $this->db->getLastId();
		
		if (isset($data['reviewfield'])) {
			foreach ($data['reviewfield'] as $key => $value) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_review_field_submit SET 
				review_id ='" . (int)$review_id . "',
				rf_id ='" . (int)$key . "',
				vendor_id='".(int)$data['vendor_id']."',
				value='".$this->db->escape($value)."'
				"); 
			}
		}
			/* update 02 11 2020 */
			$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_to_review SET review_id = '" . (int)$review_id . "', vendor_id = '".(int)$data['vendor_id']."'");
				/* update 02 11 2020 */
	}

	public function editReview($review_id, $data) {
		$sql="update " . DB_PREFIX . "vendor_review set 
		text='".$this->db->escape($data['text'])."',
		customer_id='".(int)$data['customer_id']."',
		vendor_id='".(int)$data['vendor_id']."',
		status='".(int)$data['status']."',
		date_modified=now()
		where review_id='".(int)$review_id."'";
	 	
		$this->db->query($sql);
		$this->db->query("delete from " . DB_PREFIX . "vendor_review_field_submit where  review_id ='" . (int)$review_id."'");
		if (isset($data['reviewfield'])) {
			foreach ($data['reviewfield'] as $key => $value) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_review_field_submit SET 
				review_id ='" . (int)$review_id . "',
				rf_id ='" . (int)$key . "',
				vendor_id='".(int)$data['vendor_id']."',
				value='".$this->db->escape($value)."'
				"); 
			}
		}
		
			/* update 02 11 2020 */
		$this->db->query("delete from " . DB_PREFIX . "vendor_to_review where  review_id ='" . (int)$review_id."'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_to_review SET review_id = '" . (int)$review_id . "', vendor_id = '".(int)$data['vendor_id']."'");
			/* update 02 11 2020 */
		
	}
	
	public function deleteReview($review_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_review WHERE review_id = '" . (int)$review_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_to_review WHERE review_id = '" . (int)$review_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_review_field_submit WHERE review_id = '" . (int)$review_id . "'");
	}
	
	public function getReview($review_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_review where review_id='".(int)$review_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getVendor($vendor_id) {
		/* 11 02 2020 sname */
		$sql = "SELECT *, CONCAT(firstname, ' ', lastname) AS sname FROM " . DB_PREFIX . "vendor where vendor_id='".(int)$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getCustomer($customer_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "customer where customer_id='".(int)$customer_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getReviews($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_review where review_id<>0";
		
		if (!empty($data['filter_vendor'])){
		 	$sql .=" and vendor_id='".$this->db->escape($data['filter_vendor'])."'";
		}
		
		if (!empty($data['filter_customer'])){
		 	$sql .=" and customer_id='".$this->db->escape($data['filter_customer'])."'";
		}
		/* 12 02 2020 */
		if (isset($data['filter_status'])){
		 	$sql .=" and status like '".$this->db->escape($data['filter_status'])."%'";
		}
		/* 12 02 2020 */
		if (!empty($data['filter_date'])){
		 	$sql .=" and date_added ='".$this->db->escape($data['filter_date'])."'";
		}
		
		$sort_data = array(
			'status',
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

	public function getTotalReview($data) {
        $sql ="SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_review where review_id<>0";

		if (!empty($data['filter_vendor'])){
		 	$sql .=" and vendor_id='".$this->db->escape($data['filter_vendor'])."'";
		}
		
		if (!empty($data['filter_customer'])){
		 	$sql .=" and customer_id='".$this->db->escape($data['filter_customer'])."'";
		}
		/* 12 02 2020 */
		if (isset($data['filter_status'])){
		 	$sql .=" and status like '".$this->db->escape($data['filter_status'])."%'";
		}
		/* 12 02 2020 */
		if (!empty($data['filter_date'])){
		 	$sql .=" and date_added ='".$this->db->escape($data['filter_date'])."'";
		}
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getFieldSubmits($review_id) {
		$form_field_data = array();
		
		$form_field_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_review_field_submit where review_id='".(int)$review_id."'");
		
		foreach ($form_field_query->rows as $key => $form_field) {
			
			$form_field_data[] = array(
				'rf_id' 		=> $form_field['rf_id'],
				'review_id' 		=> $form_field['review_id'],
				'value' 		=> $form_field['value']
			);
			
		}
		return $form_field_data;
	}

	public function getReviewFielddescription($rf_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_review_field_description WHERE rf_id = '" . (int)$rf_id . "'");

		return $query->row;
	}
	/* 18 02 2020 */
	public function getField($review_id, $vendor_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_review_field_submit vrfs LEFT JOIN " . DB_PREFIX . "vendor_review_field_description vrfd ON (vrfs.rf_id = vrfd.rf_id) LEFT JOIN ". DB_PREFIX ."vendor_review vr ON (vrfs.review_id = vr.review_id) WHERE vrfs.vendor_id = '".(int)$vendor_id."' AND vrfd.language_id = '" . (int)$this->config->get('config_language_id') . "' and vrfs.review_id='".(int)$review_id."'";
		
		$query=$this->db->query($sql);
		return $query->rows;
	}
	/* 18 02 2020 */	
}
