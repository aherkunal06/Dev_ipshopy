<?php
class ModelAssignAssignWork extends Model {

    // Get list of users with total seller count
    // public function getUsersWithSellerCount() {
    //     $sql = "SELECT u.user_id, u.username, COUNT(a.seller_id) AS total_sellers
    //             FROM " . DB_PREFIX . "user u
    //             LEFT JOIN " . DB_PREFIX . "assignment a ON u.user_id = a.user_id
    //             GROUP BY u.user_id
    //             ORDER BY u.user_id ASC";

    //     $query = $this->db->query($sql);
    //     return $query->rows;
    // }


    public function getUsersWithSellerCount($data = []) {
    $sql = "SELECT u.user_id, u.username, COUNT(DISTINCT s.seller_id) AS seller_count
            FROM " . DB_PREFIX . "user u
            LEFT JOIN " . DB_PREFIX . "assignment s ON u.user_id = s.user_id";

    $where = [];

    if (!empty($data['filter_username'])) {
        $where[] = "u.username LIKE '%" . $this->db->escape($data['filter_username']) . "%'";
    }

    if ($where) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    $sql .= " GROUP BY u.user_id ORDER BY u.username ASC";

    $query = $this->db->query($sql);

    return $query->rows;
}

//     public function getUsersWithSellerCount($data = []) {
//     $sql = "SELECT u.user_id, u.username, COUNT(s.seller_id) AS seller_count
//             FROM " . DB_PREFIX . "user u
//             LEFT JOIN " . DB_PREFIX . "assignment s ON u.user_id = s.user_id";

//     $where = [];

//     if (!empty($data['filter_username'])) {
//         $where[] = "u.username LIKE '%" . $this->db->escape($data['filter_username']) . "%'";
//     }

//     if ($where) {
//         $sql .= " WHERE " . implode(' AND ', $where);
//     }

//     $sql .= " GROUP BY u.user_id ORDER BY u.username ASC";

//     $query = $this->db->query($sql);

//     return $query->rows;
// }


