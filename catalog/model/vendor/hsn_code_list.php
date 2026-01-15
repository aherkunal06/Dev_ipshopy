<?php

class ModelVendorHsnCodeList   extends Model {
    public function addHSNCode($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "hsn_data SET hsn_code = '" . $this->db->escape($data['hsn_code']) . "', description = '" . $this->db->escape($data['description']) . "', gst_rate = '" . (float)$data['gst_rate'] . "'");
    }

    // public function getHSNCodes() {
    //     $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "hsn_data ORDER BY hsn_id DESC");
    //     return $query->rows;
    // }
    
    public function getHSNCodes($data = []) {
        $sql = "SELECT * FROM " . DB_PREFIX . "hsn_data WHERE 1";
    
        if (!empty($data['filter_hsn_code'])) {
            $sql .= " AND hsn_code LIKE '%" . $this->db->escape($data['filter_hsn_code']) . "%'";
        }
    
        if (!empty($data['filter_description'])) {
            $sql .= " AND description LIKE '%" . $this->db->escape($data['filter_description']) . "%'";
        }
    
        if (!empty($data['filter_gst_rate'])) {
            $sql .= " AND gst_rate = '" . (float)$data['filter_gst_rate'] . "'";
        }
    
        $sql .= " ORDER BY hsn_id DESC";
    
        if (isset($data['start']) && isset($data['limit'])) {
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
    
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    
    public function getTotalHSNCodes($data = []) {
        $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "hsn_data WHERE 1";
    
        if (!empty($data['filter_hsn_code'])) {
            $sql .= " AND hsn_code LIKE '%" . $this->db->escape($data['filter_hsn_code']) . "%'";
        }
    
        if (!empty($data['filter_description'])) {
            $sql .= " AND description LIKE '%" . $this->db->escape($data['filter_description']) . "%'";
        }
    
        if (!empty($data['filter_gst_rate'])) {
            $sql .= " AND gst_rate = '" . (float)$data['filter_gst_rate'] . "'";
        }
    
        $query = $this->db->query($sql);
        return $query->row['total'];
    }

    public function updateHSNCode($data) {
        $this->db->query("UPDATE " . DB_PREFIX . "hsn_data SET 
            hsn_code = '" . $this->db->escape($data['hsn_code']) . "', 
            description = '" . $this->db->escape($data['description']) . "', 
            gst_rate = '" . (float)$data['gst_rate'] . "' 
            WHERE hsn_id = '" . (int)$data['hsn_id'] . "'");
    }
    
    
    

}

