<?php
class ModelVendorVacation extends Model
{


    // public function getVacationsByVendorId($vendor_id)
    // {
    //     $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_vacation WHERE vendor_id = '" . (int)$vendor_id . "' ORDER BY vacation_id DESC");
    //     return $query->rows;
    // }
    
    public function getVacationsByVendorId($vendor_id)
    {
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "vendor_vacation 
            WHERE vendor_id = " . (int)$vendor_id . " 
            ORDER BY vacation_id DESC"
        );
        return $query->rows;
    }


    // Add vacation for a vendor
    public function addVacation($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "vendor_vacation SET 
              vendor_id = '" . (int)$data['vendor_id'] . "',
             vendor_name   = '" . $this->db->escape($data['vendor_name']) . "',
             display_name  = '" . $this->db->escape($data['display_name']) . "',
            start_date = '" . $this->db->escape($data['start_date']) . "',
            end_date = '" . $this->db->escape($data['end_date']) . "',
            reason = '" . $this->db->escape($data['reason']) . "',
            status = '" . $this->db->escape($data['status']) . "',
            date_added = '" . $this->db->escape($data['date_added']) . "'");
    }


    public function updateVacation($vacation_id, $status, $vendor_name, $display_name) {
        $this->db->query("UPDATE " . DB_PREFIX . "vendor_vacation 
            SET status = '" . $this->db->escape($status) . "',
                vendor_name = '" . $this->db->escape($vendor_name) . "', 
                display_name = '" . $this->db->escape($display_name) . "', 
                approval_date = NOW() 
            WHERE vacation_id = '" . (int)$vacation_id . "'");
    }
    
    // public function getProcessingOrderCount($vendor_id) {
    //     $query = $this->db->query("
    //         SELECT COUNT(*) AS total
    //         FROM " . DB_PREFIX . "vendor_order_product 
    //         WHERE vendor_id = '" . (int)$vendor_id . "' 
    //         AND order_status_id IN (1,2,3,8,9,10,11,12,13,14,15,16,17,19,20,21,22,23,24,25,26,27)  
    //     ");
    
    //     return (int)$query->row['total'];
    // }
    
    public function getProcessingOrderCount($vendor_id) {
        $pending_statuses = [1,2,3,8,9,10,11,12,13,14,15,16,17,19,20,21,23,24,25,26,27,28,29,31,32,33,34,35,36,37,38,39]; // Update as per actual "pending" status IDs
        $status_list = implode(',', $pending_statuses);
    
        $query = $this->db->query("
            SELECT COUNT(*) AS total
            FROM " . DB_PREFIX . "vendor_order_product vop
            LEFT JOIN " . DB_PREFIX . "order o ON vop.order_id = o.order_id
            WHERE vop.vendor_id = '" . (int)$vendor_id . "'
            AND o.order_status_id IN ($status_list)
        ");
    
        return (int)$query->row['total'];
    }
    




    // public function hasPendingOrders($vendor_id) {
    //     $query = $this->db->query("
    //         SELECT COUNT(*) AS total
    //         FROM " . DB_PREFIX . "vendor_order_product 
    //         WHERE order_status_id NOT IN (18, 5, 7)
    //         AND vendor_id = '" . (int)$vendor_id . "'
    //     ");

    //     return (int)$query->row['total'] > 0;
    // }
    public function hasPendingOrders($vendor_id)
    {
        $query = $this->db->query("
            SELECT COUNT(*) AS total
            FROM " . DB_PREFIX . "order_product op
            JOIN " . DB_PREFIX . "order o ON op.order_id = o.order_id
            JOIN " . DB_PREFIX . "vendor_to_product vtp ON op.product_id = vtp.product_id
            WHERE vtp.vendor_id = '" . (int)$vendor_id . "' 
            AND o.order_status_id NOT IN (5, 7, 18, 22, 30)
        ");

        return (int)$query->row['total'] > 0;
    }



    public function getVendorProducts($vendor_id)
    {
        $query = $this->db->query("
            SELECT p.*, vp.vendor_id
            FROM " . DB_PREFIX . "product p
            JOIN " . DB_PREFIX . "vendor_to_product vp ON p.product_id = vp.product_id
            WHERE vp.vendor_id = '" . (int)$vendor_id . "'
        ");

        return $query->rows;
    }
    
    


    // public function disableVendorProductsDuringVacation($vendor_id, $start_date, $end_date)
    // {
    //     // $this->db->query("UPDATE " . DB_PREFIX . "product SET status = 0 
    //     //     WHERE vendor_id = '" . (int)$vendor_id . "'");

    //     //     $this->db->query("
    //     //     UPDATE " . DB_PREFIX . "product p
    //     //     JOIN " . DB_PREFIX . "vendor_order_product vp ON p.product_id = vp.product_id
    //     //     SET p.status = 0
    //     //     WHERE vp.vendor_id = '" . (int)$vendor_id . "'
    //     // ");

        
    //     $query = $this->db->query("
    //         SELECT COUNT(*) AS pending_orders
    //         FROM " . DB_PREFIX . "vendor_order_product
    //         WHERE vendor_id = '" . (int)$vendor_id . "'
    //         AND order_status_id NOT IN (5, 7, 18)
    //     ");

    //     $pending_orders = (int)$query->row['pending_orders'];

    //     // Step 2: If no pending orders, disable the products
    //     if ($pending_orders == 0) {
    //         $this->db->query("
    //             UPDATE " . DB_PREFIX . "product p
    //             JOIN " . DB_PREFIX . "vendor_to_product vp ON p.product_id = vp.product_id
    //             SET p.status = 0
    //             WHERE vp.vendor_id = '" . (int)$vendor_id . "'
    //         ");
    //         return false;
    //     }

    //     // return $pending_orders === 0;
    //     return true; // Not disabled due to pending orders

    // }
    
    
    public function disableVendorProductsDuringVacation($vendor_id, $start_date, $end_date)
    {
        // count vendor's orders with status NOT 5,7,18
        $query = $this->db->query("
            SELECT COUNT(*) AS pending_orders
            FROM " . DB_PREFIX . "vendor_order_product vop
            WHERE vop.vendor_id = '" . (int)$vendor_id . "'
            AND vop.order_status_id NOT IN (5,7,18,30,22)
        ");
    
        $pending_orders = (int)$query->row['pending_orders'];
    
        if ($pending_orders > 0) {
            // There are orders in other statuses (pending, processing, etc)
            return [
                'status' => false,
                'pending_orders' => $pending_orders
            ];
        }
    
        // no pending orders, so disable products
        $this->db->query("
            UPDATE " . DB_PREFIX . "product p
            JOIN " . DB_PREFIX . "vendor_to_product vp ON p.product_id = vp.product_id
            SET p.status = 0
            WHERE vp.vendor_id = '" . (int)$vendor_id . "'
        ");
    
        return [
            'status' => true,
            'pending_orders' => 0
        ];
    }


    public function enableVendorProductsAfterVacation($vendor_id)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET status = 1 
            WHERE vendor_id = '" . (int)$vendor_id . "'");
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


    public function updateVacationStatus($vacation_id, $status)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "vendor_vacation SET status = '" . $this->db->escape($status) . "', approval_date = NOW() WHERE vacation_id = '" . (int)$vacation_id . "'");
    }

    public function getAllVacations()
    {
        // $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_vacation ORDER BY date_added DESC");
        $query = $this->db->query("SELECT 
        v.*, 
        vd.firstname, 
        vd.lastname, 
        vd.display_name 
    FROM " . DB_PREFIX . "vendor_vacation v 
    LEFT JOIN " . DB_PREFIX . "vendor vd ON v.vendor_id = vd.vendor_id 
    ORDER BY v.date_added DESC");

        return $query->rows;
    }

    // Get all vacations for a vendor
    public function getVacations($vendor_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_vacation WHERE vendor_id = '" . (int)$vendor_id . "' ORDER BY date_added DESC");
        return $query->rows;
    }

    public function getVacation($vacation_id)
    {
        // $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_vacation WHERE vacation_id = '" . (int)$vacation_id . "'");
        $query = $this->db->query("SELECT v.*, vd.firstname, vd.lastname, vd.display_name 
          FROM " . DB_PREFIX . "vendor_vacation v 
          LEFT JOIN " . DB_PREFIX . "vendor vd ON v.vendor_id = vd.vendor_id 
          where vacation_id = '" . (int)$vacation_id . "'
          ORDER BY v.date_added DESC");

        return $query->row;
    }
    // Disable products for vendors who are currently on vacation
    public function disableProductsForActiveVacations()
    {
        $today = date('Y-m-d');

        // Find vendors on vacation today
        $query = $this->db->query("SELECT DISTINCT vendor_id FROM " . DB_PREFIX . "vendor_vacation 
            WHERE status = 'Approved' 
            AND start_date <= '" . $this->db->escape($today) . "' 
            AND end_date >= '" . $this->db->escape($today) . "'");

        foreach ($query->rows as $row) {
            $vendor_id = (int)$row['vendor_id'];

            // Disable all products for this vendor
            $this->db->query("UPDATE " . DB_PREFIX . "product
                SET status = 0 
                WHERE vendor_id = '" . $vendor_id . "'");
        }
    }

    // Optional: Re-enable products after vacation ends
    public function enableProductsForReturnedVendors()
    {
        $today = date('Y-m-d');

        // Find vendors whose vacation ended yesterday or earlier
        $query = $this->db->query("SELECT DISTINCT vendor_id FROM " . DB_PREFIX . "vendor_vacation 
            WHERE status = 'Approved' 
            AND end_date < '" . $this->db->escape($today) . "'");

        foreach ($query->rows as $row) {
            $vendor_id = (int)$row['vendor_id'];

            // Enable products for the vendor
            $this->db->query("UPDATE " . DB_PREFIX . "product 
                SET status = 1 
                WHERE vendor_id = '" . $vendor_id . "'");
        }
    }
}
