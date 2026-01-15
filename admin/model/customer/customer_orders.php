<?php
class ModelCustomerCustomerOrders extends Model {
    
    public function getCustomerOrders($data = array()) {
		// Ensure customer_id is an integer

        $sql = "SELECT 
                    o.order_id, 
                    CONCAT(o.firstname, ' ', o.lastname) AS customer, 
                    SUM(op.quantity) AS quantity, 
                    o.total AS total, 
                    os.name AS status, 
                    o.date_added AS 'date added', 
                    o.date_modified AS 'date modified' 
                FROM oc_order o 
                LEFT JOIN oc_order_product op ON o.order_id = op.order_id 
                LEFT JOIN oc_order_status os ON o.order_status_id = os.order_status_id 
                WHERE os.language_id = 1
                GROUP BY o.order_id 
                ORDER BY o.order_id DESC";


		if (isset($data['start']) && isset($data['limit']) && $data['start'] >= 0 && $data['limit'] > 0) {
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getCustomers($data = array()) {
        $sql = "SELECT
                     DISTINCT(oc.customer_id) AS customer_id, 
                     CONCAT(oc.firstname, ' ', oc.lastname) AS customer, 
                     oc.email, 
                     oc.telephone,
                     ocg.name AS customer_group,
                     oc.date_added AS date_added
                FROM oc_customer oc 
                LEFT JOIN oc_customer_group_description ocg 
                    ON oc.customer_group_id = ocg.customer_group_id
                WHERE ocg.language_id = '" . (int)$this->config->get('config_language_id') . "'";
    
        $implode = array();
    
        if (!empty($data['filter_customer_id'])) {
            $implode[] = "oc.customer_id = '" . (int)$data['filter_customer_id'] . "'";
        }
    
        if (!empty($data['filter_name'])) {
            $implode[] = "CONCAT(oc.firstname, ' ', oc.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }
    
        if (!empty($data['filter_email'])) {
            $implode[] = "oc.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
        }
    
        if (!empty($data['filter_date_added'])) {
            $implode[] = "DATE(oc.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }
    
        if ($implode) {
            $sql .= " AND " . implode(" AND ", $implode);
        }
    
        $sql .= " ORDER BY oc.date_added DESC";
    
        if (isset($data['start']) && isset($data['limit']) && $data['start'] >= 0 && $data['limit'] > 0) {
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
    
        $query = $this->db->query($sql);
        return $query->rows;
    }

}
?>