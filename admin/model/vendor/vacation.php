<?php
class ModelVendorVacation extends Model
{


    public function addVacation($vendor_id, $status, $start_date, $end_date, $vendor_name, $display_name)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "vendor_vacation 
            SET vendor_id = '" . (int)$vendor_id . "', 
                status = '" . $this->db->escape($status) . "', 
                start_date = '" . $this->db->escape($start_date) . "', 
                end_date = '" . $this->db->escape($end_date) . "', 
                vendor_name = '" . $this->db->escape($vendor_name) . "', 
                display_name = '" . $this->db->escape($display_name) . "', 
                date_added = NOW()");
    }

    public function updateVacation($vacation_id, $status, $vendor_name, $display_name)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "vendor_vacation 
            SET status = '" . $this->db->escape($status) . "',
                vendor_name = '" . $this->db->escape($vendor_name) . "', 
                display_name = '" . $this->db->escape($display_name) . "', 
                approval_date = NOW() 
            WHERE vacation_id = '" . (int)$vacation_id . "'");
    }


    public function getAllVacations()
    {
        $query = $this->db->query("SELECT 
          v.*, 
          vd.firstname, 
          vd.lastname, 
          vd.display_name,
          (
            SELECT COUNT(*) 
            FROM " . DB_PREFIX . "vendor_to_product vtp 
            LEFT JOIN " . DB_PREFIX . "product p ON vtp.product_id = p.product_id 
            WHERE vtp.vendor_id = vd.vendor_id AND p.status = 1
          ) AS active_products,
          (
            SELECT SUM(p.quantity) 
            FROM " . DB_PREFIX . "vendor_to_product vtp 
            LEFT JOIN " . DB_PREFIX . "product p ON vtp.product_id = p.product_id 
            WHERE vtp.vendor_id = vd.vendor_id
          ) AS total_quantity
        FROM " . DB_PREFIX . "vendor_vacation v
        LEFT JOIN " . DB_PREFIX . "vendor vd ON v.vendor_id = vd.vendor_id
        ORDER BY v.date_added DESC");

        return $query->rows;
    }
    public function getTotalVacations($filter_data = []) {
        $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_vacation WHERE 1";
    
        if (!empty($filter_data['filter_name'])) {
            $sql .= " AND vendor_id IN (SELECT vendor_id FROM " . DB_PREFIX . "vendor WHERE CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($filter_data['filter_name']) . "%')";
        }
    
        if (!empty($filter_data['filter_date'])) {
            $sql .= " AND DATE(date_added) = '" . $this->db->escape($filter_data['filter_date']) . "'";
        }
    
        if ($filter_data['filter_approved'] !== '') {
            $sql .= " AND status = '"  . (int)$filter_data['filter_approved'] . "'";
        }
    
        if ($filter_data['filter_status'] !== '') {
            $sql .= " AND status = '" . (int)$filter_data['filter_status'] . "'";
        }
    
        $query = $this->db->query($sql);

        return $query->row['total'];
    }
    
    
    public function getVacations($filter_data = []) {
        $sql = "SELECT * FROM " . DB_PREFIX . "vendor_vacation WHERE 1";
    
        if (!empty($filter_data['filter_name'])) {
            $sql .= " AND vendor_id IN (SELECT vendor_id FROM " . DB_PREFIX . "vendor WHERE CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($filter_data['filter_name']) . "%')";
        }
    
        if (!empty($filter_data['filter_date'])) {
            $sql .= " AND DATE(date_added) = '" . $this->db->escape($filter_data['filter_date']) . "'";
        }
        
        // if ($filter_data['filter_approved'] !== '') {
        //     $sql .= " AND status = '" . $this->db->escape($filter_data['filter_approved']) . "'";  // Use 'status' column, not 'approved'
        // }
    
         // Filter by Status (Approved, Pending, Rejected)
        if (!empty($filter_data['filter_status'])) {
            $sql .= " AND status = '" . $this->db->escape($filter_data['filter_status']) . "'";
        }

        // if ($filter_data['filter_status'] !== '') {
        //     $sql .= " AND status = '" . (int)$filter_data['filter_status'] . "'";
        // }
    
        $sql .= " ORDER BY date_added DESC";
    
        if (isset($filter_data['start']) && isset($filter_data['limit'])) {
            $sql .= " LIMIT " . (int)$filter_data['start'] . "," . (int)$filter_data['limit'];
        }
    
        $query = $this->db->query($sql);
        return $query->rows;
    }


    
    

    public function getProcessingOrderCount($vendor_id)
    {
        $sql = "
        SELECT COUNT(*) AS processing_orders 
        FROM " . DB_PREFIX . "vendor_order_product vop 
        LEFT JOIN " . DB_PREFIX . "order o ON vop.order_id = o.order_id 
        WHERE vop.vendor_id = '" . (int)$vendor_id . "' 
        AND o.order_status_id NOT IN (5, 7, 18)"; // Assuming 5,7,18 are Delivered/Completed/Cancelled

        $query = $this->db->query($sql);

        return isset($query->row['processing_orders']) ? (int)$query->row['processing_orders'] : 0;
    }




    public function getExpiredVacations($today)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_vacation 
            WHERE end_date < '" . $this->db->escape($today) . "' AND status = 'Approved'");

        return $query->rows;
    }

    public function enableVendorProducts($vendor_id)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET status = 1 
            WHERE vendor_id = '" . (int)$vendor_id . "'");
    }

    public function markVacationCompleted($vacation_id)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "vendor_vacation 
            SET status = 'Completed' 
            WHERE vacation_id = '" . (int)$vacation_id . "'");
    }

    public function getVacation($vacation_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_vacation WHERE vacation_id = '" . (int)$vacation_id . "'");
        return $query->row;
    }

    public function updateVacationStatus($vacation_id, $status)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "vendor_vacation SET status = '" . $this->db->escape($status) . "', approval_date = NOW() WHERE vacation_id = '" . (int)$vacation_id . "'");
    }

    public function getVendorActiveProductCount($vendor_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total 
            FROM " . DB_PREFIX . "vendor_to_product vtp
            LEFT JOIN " . DB_PREFIX . "product p ON vtp.product_id = p.product_id
            WHERE vtp.vendor_id = '" . (int)$vendor_id . "' AND p.status = 1");

        return $query->row['total'];
    }


    public function getVendorTotalQuantity($vendor_id)
    {
        $query = $this->db->query("SELECT SUM(p.quantity) AS total 
        FROM " . DB_PREFIX . "vendor_to_product vtp
        LEFT JOIN " . DB_PREFIX . "product p ON vtp.product_id = p.product_id
        WHERE vtp.vendor_id = '" . (int)$vendor_id . "'");

        return $query->row['total'];
    }


    public function disableProductsForActiveVacations()
    {
        // Get all approved vacations that are active today
        $query = $this->db->query("
            SELECT vendor_id 
            FROM " . DB_PREFIX . "vendor_vacation 
            WHERE status = 'Approved' 
              AND CURDATE() BETWEEN start_date AND end_date
        ");

        foreach ($query->rows as $row) {
            $vendor_id = (int)$row['vendor_id'];
            // Disable all products of that vendor
            $this->db->query("
                UPDATE " . DB_PREFIX . "product 
                SET status = 0 
                WHERE vendor_id = '" . $vendor_id . "'
            ");
        }
    }

    public function enableProductsAfterVacation()
    {
        // Vendors whose vacation ended before today
        $query = $this->db->query("
            SELECT DISTINCT vendor_id 
            FROM " . DB_PREFIX . "vendor_vacation 
            WHERE status = 'Approved' 
              AND end_date < CURDATE()
        ");

        foreach ($query->rows as $row) {
            $vendor_id = (int)$row['vendor_id'];
            // Enable products back
            $this->db->query("
                UPDATE " . DB_PREFIX . "product 
                SET status = 1 
                WHERE vendor_id = '" . $vendor_id . "'
            ");
        }
    }
}