<?php
class ModelDailyReportReport extends Model {

    public function TotalOrderStatus() {
        $query = $this->db->query("
            SELECT v.vendor_id, v.email, os.name as order_status, count(*) as total_orders
                FROM `" . DB_PREFIX . "vendor_order_product` vop
                LEFT JOIN `" . DB_PREFIX . "order_status` os ON os.order_status_id = vop.order_status_id
                LEFT JOIN `" . DB_PREFIX . "vendor` v ON vop.vendor_id = v.vendor_id
                WHERE 
                    os.name in ('Processing', 'Label Generated', 'Breached', 'Delivered', 'Complete', 'Canceled', 'Reversed', 'Return In Transit', 'Return Delivered',
                    'RTO', 'RTO In Transit', 'RTO Delivered') 
                    AND vop.vendor_id IS NOT NULL
                Group by v.vendor_id, os.name
        ");
    
        return $query->rows;
    }    

    public function getOrdersByStatus($status) {
        $query = $this->db->query("
            SELECT 
                o.order_id,
                vop.name as product_name,
                vop.quantity,
                vop.total,
                o.shipping_method,
                o.awbno,
                os.name AS status,
                vop.date_added,
                v.vendor_id,
                v.email,
                v.telephone,
                v.firstname
            FROM `" . DB_PREFIX . "order` o
            LEFT JOIN `" . DB_PREFIX . "order_status` os ON (o.order_status_id = os.order_status_id)
            LEFT JOIN `" . DB_PREFIX . "vendor_order_product` vop ON (o.order_id = vop.order_id)
            LEFT JOIN `" . DB_PREFIX . "vendor` v ON (vop.vendor_id = v.vendor_id)
            WHERE 
                os.name = '" . $this->db->escape($status) . "' 
                AND vop.vendor_id IS NOT NULL
            ORDER BY o.order_id DESC
        ");
    
        return $query->rows;
    }    

}
