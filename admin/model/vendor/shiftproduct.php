<?php
class ModelVendorShiftProduct extends Model {
	
	public function getManufacturers($data = array()) {
			$sql = "SELECT * FROM " . DB_PREFIX . "manufacturer where manufacturer_id<>0";

		if (!empty($data['filter_name'])) {
			$sql .= " AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY manufacturer_id";

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
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
	
	
	public function getManufacturer($manufacturer_id) {
		
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row;
	}

	public function shiftproduct($data)
	{
		
		if(isset($data['product_manufacture']))
		{
			foreach($data['product_manufacture'] as $manufacturer_id)
			{
				
					
					$query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product` WHERE manufacturer_id = '" . (int)$manufacturer_id. "'");
					 
					if(isset($query->row['product_id']))
					{
						 
						foreach($query->rows as $row)
						{
							$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_to_product WHERE product_id = '" . (int)$row['product_id'] . "'");
							$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_to_product SET product_id = '" . (int)$row['product_id']. "', vendor_id = '" .(int)$data['vendor_id'] . "'");
							$this->db->query("update " . DB_PREFIX . "product set status='".(int)$data['status']."' WHERE product_id = '" . (int)$row['product_id']. "'");
						}
					}
				
			}
		}
		
	
	}
	
}
?>