<?php
class ModelExtensionFeedIndexing extends Model {

	public function getProducts() {

		$this->prefix = (version_compare(VERSION, '3.0', '>=')) ? 'feed_' : '';
		$actions = $this->config->get($this->prefix . 'indexing_cron_action');
		$now = time();
		switch ($this->config->get($this->prefix . 'indexing_frequency')) {
			case '1':
				$date = date('Y-m-d H:i:s', strtotime('-1 day', $now));
				break;

			case '2':
				$date = date('Y-m-d H:i:s', strtotime('-1 week', $now));
				break;

			case '3':
				$date = date('Y-m-d H:i:s', strtotime('-1 month', $now));
				break;
			
			default:
				$date = date('Y-m-d H:i:s', strtotime('-1 hour', $now));
				break;
		}

		$sql = "SELECT p.product_id";

		$sql .= " FROM " . DB_PREFIX . "product p";

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";

		$sql .= " WHERE p.status = '1' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (in_array('add_product', $actions) || in_array('edit_product', $actions)) {
			$sql .= " AND (";
			if (in_array('add_product', $actions)) {
				$sql .= "(p.date_added <= NOW() AND p.date_added >= '".$date."')";
			}
			if (in_array('add_product', $actions) AND in_array('edit_product', $actions)) {
				$sql .= " || ";
			}
			if (in_array('edit_product', $actions)) {
				$sql .= "(p.date_modified <= NOW() AND p.date_modified >= '".$date."')";
			}
			$sql .= ")";
		}
		
		$sql .= " GROUP BY p.product_id";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getCategories() {

		$this->prefix = (version_compare(VERSION, '3.0', '>=')) ? 'feed_' : '';
		$actions = $this->config->get($this->prefix . 'indexing_cron_action');
		$now = time();
		switch ($this->config->get($this->prefix . 'indexing_frequency')) {
			case '1':
				$date = date('Y-m-d H:i:s', strtotime('-1 day', $now));
				break;

			case '2':
				$date = date('Y-m-d H:i:s', strtotime('-1 week', $now));
				break;

			case '3':
				$date = date('Y-m-d H:i:s', strtotime('-1 month', $now));
				break;
			
			default:
				$date = date('Y-m-d H:i:s', strtotime('-1 hour', $now));
				break;
		}

		$sql = "SELECT c.category_id";

		$sql .= " FROM " . DB_PREFIX . "category c";

		$sql .= " LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id)";

		$sql .= " WHERE c.status = '1' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (in_array('add_category', $actions) || in_array('edit_category', $actions)) {
			$sql .= " AND (";
			if (in_array('add_category', $actions)) {
				$sql .= "(c.date_added <= NOW() AND c.date_added >= '".$date."')";
			}
			if (in_array('add_category', $actions) AND in_array('edit_category', $actions)) {
				$sql .= " || ";
			}
			if (in_array('edit_category', $actions)) {
				$sql .= "(c.date_modified <= NOW() AND c.date_modified >= '".$date."')";
			}
			$sql .= ")";
		}
		
		$sql .= " GROUP BY c.category_id";

		$query = $this->db->query($sql);

		return $query->rows;
	}

}