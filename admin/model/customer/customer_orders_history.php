<?php
class ModelCustomerCustomerOrdersHistory extends Model{
	
    
    public function getOrdersByCustomerId($filter_data) {
        $customer_id = (int)$filter_data['customer_id'];

        $sql = "SELECT 
                    o.order_id, 
                    o.date_added, 
                    o.total, 
                    o.payment_method, 
                    os.name AS status,
                    op.name AS product_name,
                    op.quantity AS product_quantity,
                    op.price AS product_price,
                    p.image AS product_image,
                    CASE 
                        WHEN ovs.payment_status IS NULL OR ovs.payment_status = '' THEN 'Unpaid' 
                        ELSE 'Paid' 
                    END AS payment_status
                FROM " . DB_PREFIX . "order o
                LEFT JOIN " . DB_PREFIX . "order_status os 
                    ON o.order_status_id = os.order_status_id
                LEFT JOIN " . DB_PREFIX . "order_product op 
                    ON o.order_id = op.order_id
                LEFT JOIN " . DB_PREFIX . "product p
                    ON op.product_id = p.product_id
                LEFT JOIN " . DB_PREFIX . "order_vendor_settlement ovs 
                    ON o.order_id = ovs.order_id
                WHERE o.customer_id = '" . $customer_id . "' 
                AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        // Filters (same as before)
        if (!empty($filter_data['filter_order_status'])) {
            $implode = array();
            $order_statuses = explode(',', $filter_data['filter_order_status']);

            foreach ($order_statuses as $order_status_id) {
                $implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
            }

            if ($implode) {
                $sql .= " AND (" . implode(" OR ", $implode) . ")";
            }
        } elseif (isset($filter_data['filter_order_status_id']) && $filter_data['filter_order_status_id'] !== '') {
            $sql .= " AND o.order_status_id = '" . (int)$filter_data['filter_order_status_id'] . "'";
        }

        if (!empty($filter_data['filter_order_id'])) {
            $sql .= " AND o.order_id = '" . (int)$filter_data['filter_order_id'] . "'";
        }

        if (!empty($filter_data['filter_status'])) {
            $sql .= " AND os.name LIKE '%" . $this->db->escape($filter_data['filter_status']) . "%'";
        }

        if (!empty($filter_data['filter_payment_status'])) {
            if (strtolower($filter_data['filter_payment_status']) == 'paid') {
                $sql .= " AND ovs.payment_status IS NOT NULL AND ovs.payment_status != ''";
            } else {
                $sql .= " AND (ovs.payment_status IS NULL OR ovs.payment_status = '')";
            }
        }

        if (!empty($filter_data['filter_payment_method'])) {
            $sql .= " AND o.payment_method = '" . $this->db->escape($filter_data['filter_payment_method']) . "'";
        }

        if (!empty($filter_data['filter_date_added'])) {
            $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($filter_data['filter_date_added']) . "')";
        }

        $sql .= " ORDER BY o.order_id DESC";

        $order_query = $this->db->query($sql);

        // Summary query with static statuses and grouping of RTO statuses + Return, Refunded, Reversed in Return group
        $summary_sql = "
            SELECT 
                sg.status_group AS order_status,
                COUNT(o_data.order_id) AS total_orders,
                IFNULL(SUM(o_data.total), 0) AS total_amount
            FROM (
                SELECT 'Complete' AS status_group
                UNION ALL SELECT 'Canceled'
                UNION ALL SELECT 'RTO'
                UNION ALL SELECT 'Return'
                UNION ALL SELECT 'Other'
            ) AS sg
            LEFT JOIN (
                SELECT 
                    o.order_id,
                    o.total,
                    CASE 
                        WHEN os.name IN ('Complete') THEN 'Complete'
                        WHEN os.name IN ('Canceled', 'Cancelled') THEN 'Canceled'
                        WHEN os.name IN ('RTO', 'RTO Delivered', 'RTO In Transit') THEN 'RTO'
                        WHEN os.name IN ('Return', 'Refunded', 'Reversed') THEN 'Return'
                        ELSE 'Other'
                    END AS status_group
                FROM " . DB_PREFIX . "order o
                LEFT JOIN " . DB_PREFIX . "order_status os 
                    ON o.order_status_id = os.order_status_id
                WHERE o.customer_id = '" . $customer_id . "'
                AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($filter_data['filter_date_added'])) {
            $summary_sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($filter_data['filter_date_added']) . "')";
        }

        $summary_sql .= "
            ) AS o_data ON sg.status_group = o_data.status_group
            GROUP BY sg.status_group
            ORDER BY FIELD(sg.status_group, 'Complete', 'Canceled', 'RTO', 'Return', 'Other')
        ";

        $summary_query = $this->db->query($summary_sql);

        $summary_rows = $summary_query->rows;

        $grand_total_orders = 0;
        $grand_total_amount = 0.0;

        foreach ($summary_rows as $row) {
            $grand_total_orders += (int)$row['total_orders'];
            $grand_total_amount += (float)$row['total_amount'];
        }

        return [
            'orders' => $order_query->rows,
            'summary' => $summary_rows,
            'grand_total_orders' => $grand_total_orders,
            'grand_total_amount' => $grand_total_amount
        ];
    }

}