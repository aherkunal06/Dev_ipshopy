<?php
class ModelVendorHsnRequestform extends Controller {


// public function addHsnRequest($vendor_id, $data) {
//     $this->db->query("INSERT INTO " . DB_PREFIX . "vendor_hsn_request SET
//       vendor_id = '" . (int)$vendor_id . "',
//       hsn_code = '" . $this->db->escape($data['hsn_code']) . "',
//       gst_rate = '" . (float)$data['gst_rate'] . "',
//       description = '" . $this->db->escape($data['description']) . "',
//       status = 'Pending',
//       date_requested = NOW()
//     ");
//   }


    public function addHsnRequest($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "vendor_hsn_request SET 
            vendor_id = '" . (int)$this->vendor->getId() . "', 
            hsn_code = '" . $this->db->escape($data['hsn_code']) . "', 
            description = '" . $this->db->escape($data['description']) . "', 
            gst_rate = '" . (float)$data['gst_rate'] . "',
            status = 'Pending',
            date_added = NOW()");
    }
}
