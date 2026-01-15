<?php
class ModelVendorReviewField extends Model {
	public function addReviewField($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_review_field SET sort_order = '" . (int)$data['sort_order'] . "',status = '" . (int)$data['status'] . "',date_added=now()");

		$rf_id = $this->db->getLastId();

		foreach ($data['vendor_review_field_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_review_field_description SET rf_id = '" . (int)$rf_id . "', language_id = '" . (int)$language_id . "', field_name = '" . $this->db->escape($value['field_name']) . "'");
		}
		return $rf_id;
	}

	public function editReviewField($rf_id, $data) {
		$sql="update " . DB_PREFIX . "vendor_review_field set sort_order = '" . (int)$data['sort_order'] . "',status = '" . $data['status'] . "',date_modified=now() where rf_id='".(int)$rf_id."'";
		$this->db->query($sql);
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_review_field_description WHERE rf_id = '" . (int)$rf_id . "'");

		foreach ($data['vendor_review_field_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_review_field_description SET rf_id = '" . (int)$rf_id . "', language_id = '" . (int)$language_id . "', field_name = '" . $this->db->escape($value['field_name']) . "'");
		}
	}

	public function deleteReviewField($rf_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_review_field WHERE rf_id = '" . (int)$rf_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_review_field_description WHERE rf_id = '" . (int)$rf_id . "'");
	}

	public function getReviewField($rf_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_review_field WHERE rf_id = '" . (int)$rf_id . "'");

		return $query->row;
	}

	public function getReviewFields($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_review_field vrf LEFT JOIN " . DB_PREFIX . "vendor_review_field_description vrfd ON (vrf.rf_id = vrfd.rf_id) WHERE vrfd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if (isset($data['filter_status'])){
		 	$sql .=" and vrf.status like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		if (isset($data['filter_name'])){
		 	$sql .=" and vrfd.field_name like '".$this->db->escape($data['filter_name'])."%'";
		}
		
		$sort_data = array(
			'vrfd.field_name',
			'vrf.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY vrfd.field_name";
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$sql .= " ASC";
		} else {
			$sql .= " DESC";
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

	public function getdeleteReviewFieldStores($rf_id) {
		
	}
	
	public function getVendorReviewFieldDescriptions($rf_id) {
		$attribute_group_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_review_field_description WHERE rf_id = '" . (int)$rf_id . "'");

		foreach ($query->rows as $result) {
			$attribute_group_data[$result['language_id']] = array('field_name' => $result['field_name']);
		}

		return $attribute_group_data;
	}
	
	public function getTotalReviewFields($data) {
		
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_review_field vrf LEFT JOIN " . DB_PREFIX . "vendor_review_field_description vrfd ON (vrf.rf_id = vrfd.rf_id) WHERE vrfd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($data['filter_status'])){
		 	$sql .=" and vrf.status like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		if (isset($data['filter_name'])){
		 	$sql .=" and vrfd.field_name like '".$this->db->escape($data['filter_name'])."%'";
		}
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
}
