<?php
class ModelVendorCommission extends Model {
	public function addCommission($data) {
		$sql="INSERT INTO " . DB_PREFIX . "vendor_commission set
			category_id='".(int)$data['category_id']."',
			fixed='".(int)$data['fixed']."',
			percentage='".(int)$data['percentage']."',
			date_added=now()";
		$this->db->query($sql);
	}

	public function editCommission($commission_id, $data) {
		$sql="update " . DB_PREFIX . "vendor_commission set 
			category_id='".(int)$data['category_id']."',
			fixed='".(int)$data['fixed']."',
			percentage='".(int)$data['percentage']."',
			date_modified=now()
			where commission_id='".(int)$commission_id."'";
		$this->db->query($sql);
	}
	
	public function deleteCommission($commission_id) {
		$sql="delete  from " . DB_PREFIX . "vendor_commission where commission_id='".(int)$commission_id."'";
		$query=$this->db->query($sql);
	}

	public function getCommission($commission_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_commission where commission_id='".(int)$commission_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getCommissions($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_commission where commission_id<>0";
		
		if (!empty($data['filter_categoryname'])){
		 	$sql .=" and category_id='".$this->db->escape($data['filter_categoryname'])."'";
		}
		
		if (isset($data['filter_id'])){
		 	$sql .=" and commission_id like '".$this->db->escape($data['filter_id'])."%'";
		}
		
		$sort_data = array(
			'percentage',
			'commission_id',
			'fixed',
			'category_id'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY commission_id";
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

	public function getTotalCommission($data) {
		$sql ="SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_commission where commission_id<>0";
			if (isset($data['filter_categoryname'])){
		 	$sql .=" and category_id like '".$this->db->escape($data['filter_categoryname'])."%'";
		}
		
		if (isset($data['filter_id'])){
		 	$sql .=" and commission_id like '".$this->db->escape($data['filter_id'])."%'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
}
