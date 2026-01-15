<?php
class ModelVendorPickupScheduledOrders extends Model {
    public function getTotalPickupScheduledOrders($vendor_id, $data = array()) {
    $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id = '" . (int)$vendor_id . "' AND order_status_id IN (23, 27, 28)";
        if (!empty($data['filter_order_id'])) {
            $sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        if (!empty($data['filter_product_name'])) {
            $sql .= " AND name LIKE '%" . $this->db->escape($data['filter_product_name']) . "%'";
        }

        if (!empty($data['filter_date_added'])) {
            $sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        $query = $this->db->query($sql);
        return $query->row['total'];
    }

    public function getPickupScheduledOrders($vendor_id, $data = array()) {
        // $sql = "SELECT o.order_id, vop.name, vop.quantity, o.total, o.date_added, os.name AS status_name, 
        //               MIN(pi.image) AS image 
        //         FROM " . DB_PREFIX . "vendor_order_product vop 
        //         LEFT JOIN " . DB_PREFIX . "order o ON vop.order_id = o.order_id 
        //         LEFT JOIN " . DB_PREFIX . "product_image pi ON vop.product_id = pi.product_id 
        //         LEFT JOIN " . DB_PREFIX . "order_status os ON vop.order_status_id = os.order_status_id 
        //         WHERE vop.vendor_id = '" . (int)$vendor_id . "' 
        //         AND vop.order_status_id = '27' 
        //         AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
         $sql = "SELECT o.order_id, vop.name, vop.quantity, o.total, o.date_added, os.name AS status_name, p.image AS image 
                FROM " . DB_PREFIX . "vendor_order_product vop 
                LEFT JOIN " . DB_PREFIX . "order o ON vop.order_id = o.order_id 
                LEFT JOIN " . DB_PREFIX . "product p ON vop.product_id = p.product_id 
                LEFT JOIN " . DB_PREFIX . "order_status os ON vop.order_status_id = os.order_status_id 
                WHERE vop.vendor_id = '" . (int)$vendor_id . "' 
                AND vop.order_status_id IN (23, 27, 28)  
                AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_order_id'])) {
            $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        if (!empty($data['filter_product_name'])) {
            $sql .= " AND vop.name LIKE '%" . $this->db->escape($data['filter_product_name']) . "%'";
        }

        if (!empty($data['filter_date_added'])) {
            $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        $sql .= " GROUP BY o.order_id ORDER BY o.order_id DESC";

        if (isset($data['start']) && isset($data['limit']) && $data['start'] >= 0 && $data['limit'] > 0) {
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }
}
?>
