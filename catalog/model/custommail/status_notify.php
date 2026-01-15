<?php
class ModelCustommailStatusNotify extends Model {
    
    public function getOrderInfo($order_id) {
        $query = $this->db->query("SELECT o.customer_id, o.firstname, o.telephone, o.order_id, os.name as status, o.date_added, o.total, 
            o.email, o.customer_id, o.store_url, o.shipping_address_1, o.shipping_postcode, o.shipping_city, 
            o.shipping_zone, o.shipping_country, 
            o.shipping_method, o.payment_method, op.name as productname, op.model
            FROM `" . DB_PREFIX . "order` o 
            LEFT JOIN `" . DB_PREFIX . "order_status` os 
                ON o.order_status_id = os.order_status_id 
            LEFT JOIN `" . DB_PREFIX . "order_product` op 
                ON o.order_id = op.order_id 
            WHERE o.order_id = '" . (int)$order_id . "' 
            AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'
            AND os.name IN ('In Transit', 'Out For Delivery', 'Delivered')");
    
        return $query->rows; // <-- use rows instead of row
    }
    
    public function getVendorsByOrder($order_id, $vendor_id) {
        $query = $this->db->query("SELECT v.vendor_id, v.email, v.telephone, v.firstname as seller_name, vop.order_id, vop.name AS productname, vop.model, os.name AS status,
            o.awbno, vop.total, o.shipping_method, o.firstname as customer_name, o.lastname, o.date_added, 
            o.shipping_address_1, o.shipping_postcode, o.shipping_city, o.shipping_zone, o.shipping_country, o.store_url
            FROM " . DB_PREFIX . "vendor_order_product vop
            LEFT JOIN " . DB_PREFIX . "vendor v ON vop.vendor_id = v.vendor_id
            LEFT JOIN " . DB_PREFIX . "order o ON vop.order_id = o.order_id
            LEFT JOIN " . DB_PREFIX . "order_status os 
                ON vop.order_status_id = os.order_status_id 
                AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'
            WHERE vop.order_id = '" . (int)$order_id . "'
            AND vop.vendor_id = '" . (int)$vendor_id . "'
            AND os.name IN ('Processing', 'Delivered', 'Complete')");

        return $query->rows;

    }


    public function getVendorsFromOrder($order_id) {
        $query = $this->db->query("SELECT DISTINCT vendor_id FROM " . DB_PREFIX . "vendor_order_product WHERE order_id = '" . (int)$order_id . "'");
        return $query->rows;
    }
    
}
