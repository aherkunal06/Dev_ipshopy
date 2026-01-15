<?php
class ModelTrackingSellerList extends Model {

public function getSellerRegistrations($filter_username = null, $filter_date = null) {
    // Start with the base query
    $sql = "SELECT 
                DATE(date_added) AS date, 
                registered_by, 
                COUNT(*) AS total_registrations,
                SUM(CASE WHEN status = 1 AND approved = 1 THEN 1 ELSE 0 END) AS approve_count,
                SUM(CASE WHEN status = 0 AND approved = 1 THEN 1 ELSE 0 END) AS disapprove_count,
                SUM(CASE WHEN (status = 0 AND approved = 0) OR (status = 1 AND approved = 0) THEN 1 ELSE 0 END) AS pending_count
            FROM oc_vendor
            WHERE 1"; // Start with "WHERE 1" for flexibility in adding conditions

    // Apply both filter for 'filter_username' and 'filter_date' if both are provided
    if ($filter_date && $filter_username) {
        $sql .= " AND DATE(date_added) = '" . $this->db->escape($filter_date) . "' 
                  AND registered_by LIKE '%" . $this->db->escape($filter_username) . "%'";
    } elseif ($filter_date) {
        // Apply only filter_date if filter_username is not provided
        $sql .= " AND DATE(date_added) = '" . $this->db->escape($filter_date) . "'";
    } elseif ($filter_username) {
        // Apply only filter_username if filter_date is not provided
        $sql .= " AND registered_by LIKE '%" . $this->db->escape($filter_username) . "%'";
    }

    // Add the group by and order by clauses
    $sql .= " GROUP BY DATE(date_added), registered_by
              ORDER BY date DESC";

    // Execute the query
    $query = $this->db->query($sql);

    // Return the result rows
    return $query->rows;
}


    // Get vendors by 'registered_by' and date
    public function getVendorsByRegisteredByAndDate($registered_by, $date) {
        $query = $this->db->query("SELECT vendor_id, firstname, lastname, email, display_name, status, approved, product_status
            FROM " . DB_PREFIX . "vendor 
            WHERE registered_by = '" . $this->db->escape($registered_by) . "' 
            AND DATE(date_added) = '" . $this->db->escape($date) . "'");

        return $query->rows;
    }

public function getTotalRegistrationsByUser($filter_username = null) {
    $sql = "SELECT registered_by, COUNT(*) as total FROM " . DB_PREFIX . "vendor WHERE 1";

    if (!empty($filter_username)) {
        $sql .= " AND registered_by LIKE '%" . $this->db->escape($filter_username) . "%'";
    }

    $sql .= " GROUP BY registered_by";

    $query = $this->db->query($sql);

    return $query->rows;
}


public function getFilteredRegistrations($filter_username = null) {
    $sql = "SELECT registered_by, COUNT(*) as total
            FROM oc_vendor
            WHERE 1"; // "WHERE 1" allows for flexibility when adding conditions

    // Apply filter condition for username
    if ($filter_username) {
        $sql .= " AND registered_by LIKE '%" . $this->db->escape($filter_username) . "%'";
    }

    $sql .= " GROUP BY registered_by";

    $query = $this->db->query($sql);

    return $query->rows;
}

}