<?php
class ModelVendorTodaysOrder extends Model
{
    public function getTotalTodaysOrders($vendor_id, $data = array())
    {
        // Get the total count of today's orders
        $sql = "SELECT COUNT(DISTINCT order_id) AS total
FROM " . DB_PREFIX . "vendor_order_product
WHERE vendor_id = '" . (int)$vendor_id . "'
AND DATE(date_added) = '" . $this->db->escape($data['date_added']) . "'";

        // Apply filter if order ID is provided
        if (!empty($data['filter_order_id'])) {
            $sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        // Apply filter if product name is provided
        if (!empty($data['filter_product_name'])) {
            $sql .= " AND name LIKE '%" . $this->db->escape($data['filter_product_name']) . "%'";
        }

        // Execute the query and return the result
        $query = $this->db->query($sql);
        return $query->row['total'];
    }

    public function getTodaysOrders($vendor_id, $data = array())
    {
        // Basic SQL query to get the today's orders
        $sql = "SELECT o.order_id, vop.name, vop.quantity, o.total, o.date_added, os.name AS status_name,
MIN(pi.image) AS image
FROM " . DB_PREFIX . "vendor_order_product vop
LEFT JOIN " . DB_PREFIX . "order o ON vop.order_id = o.order_id
LEFT JOIN " . DB_PREFIX . "product_image pi ON vop.product_id = pi.product_id
LEFT JOIN " . DB_PREFIX . "order_status os ON vop.order_status_id = os.order_status_id
WHERE vop.vendor_id = '" . (int)$vendor_id . "'
AND DATE(o.date_added) = '" . $this->db->escape($data['date_added']) . "'
AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        // Apply filter if order ID is provided
        if (!empty($data['filter_order_id'])) {
            $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        // Apply filter if product name is provided
        if (!empty($data['filter_product_name'])) {
            $sql .= " AND vop.name LIKE '%" . $this->db->escape($data['filter_product_name']) . "%'";
        }

        // Group by order_id and order by order_id DESC
        $sql .= " GROUP BY o.order_id ORDER BY o.order_id DESC";

        // Apply pagination limits if set
        if (isset($data['start']) && isset($data['limit']) && $data['start'] >= 0 && $data['limit'] > 0) {
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        // Execute the query and return the result
        $query = $this->db->query($sql);
        return $query->rows;
    }
}
