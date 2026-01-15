<?php
class ModelVendorShipping extends Model {
	public function addShipping($data) {
		$sql="INSERT INTO " . DB_PREFIX . "shipping set
		vendor_id='".(int)$data['vendor_id']."',
		country_id='".(int)$data['country_id']."',
		zip_from='".$this->db->escape($data['zip_from'])."',
		
		weight_from='".$this->db->escape($data['weight_from'])."',
		weight_to='".$this->db->escape($data['weight_to'])."',
		price='".$this->db->escape($data['price'])."',
		date_added=now()";
		$this->db->query($sql);
		
	}

	public function deleteShipping($shipping_id) {
		$sql="delete  from " . DB_PREFIX . "shipping where shipping_id='".(int)$shipping_id."'";
		$query=$this->db->query($sql);
	}

	public function getShipping($vendor_id) {
		$sql="SELECT * FROM " . DB_PREFIX . "shipping where vendor_id='".(int)$vendor_id."'";
		$query=$this->db->query($sql);
		return $query->rows;
	}
	
	public function getShippings($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "shipping where shipping_id<>0";
		
		if (!empty($data['filter_vendor'])){
		 	$sql .=" and vendor_id like '".$this->db->escape($data['filter_vendor'])."%'";
		}

		if (!empty($data['filter_country'])){
		 	$sql .=" and country_id like '".$this->db->escape($data['filter_country'])."%'";
		}

		if (!empty($data['filter_zipfrom'])){
		 	$sql .=" and zip_from like '".$this->db->escape($data['filter_zipfrom'])."%'";
		}

	

		if (!empty($data['filter_weightfrom'])){
		 	$sql .=" and weight_from >= '".$this->db->escape($data['filter_weightfrom'])."%'";
		}

		if (!empty($data['filter_weightto'])){
		 	$sql .=" and weight_to <= '".$this->db->escape($data['filter_weightto'])."%'";
		}

		if (!empty($data['filter_price'])){
		 	$sql .=" and price like '".$this->db->escape($data['filter_price'])."%'";
		}

		$sort_data = array(
			'shipping_id'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY shipping_id";
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

	public function getTotalShippping($data) {
        $sql ="SELECT COUNT(*) AS total FROM " . DB_PREFIX . "shipping where shipping_id<>0 ";

        if (!empty($data['filter_vendor'])){
		 	$sql .=" and vendor_id like '".$this->db->escape($data['filter_vendor'])."%'";
		}


		if (!empty($data['filter_country'])){
		 	$sql .=" and country_id like '".$this->db->escape($data['filter_country'])."%'";
		}

		if (!empty($data['filter_zipfrom'])){
		 	$sql .=" and zip_from like '".$this->db->escape($data['filter_zipfrom'])."%'";
		}

		

		if (!empty($data['filter_weightfrom'])){
		 	$sql .=" and weight_from >= '".$this->db->escape($data['filter_weightfrom'])."%'";
		}

		if (!empty($data['filter_weightto'])){
		 	$sql .=" and weight_to <= '".$this->db->escape($data['filter_weightto'])."%'";
		}

		if (!empty($data['filter_price'])){
		 	$sql .=" and price like '".$this->db->escape($data['filter_price'])."%'";
		}
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getVendorStoreDescription($data) {
        
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor vs LEFT JOIN " . DB_PREFIX . "vendor_description vsd ON (vs.vendor_id = vsd.vendor_id) WHERE vsd.language_id = '" . (int)$this->config->get('config_language_id') . "' and vs.vendor_id<>0";
		$query = $this->db->query($sql);
		
		return $query->rows;
	}

	public function getVendorDescription($vendor_id) {        
		$sql ="SELECT * FROM " . DB_PREFIX . "vendor_description where vendor_id='".(int)$vendor_id."' AND language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function addImport($vendor_id, $data){
			$this->db->query("DELETE FROM " . DB_PREFIX . "shipping WHERE vendor_id='".(int)$vendor_id."' and country_id='".(int)$data['country_id']."' and
		zip_from='".$this->db->escape($data['zip_from'])."' and 
		weight_from='".$this->db->escape($data['weight_from'])."' and 
		weight_to='".$this->db->escape($data['weight_to'])."'");

		$sql = "INSERT INTO " . DB_PREFIX . "shipping set
		vendor_id='".(int) $data['vendor_id']."',
		country_id='".(int)$data['country_id']."',
		zip_from='".$this->db->escape($data['zip_from'])."',
		weight_from='".$this->db->escape($data['weight_from'])."',
		weight_to='".$this->db->escape($data['weight_to'])."',
		price='".$this->db->escape($data['price'])."'";
		$this->db->query($sql);
		
	}

	public function getCountrybyname($country) {
		$query = $this->db->query("SELECT country_id FROM " . DB_PREFIX . "country WHERE name = '" . $country . "'");

		return $query->row['country_id'];
	}

	public function getvendorbyname($vendor) {
		$query = $this->db->query("SELECT vendor_id FROM " . DB_PREFIX . "vendor_description WHERE name = '" . $vendor . "'");

		return $query->row['vendor_id'];
	}
}
