<?php
class ModelVendorOrderInvoice extends Model {

    public function getVendorsWithInvoices($order_id) {

        // ✅ Prepare SQL
        $sql = "
            SELECT 
                v.vendor_id, 
                v.firstname, 
                v.email, 
                vop.order_id, 
                ov.invoice_path, 
                ov.date_added 
            FROM 
                " . DB_PREFIX . "vendor v 
            LEFT JOIN " . DB_PREFIX . "vendor_order_product vop 
                ON v.vendor_id = vop.vendor_id 
            LEFT JOIN " . DB_PREFIX . "order_invoice ov 
                ON vop.order_id = ov.order_id
            WHERE vop.order_id = '" . (int)$order_id . "'
        ";

        // ✅ Execute query
        $query = $this->db->query($sql);

        return $query->rows;
    }
    
}