    // Get detailed assignments for a specific user (for the View button)
    public function getSellerDetailsByUserId($user_id, $assign_date = '') {
        $sql = "SELECT a.*, concat(v.firstname,' ',v.lastname )AS seller_name, v.email, v.status
                FROM " . DB_PREFIX . "assignment a
                LEFT JOIN " . DB_PREFIX . "vendor v ON a.seller_id = v.vendor_id
                WHERE a.user_id = '" . (int)$user_id . "'";
                
        if (!empty($assign_date)) {
            $sql .= " AND DATE(a.assign_date) = '" . $this->db->escape($assign_date) . "'";
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    // public function assignSeller($user_id, $seller_id) {
    //     $this->db->query("INSERT INTO " . DB_PREFIX . "assignment SET user_id = '" . (int)$user_id . "', seller_id = '" . (int)$seller_id . "', assign_date = NOW()");
    // }

    public function getSellers() {
        $query = $this->db->query("
            SELECT vendor_id AS seller_id, firstname AS seller_name, lastname as seller_lastname
            FROM " . DB_PREFIX . "vendor
            ORDER BY seller_id
        ");
        return $query->rows;
    }

    //public function getSellerDetailsByUserId($user_id) {
     //   $sql = "SELECT a.*, v.firstname AS seller_name, v.email, v.status
       //         FROM " . DB_PREFIX . "assignment a
         //       LEFT JOIN " . DB_PREFIX . "vendor v ON a.seller_id = v.vendor_id
           //     WHERE a.user_id = '" . (int)$user_id . "'";

        //return $query->rows;
   // }
    
// public function getSellerCountByDate($user_id, $filter = []) {
//     $sql = "SELECT DATE(a.assign_date) AS assign_date, COUNT(*) AS seller_count, u.user_id, u.username
//             FROM " . DB_PREFIX . "assignment a
//             LEFT JOIN " . DB_PREFIX . "user u ON a.user_id = u.user_id
//             WHERE a.user_id = '" . (int)$user_id . "'";

//     if (!empty($filter['filter_assign_date'])) {
//         $sql .= " AND DATE(a.assign_date) = '" . $this->db->escape($filter['filter_assign_date']) . "'";
//     }

//     $sql .= " GROUP BY DATE(a.assign_date)
//               ORDER BY assign_date DESC";

//     $query = $this->db->query($sql);
//     return $query->rows;
// }
// public function getSellerCountByDate($user_id, $filter = []) {
//     $sql = "SELECT DATE(a.assign_date) AS assign_date, COUNT(*) AS seller_count, u.user_id, u.username
//             FROM " . DB_PREFIX . "assignment a
//             LEFT JOIN " . DB_PREFIX . "user u ON a.user_id = u.user_id
//             WHERE a.user_id = '" . (int)$user_id . "'";

//     // Add assign_date filter
//     if (!empty($filter['filter_assign_date'])) {
//         $sql .= " AND DATE(a.assign_date) = '" . $this->db->escape($filter['filter_assign_date']) . "'";
//     }

//     // Add username filter (optional, in case you support this too)
//     if (!empty($filter['filter_user_id'])) {
//         $sql .= " AND u.user_id = '" . (int)$filter['filter_user_id'] . "'";
//     } elseif (!empty($filter['filter_username'])) {
//         $sql .= " AND u.username LIKE '%" . $this->db->escape($filter['filter_username']) . "%'";
//     }

//     $sql .= " GROUP BY DATE(a.assign_date)
//               ORDER BY assign_date DESC";

//     $query = $this->db->query($sql);
//     return $query->rows;
// }
// public function getSellerCountByDate($user_id, $filter = []) {
//     $sql = "SELECT DATE(a.assign_date) AS assign_date, COUNT(*) AS seller_count, u.user_id, u.username
//             FROM " . DB_PREFIX . "assignment a
//             LEFT JOIN " . DB_PREFIX . "user u ON a.user_id = u.user_id
//             WHERE a.user_id = '" . (int)$user_id . "'";

//     if (!empty($filter['filter_assign_date'])) {
//         $sql .= " AND DATE(a.assign_date) = '" . $this->db->escape($filter['filter_assign_date']) . "'";
//     }

//     if (!empty($filter['filter_username'])) {
//         $sql .= " AND u.username LIKE '" . $this->db->escape($filter['filter_username']) . "%'";
//     }

//     $sql .= " GROUP BY DATE(a.assign_date)
//               ORDER BY assign_date DESC";

//     $query = $this->db->query($sql);
//     return $query->rows;
// }
public function getSellerCountByDate($user_id, $filter = []) {
    $sql = "SELECT DATE(a.assign_date) AS assign_date, COUNT(*) AS seller_count, u.user_id, u.username
            FROM " . DB_PREFIX . "assignment a
            LEFT JOIN " . DB_PREFIX . "user u ON a.user_id = u.user_id
            WHERE a.user_id = '" . (int)$user_id . "'";

    if (!empty($filter['filter_assign_date'])) {
        $sql .= " AND DATE(a.assign_date) = '" . $this->db->escape($filter['filter_assign_date']) . "'";
    }

    if (!empty($filter['filter_username'])) {
        $sql .= " AND u.username LIKE '%" . $this->db->escape($filter['filter_username']) . "%'";
    }

    $sql .= " GROUP BY DATE(a.assign_date)
              ORDER BY assign_date DESC";

    $query = $this->db->query($sql);
    return $query->rows;
}

public function assignSeller($user_id, $seller_id) {
    // First, check if this assignment already exists to avoid duplicates
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "assignment WHERE user_id = '" . (int)$user_id . "' AND seller_id = '" . (int)$seller_id . "'");

    if (!$query->num_rows) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "assignment SET user_id = '" . (int)$user_id . "', seller_id = '" . (int)$seller_id . "', assign_date = NOW()");
    }
}
public function unassignSeller($user_id,$seller_id) {
    // Delete only the specific user-seller assignment
    $this->db->query("DELETE FROM " . DB_PREFIX . "assignment WHERE user_id = '" . (int)$user_id . "' AND seller_id = '" .  (int)$seller_id . "'");

    // Check if deletion affected any rows
    if ($this->db->countAffected() > 0) {
        return true;

    } else {
        return false;
    }
}

public function unassignAllSellers($user_id) {
    // Delete all assignments for this user
    $this->db->query("DELETE FROM " . DB_PREFIX . "assignment WHERE user_id = '" . (int)$user_id . "'");
    
    // Return the number of rows affected
    return $this->db->countAffected();
}

}