<?php
class ModelVendorHsnRequestlist extends Model {
    public function getHsnRequests() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_hsn_request ORDER BY date_added DESC");
        return $query->rows;
    }

    // public function updateStatus($id, $status) {
    //     $this->db->query("UPDATE " . DB_PREFIX . "vendor_hsn_request SET status = '" . $this->db->escape($status) . "', date_modified = NOW() WHERE id = '" . (int)$id . "'");


    //       // Fetch vendor email
    // $query = $this->db->query("SELECT v.email, v.firstname, r.hsn_code 
    //     FROM " . DB_PREFIX . "vendor_hsn_request r 
    //     LEFT JOIN " . DB_PREFIX . "vendor v ON r.vendor_id = v.vendor_id 
    //     WHERE r.id = '" . (int)$id . "'");

    // // Fallback if no vendor email
    //     return $query->num_rows ? $query->row : ['email' => '', 'firstname' => '', 'hsn_code' => ''];

    // }

    public function updateStatus($id, $status) {
    // Update status
    $this->db->query("UPDATE " . DB_PREFIX . "vendor_hsn_request 
                      SET status = '" . $this->db->escape($status) . "', date_modified = NOW() 
                      WHERE id = '" . (int)$id . "'");

    // Get vendor & HSN details
    $query = $this->db->query("SELECT v.email, v.firstname, r.hsn_code, r.description, r.gst_rate 
        FROM " . DB_PREFIX . "vendor_hsn_request r 
        LEFT JOIN " . DB_PREFIX . "vendor v ON r.vendor_id = v.vendor_id 
        WHERE r.id = '" . (int)$id . "'");

    $vendor = $query->row;

    // If status is Approved, insert into oc_hsn_data
    if ($status == 'Approved') {
        // First check if already exists
        $check = $this->db->query("SELECT * FROM " . DB_PREFIX . "hsn_data WHERE hsn_code = '" . $this->db->escape($vendor['hsn_code']) . "'");
        if (!$check->num_rows) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "hsn_data 
                SET hsn_code = '" . $this->db->escape($vendor['hsn_code']) . "',
                    description = '" . $this->db->escape($vendor['description']) . "',
                    gst_rate = '" . (float)$vendor['gst_rate'] . "'");
        }
    }

    return $vendor;
}

}
