<?php
class ModelClickpostLabelGeneration extends Model
{

    public function getPickupInfo($order_id)
    {
        $pickup_query  = $this->db->query("
            SELECT v.zone_id, z.name as zone_name, v.address_1, v.email, v.postcode, v.city, v.gstin, v.firstname, v.lastname, v.telephone 
            FROM " . DB_PREFIX . "vendor_order_product vop
            JOIN " . DB_PREFIX . "vendor v ON vop.vendor_id = v.vendor_id
            LEFT JOIN " . DB_PREFIX . "zone z ON v.zone_id = z.zone_id
            WHERE vop.order_id = '" . (int)$order_id . "'
        ");

        $drop_query = $this->db->query("
            SELECT o.order_id, o.firstname, o.lastname, o.email, o.telephone, o.payment_address_1, o.payment_city, o.payment_postcode, z.name AS payment_zone,
            o.payment_code, o.total, o.invoice_no, o.date_added FROM " . DB_PREFIX . "order o LEFT JOIN " . DB_PREFIX . "zone z ON o.payment_zone_id = z.zone_id
            WHERE o.order_id = '" . (int)$order_id . "' LIMIT 1
        ");

        $shipment_query = $this->db->query("
            SELECT op.order_product_id, op.order_id, op.product_id, op.name AS order_name, op.model AS order_model, op.quantity AS order_quantity,
            op.price AS order_price, op.total AS order_total, op.tax AS order_tax, op.reward AS order_reward,
            p.sku, p.upc, p.ean, p.jan, p.isbn, p.mpn, p.hsn_code, p.location, p.stock_status_id, p.image, p.manufacturer_id, p.shipping, p.points, p.tax_class_id,
            p.date_available, p.weight, p.weight_class_id, p.length, p.width, p.height, p.length_class_id, p.subtract, p.minimum, p.sort_order,
            p.status, p.viewed, p.date_added AS product_added_date, p.date_modified AS product_modified_date FROM " . DB_PREFIX . "order_product op
            LEFT JOIN " . DB_PREFIX . "product p ON op.product_id = p.product_id
            WHERE op.order_id = '" . (int)$order_id . "'
        ");

        return [
            'pickup_query'   => $pickup_query,
            'drop_query'     => $drop_query,
            'shipment_query' => $shipment_query
        ];
    }


    public function saveClickpostLabelData($ipshopy_order_id, $commercial_invoice_url, $waybill, $reference_number, $shipping_charge, $label_url, $courier_partner_id, $courier_name, $tracking_id, $pickup_datetime)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "clickpost_order 
            SET 
                ipshopy_order_id = '" . (int)$ipshopy_order_id . "',
                commercial_invoice_url = '" . $this->db->escape($commercial_invoice_url) . "',
                waybill = '" . $this->db->escape($waybill) . "',
                reference_number = '" . $this->db->escape($reference_number) . "',
                shipping_charge = '". (int)$shipping_charge. "',
                label_url = '" . $this->db->escape($label_url) . "',
                courier_partner_id = '" . (int)$courier_partner_id . "',
                courier_name = '" . $this->db->escape($courier_name) . "',
                tracking_id = '" . $this->db->escape($tracking_id) . "',
                pickup_datetime = '". $this->db->escape($pickup_datetime). "',
                date_added = NOW()
        ");
    }

    public function recommendation($order_id) {
        $query = $this->db->query("
            SELECT v.postcode AS pickup_pincode, o.shipping_postcode AS drop_pincode, p.weight, p.length, p.width AS breadth, p.height,
            'FORWARD' AS delivery_type, op.total AS invoice_value, o.payment_code FROM " . DB_PREFIX . "vendor_order_product op JOIN 
            " . DB_PREFIX . "vendor v ON op.vendor_id = v.vendor_id JOIN " . DB_PREFIX . "order o ON op.order_id = o.order_id JOIN 
            " . DB_PREFIX . "product p ON op.product_id = p.product_id WHERE  op.order_id = '" . (int)$order_id . "'
        ");
    
        $results = [];
        foreach ($query->rows as $row) {
            $results[] = [
                'reference_number' => '',
                'item'             => '1',
                'pickup_pincode'   => $row['pickup_pincode'],
                'drop_pincode'     => $row['drop_pincode'],
                'invoice_value'    => (int)$row['invoice_value'],
                'weight'           => (float)$row['weight'],
                'length'           => (float)$row['length'],
                'breadth'          => (float)$row['breadth'],
                'height'           => (float)$row['height'],
                'delivery_type'    => $row['delivery_type'],
                'order_type'       => strtoupper($row['payment_code']) === 'COD' ? 'COD' : 'PREPAID'
            ];
        }
    
        return $results;
    }
    
    public function saveClickpostToOrder($ipshopy_order_id, $waybill, $label_url, $courier_partner_id, $courier_name)
    {
           $this->db->query("
            UPDATE " . DB_PREFIX . "order 
            SET 
                awbno = '" . $this->db->escape($waybill) . "',
                shipping_label = '" . $this->db->escape($label_url) . "',
                courier_id = '" . (int)$courier_partner_id . "',
                courier_name = '" . $this->db->escape($courier_name) . "'
            WHERE order_id = '" . $this->db->escape($ipshopy_order_id) . "'
        ");
    }
    
    
// 	public function assignOrderToManifest($order_id, $manifest_id ) {
// 		$this->db->query("UPDATE " . DB_PREFIX . "clickpost_order 
// 		SET manifest_id = '" . (int)$manifest_id . "'
// 		WHERE ipshopy_order_id = '" . (int)$order_id . "'");
// 	}
    
//     public function getManifestData($manifest_id) {

        
//          $order_query = $this->db->query("
//             SELECT o.order_id, o.firstname, o.lastname, o.email, o.telephone, o.payment_address_1, o.payment_city, o.payment_postcode, z.name AS payment_zone,
//             o.payment_code, o.total, o.invoice_no, o.courier_name, o.manifest_id, o.awbno, o.manifest_date, o.date_added 
//             FROM " . DB_PREFIX . "order o LEFT JOIN " . DB_PREFIX . "zone z ON o.payment_zone_id = z.zone_id WHERE o.order_id = '" . (int)$order_id . "' LIMIT 1
//         ");

        
//         $product_query = $this->db->query("
//             SELECT op.order_product_id, op.order_id, op.product_id, op.name AS order_name, op.model AS order_model, op.quantity AS order_quantity,
//             op.price AS order_price, op.total AS order_total, op.tax AS order_tax, op.reward AS order_reward,
//             p.sku, p.upc, p.ean, p.jan, p.isbn, p.mpn, p.hsn_code, p.location, p.stock_status_id, p.image, p.manufacturer_id, p.shipping, p.points, p.tax_class_id,
//             p.date_available, p.weight, p.weight_class_id, p.length, p.width, p.height, p.length_class_id, p.subtract, p.minimum, p.sort_order,
//             p.status, p.viewed, p.date_added AS product_added_date, p.date_modified AS product_modified_date FROM " . DB_PREFIX . "order_product op
//             LEFT JOIN " . DB_PREFIX . "product p ON op.product_id = p.product_id
//             WHERE op.order_id = '" . (int)$order_id . "'
//         ");
        
//         return [
//             'order_query'   => $order_query,
//             'product_query'     => $product_query
//         ];
        
//     }

    



}
