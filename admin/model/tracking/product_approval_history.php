<?php
class ModelTrackingProductApprovalHistory extends Model {
    
    // public function getProductGroupedByApprovedByAndDate($approved_by = null) {
    //     $sql = "SELECT approved_by, 
    //               DATE(approved_date) AS approved_date,
    //               SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS approved,
    //               SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS disapproved,
    //               SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS pending
    //         FROM " . DB_PREFIX . "product
    //         WHERE approved_by IS NOT NULL AND approved_date IS NOT NULL";

    // if ($approved_by) {
    //     $sql .= " AND approved_by = '" . $this->db->escape($approved_by) . "'";
    // }

    // $sql .= " GROUP BY approved_by, DATE(approved_date) ORDER BY approved_date DESC";

    // $query = $this->db->query($sql);
    // return $query->rows;
    // }
    
    public function getProductGroupedByApprovedByAndDate($approved_by = null, $approved_date = null) {
        $sql = "SELECT approved_by, 
                       DATE(approved_date) AS approved_date,
                       SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS approved,
                       SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS disapproved,
                       SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS pending
                FROM " . DB_PREFIX . "product
                WHERE approved_by IS NOT NULL AND approved_date IS NOT NULL";

        if ($approved_by) {
            $sql .= " AND approved_by = '" . $this->db->escape($approved_by) . "'";
        } else {
            // If no approved_by provided, return empty result (no data)
            return [];
        }

        if ($approved_date) {
            $sql .= " AND DATE(approved_date) = '" . $this->db->escape($approved_date) . "'";
        }

        $sql .= " GROUP BY approved_by, DATE(approved_date) ORDER BY approved_date DESC";

        $query = $this->db->query($sql);

        return $query->rows;
    }
    
}
