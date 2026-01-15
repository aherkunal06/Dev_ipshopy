<?php
class ModelTrackingProductAddHistory extends Model {
    
    public function getProductGroupedByAddedByAndDate($added_by = null, $filter_date = null) {
        $sql = "SELECT 
                    added_by,
                    DATE(date_added) AS date_added,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS approved,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS disapproved,
                    SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS pending,
                    COUNT(*) AS total
                FROM " . DB_PREFIX . "product
                WHERE 1";
        
        if (!empty($added_by)) {
            $sql .= " AND added_by = '" . $this->db->escape($added_by) . "'";
        }
        
        if (!empty($filter_date)) {
            $sql .= " AND DATE(date_added) = '" . $this->db->escape($filter_date) . "'";
        }
        
        $sql .= " GROUP BY added_by, DATE(date_added)
                  ORDER BY DATE(date_added) DESC";
        
        $query = $this->db->query($sql);
        return $query->rows;
    }


    public function getUsersAndVendors($filter = []) {
        $sql = "(SELECT user_id, username, firstname, lastname, email, status, date_added FROM " . DB_PREFIX . "user)
                UNION
                (SELECT vendor_id AS user_id, username, firstname, lastname, email, status, date_added FROM " . DB_PREFIX . "vendor)";

        $query = $this->db->query($sql);
        $results = $query->rows;

        if (!empty($filter['filter_name']) || !empty($filter['filter_email'])) {
            $results = array_filter($results, function ($row) use ($filter) {
                $name = $row['username'] ?: ($row['firstname'] . ' ' . $row['lastname']);
                return (stripos($name, $filter['filter_name']) !== false || stripos($row['email'], $filter['filter_email']) !== false);
            });
        }

        return array_values($results);
    }


    public function getProductCountsGroupedByUser() {
        $sql = "SELECT added_by, 
                       COUNT(*) AS total,
                       SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS approved,
                       SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS disapproved,
                       SUM(CASE WHEN status IS NULL THEN 1 ELSE 0 END) AS pending
                FROM " . DB_PREFIX . "product
                GROUP BY added_by";
    
        $query = $this->db->query($sql);
    
        $counts = [];
    
        foreach ($query->rows as $row) {
            $key = $row['added_by'];
            $counts[$key] = [
                'total'       => (int)$row['total'],
                'approved'    => (int)$row['approved'],
                'disapproved' => (int)$row['disapproved'],
                'pending'     => (int)$row['pending']
            ];
        }
    
        return $counts;
    }

}
