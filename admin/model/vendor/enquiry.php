<?php
class ModelVendorEnquiry extends Model {
	public function deleteEnquiry($inquiry_id) {
		$sql="delete  from " . DB_PREFIX . "vendor_inquiry where inquiry_id='" .(int)$inquiry_id."'";
		$query=$this->db->query($sql);
	}
	
	public function getEnquiry($inquiry_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_inquiry where inquiry_id='".(int)$inquiry_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	/* 11 02 2020 sname */
	public function getVendor($vendor_id) {
	
		$sql = "SELECT *, CONCAT(firstname, ' ', lastname) AS sname FROM " . DB_PREFIX . "vendor where vendor_id='".(int)$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getCustomer($customer_id) {
		$sql = "SELECT *, CONCAT(firstname, ' ', lastname) AS customername FROM " . DB_PREFIX . "customer where customer_id='".(int)$customer_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	/* 11 02 2020 customername */

	public function getProduct($product_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "product_description where product_id='". (int)$product_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}


	public function getEnquires($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_inquiry where inquiry_id<>0";
		
		
		if (!empty($data['filter_vendor'])){
		 	$sql .=" and vendor_id='".$this->db->escape($data['filter_vendor'])."'";
		}
		
		if (!empty($data['filter_customer'])){
		 	$sql .=" and customer_id like '".$this->db->escape($data['filter_customer'])."%'";
		}
		
		if (!empty($data['filter_productname'])){
		 	$sql .=" and product_id like '".$this->db->escape($data['filter_productname'])."%'";
		}
		
		if (!empty($data['filter_enqname'])){
		 	$sql .=" and name like '".$this->db->escape($data['filter_enqname'])."%'";
		}
		
		$sort_data = array(
			'status',
			'date_added',
			'inquiry_id'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY date_added";
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

	public function getTotalEnquiry($data) {
        $sql ="SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_inquiry where inquiry_id<>0";

		
		if (!empty($data['filter_vendor'])){
		 	$sql .=" and vendor_id like '".$this->db->escape($data['filter_vendor'])."%'";
		}
		
		if (!empty($data['filter_customer'])){
		 	$sql .=" and customer_id like '".$this->db->escape($data['filter_customer'])."%'";
		}
		
		if (!empty($data['filter_productname'])){
		 	$sql .=" and product_id like '".$this->db->escape($data['filter_productname'])."%'";
		}
		
		if (!empty($data['filter_name'])){
		 	$sql .=" and name like '".$this->db->escape($data['filter_name'])."%'";
		}
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function addMessage($data) {
		$sql="INSERT INTO " . DB_PREFIX . "vendor_inquiry_message set inquiry_id='".(int)$data['inquiry_id']."',product_id='".(int)$data['product_id']."',customer_id='".(int)$data['customer_id']."',vendor_id='".(int)$data['vendor_id']."',message='".$this->db->escape($data['message'])."',date_added=now()";
		$this->db->query($sql);
	}

	public function getMessages($inquiry_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_inquiry_message where inquiry_id='".(int)$inquiry_id."'";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getTotalMessages($inquiry_id) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_inquiry_message where inquiry_id='".(int)$inquiry_id."'";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
}
