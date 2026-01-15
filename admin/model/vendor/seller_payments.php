<?php
class ModelVendorSellerPayments extends Model {
    public function getVendorPayments($vendor_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_vendor_settlement WHERE vendor_id = '" . (int)$vendor_id . "'");
        return $query->rows;
    }

    public function getTotalPayments($vendor_id) {
        $query = $this->db->query("SELECT SUM(amount) as total FROM " . DB_PREFIX . "order_vendor_settlement WHERE vendor_id = '" . (int)$vendor_id . "'");
        return $query->row['total'];
    }
    public function getSellerPayments($filter_data = []) {
        $sql = "
            SELECT 
                v.vendor_id,
                v.firstname,
                v.lastname,
                vap.amount,
                vap.payment_method,
                vap.reference_number,
                vap.date_added
            FROM " . DB_PREFIX . "vendor_amount_pay vap
            LEFT JOIN " . DB_PREFIX . "vendor v ON vap.vendor_id = v.vendor_id
            ORDER BY vap.date_added DESC
        ";
    
        $query = $this->db->query($sql);
        return $query->rows;
    }    
    
}
