<?php
class ModelVendorAllOrder extends Model {
    
//   public function getTotalAllOrders($vendor_id) {
//     $sql = "SELECT COUNT(DISTINCT o.order_id) AS total 
//                 FROM " . DB_PREFIX . "vendor_order_product vop 
//                 LEFT JOIN " . DB_PREFIX . "order o ON vop.order_id = o.order_id 
//                 LEFT JOIN " . DB_PREFIX . "order_status os ON vop.order_status_id = os.order_status_id 
//                 WHERE vop.vendor_id = '" . (int)$vendor_id . "' AND vop.order_status_id != '0' ";

//     // Apply filters
//     if (!empty($data['filter_order_id'])) {
//       $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
//     }

//     if (!empty($data['filter_product_name'])) {
//       $sql .= " AND vop.name LIKE '%" . $this->db->escape($data['filter_product_name']) . "%'";
//     }

//     if (!empty($data['filter_date_added'])) {
//       $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
//     }

//     $query = $this->db->query($sql);
//     return $query->row['total'];
//   }

//   public function getAllOrders($vendor_id, $data = array()) {
//     $sql = "SELECT o.order_id, vop.name, vop.quantity, o.total, o.date_added, os.name AS status_name, 
//                 MIN(pi.image) AS image 
//                 FROM " . DB_PREFIX . "vendor_order_product vop
//                 LEFT JOIN " . DB_PREFIX . "order o ON vop.order_id = o.order_id
//                 LEFT JOIN " . DB_PREFIX . "product_image pi ON vop.product_id = pi.product_id
//                 LEFT JOIN " . DB_PREFIX . "order_status os ON vop.order_status_id = os.order_status_id
//                 WHERE vop.vendor_id = '" . (int)$vendor_id . "'
//                 AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'";

//     // Apply filters
//     if (!empty($data['filter_order_id'])) {
//       $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
//     }

//     if (!empty($data['filter_product_name'])) {
//       $sql .= " AND vop.name LIKE '%" . $this->db->escape($data['filter_product_name']) . "%'";
//     }

//     if (!empty($data['filter_date_added'])) {
//       $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
//     }

//     // Group by order_id and order by order_id in descending order
//     $sql .= " GROUP BY o.order_id ORDER BY o.order_id DESC";

//     // Pagination logic
//     if (isset($data['start']) && isset($data['limit']) && $data['start'] >= 0 && $data['limit'] > 0) {
//       $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
//     }

//     $query = $this->db->query($sql);
//     return $query->rows;
//   }

    public function getTotalAllOrders($vendor_id, $data = array())
    {
        $sql = "SELECT COUNT(DISTINCT o.order_id) AS total 
                FROM " . DB_PREFIX . "vendor_order_product vop 
                LEFT JOIN " . DB_PREFIX . "order o ON vop.order_id = o.order_id 
                LEFT JOIN " . DB_PREFIX . "order_status os ON vop.order_status_id = os.order_status_id 
                WHERE vop.vendor_id = '" . (int)$vendor_id . "' AND vop.order_status_id != '0' ";
    
        // Apply filters
        if (!empty($data['filter_order_id'])) {
            $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
        }
    
        if (!empty($data['filter_product_name'])) {
            $sql .= " AND vop.name LIKE '%" . $this->db->escape($data['filter_product_name']) . "%'";
        }
    
        if (!empty($data['filter_date_added'])) {
            $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }
    
        $query = $this->db->query($sql);
        return $query->row['total'];
    }
    // ...existing code...
  
  public function getAllOrders($vendor_id, $data = array())
  {
    $sql = "SELECT o.order_id, vop.name, vop.quantity, o.total, o.date_added, os.name AS status_name, 
                MIN(pi.image) AS image 
                FROM " . DB_PREFIX . "vendor_order_product vop
                LEFT JOIN " . DB_PREFIX . "order o ON vop.order_id = o.order_id
                LEFT JOIN " . DB_PREFIX . "product_image pi ON vop.product_id = pi.product_id
                LEFT JOIN " . DB_PREFIX . "order_status os ON vop.order_status_id = os.order_status_id
                WHERE vop.vendor_id = '" . (int)$vendor_id . "'
                AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'";

    // Apply filters
    if (!empty($data['filter_order_id'])) {
      $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
    }

    if (!empty($data['filter_product_name'])) {
      $sql .= " AND vop.name LIKE '%" . $this->db->escape($data['filter_product_name']) . "%'";
    }

    if (!empty($data['filter_date_added'])) {
      $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
    }

    // Group by order_id and order by order_id in descending order
    $sql .= " GROUP BY o.order_id ORDER BY o.order_id DESC";

    // Pagination logic
    if (isset($data['start']) && isset($data['limit']) && $data['start'] >= 0 && $data['limit'] > 0) {
      $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
    }

    $query = $this->db->query($sql);
    return $query->rows;
  }
  
}
