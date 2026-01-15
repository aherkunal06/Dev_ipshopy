<?php
class ModelAccountReturn extends Model {
	public function addReturn($data) {
	    //update the following query add product id on 21-05-205------
		$this->db->query("INSERT INTO `" . DB_PREFIX . "return` SET order_id = '" . (int)$data['order_id'] . "', customer_id = '" . (int)$this->customer->getId() . "', product_id = '" . (int)$data['product_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', product = '" . $this->db->escape($data['product']) . "', model = '" . $this->db->escape($data['model']) . "', quantity = '" . (int)$data['quantity'] . "', opened = '" . (int)$data['opened'] . "', return_reason_id = '" . (int)$data['return_reason_id'] . "', return_status_id = '" . (int)$this->config->get('config_return_status_id') . "', comment = '" . $this->db->escape($data['comment']) . "', date_ordered = '" . $this->db->escape($data['date_ordered']) . "', date_added = NOW(), date_modified = NOW()");

		return $this->db->getLastId();
		// added changes for return image on 20-05-2025 ----------------------
		// If an image was uploaded, store it
		if (!empty($data['return_image'])) {
			$this->addReturnImage($return_id, $data['return_image']);
		} 

			return $return_id;
        //--------------------------------------------------------------------------------
	}

	public function getReturn($return_id) {
		$query = $this->db->query("SELECT r.return_id, r.order_id, r.firstname, r.lastname, r.email, r.telephone, r.product, r.model, r.quantity, r.opened, (SELECT rr.name FROM " . DB_PREFIX . "return_reason rr WHERE rr.return_reason_id = r.return_reason_id AND rr.language_id = '" . (int)$this->config->get('config_language_id') . "') AS reason, (SELECT ra.name FROM " . DB_PREFIX . "return_action ra WHERE ra.return_action_id = r.return_action_id AND ra.language_id = '" . (int)$this->config->get('config_language_id') . "') AS action, (SELECT rs.name FROM " . DB_PREFIX . "return_status rs WHERE rs.return_status_id = r.return_status_id AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status, r.comment, r.date_ordered, r.date_added, r.date_modified FROM `" . DB_PREFIX . "return` r WHERE r.return_id = '" . (int)$return_id . "' AND r.customer_id = '" . $this->customer->getId() . "'");
        
        $return_info = $query->row;
        $data['return_reason'] = $return_info['reason'];
        
		return $query->row;
	}

	public function getReturns($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->query("SELECT r.return_id, r.order_id, r.firstname, r.lastname, rs.name as status, r.date_added, r.product, r.model, r.product_id FROM " . DB_PREFIX . "return r LEFT JOIN " . DB_PREFIX . "return_status rs ON (r.return_status_id = rs.return_status_id) WHERE r.customer_id = '" . (int)$this->customer->getId() . "' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.return_id DESC LIMIT " . (int)$start . ",".(int)$limit);
		return $query->rows;
	}

	public function getTotalReturns() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "return`WHERE customer_id = '" . $this->customer->getId() . "'");

		return $query->row['total'];
	}

	public function getReturnHistories($return_id) {
		$query = $this->db->query("SELECT rh.date_added, rs.name AS status, rh.comment FROM " . DB_PREFIX . "return_history rh LEFT JOIN " . DB_PREFIX . "return_status rs ON rh.return_status_id = rs.return_status_id WHERE rh.return_id = '" . (int)$return_id . "' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY rh.date_added ASC");

		return $query->rows;
	}
	
	// added new function for store the return images on 20-05-2025 -----
	public function addReturnImage($return_id, $image_path) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "return_image` SET return_id = '" . (int)$return_id . "', image = '" . $this->db->escape($image_path) . "'");
	}
	
	
	public function updateReturnOrderStatus($return_id, $order_status_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "return` 
						 SET oc_order_status_id = '" . (int)$order_status_id . "' 
						 WHERE return_id = '" . (int)$return_id . "'");
	}
	
	//----------------------------------------------------------------------------------
	
	public function getReturnImages($return_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "return_image WHERE return_id = '" . (int)$return_id . "'");
		
		return $query->rows;
	}
	
}
