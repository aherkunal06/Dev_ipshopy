<?php
class ModelVendorClaimReturnClaim extends Model
{
    public function index() {}
    public function getClaimById($claim_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "return_claim WHERE claim_id = '" . (int)$claim_id . "'");
        return $query->row;
    }
    public function getClaimIssueDetails()
    {
    $query = $this->db->query("
          SELECT ci.claim_issue_type_id, 
                 ci.claim_issue_name, 
                 cia.claim_issue_area_id, 
                 cia.claim_issue_type_id AS cia_type_id, 
                 cia.claim_issue_area
          FROM " . DB_PREFIX . "claim_issue ci
          LEFT JOIN " . DB_PREFIX . "claim_issue_area cia
              ON ci.claim_issue_type_id = cia.claim_issue_type_id
      ");

    return $query->rows;  // Return all the rows from the query result
  }

    public function createClaim($data)
    {
     // Step 1: Get customer_id from oc_return
$return_query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "return WHERE return_id = '" . (int)$data['return_id'] . "'");
$customer_id = isset($return_query->row['customer_id']) ? (int)$return_query->row['customer_id'] : 0;

// Step 2: Insert into return_claim
$this->db->query("INSERT INTO " . DB_PREFIX . "return_claim 
    SET vendor_id = '" . (int)$data['vendor_id'] . "',
        return_id = '" . (int)$data['return_id'] . "',
        order_id = '" . (int)$data['order_id'] . "',
        product_id = '" . (int)$data['product_id'] . "',
        claim_amount = '" . (int)$data['claim_amount'] . "',
        model = '" . $data['model'] . "',
        customer_id = '" . $customer_id . "',
        claim_issue_type_id = '" . (int)$data['claim_issue_type_id'] . "',
        claim_issue_area_id = '" . (int)$data['claim_issue_area_id'] . "',
        name = '" . $this->db->escape($data['product']) . "',
        return_date = '" . $this->db->escape($data['return_date']) . "',
        claim_status_id = 40,
        date_added = NOW()");
        
      $claim_id = $this->db->getLastId();
    
      // Step 2: Insert shipping label into new claim_shipping_label table
      if (!empty($data['shipping_label'])) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "claim_label_image
            SET claim_id = '" . (int)$claim_id . "',
            image = '" . $this->db->escape($data['shipping_label']) . "'");
      }
    
      // Step 3: Insert claim images (excluding shipping label)
      if (!empty($data['images'])) {
        $images = explode(',', $data['images']);
        foreach ($images as $image) {
          if (!empty($image)) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "claim_images 
                      SET claim_id = '" . (int)$claim_id . "',
                          claim_image = '" . $this->db->escape($image) . "'");
          }
        }
      }
    // Step 4: Insert the description with claim_history_id
    $this->db->query("INSERT INTO " . DB_PREFIX . "claim_description 
        SET claim_id = '" . (int)$claim_id . "',
            description = '" . $this->db->escape($data['description']) . "',
            date_added = NOW(),
            date_modified = NOW()");
      // Step 4: Update vendor_order_product table
    //   $this->db->query("UPDATE " . DB_PREFIX . "vendor_order_product 
    //       SET order_status_id = 40 
    //       WHERE order_id = '" . (int)$data['order_id'] . "' 
    //         AND product_id = '" . (int)$data['product_id'] . "'");
    
      // Step 5: Update oc_order table
      $this->db->query("UPDATE " . DB_PREFIX . "order 
          SET order_status_id = 40 
          WHERE order_id = '" . (int)$data['order_id'] . "'");
    
      // Step 6: Update oc_return table
      $this->db->query("UPDATE " . DB_PREFIX . "return 
          SET order_status_id = 40 
          WHERE return_id = '" . (int)$data['return_id'] . "'");
    }
 
    public function getSellerClaimlist($vendor_id) 
{
    $query = $this->db->query("
        SELECT rc.*, os.name AS status_name
        FROM " . DB_PREFIX . "return_claim rc
        LEFT JOIN " . DB_PREFIX . "order_status os 
            ON rc.claim_status_id = os.order_status_id
        WHERE rc.vendor_id = '" . (int)$vendor_id . "'
    ");
    
    return $query->rows;
}


public function getAllClaims($vendor_id, $filter_status = '') {
   $sql = "
    SELECT 
        rc.*, 
        os.name AS status_name
    FROM " . DB_PREFIX . "return_claim rc
    LEFT JOIN " . DB_PREFIX . "order_status os 
        ON rc.claim_status_id = os.order_status_id
    WHERE rc.vendor_id = '" . (int)$vendor_id . "'
    ";
    
    // filters...
    if ($filter_status == 'approved') {
        $sql .= " AND rc.claim_status_id = 42";
    } elseif ($filter_status == 'not_approved') {
        $sql .= " AND rc.claim_status_id = 43";
    } elseif ($filter_status == 'claim_request') {
        $sql .= " AND rc.claim_status_id = 40";
    }
    // elseif ($filter_status == 'awaiting_response') {
    //     $sql .= " AND rc.claim_status_id = 41";
    // } elseif ($filter_status == 'closed') {
    //     $sql .= " AND rc.claim_status_id = 44";
    // }
    
    $sql .= " ORDER BY rc.claim_id DESC";
    
    $query = $this->db->query($sql);
    
    return $query->rows;

}

    public function getClaimCounts($vendor_id)
    {
        $status_ids = [
            'claim_request'      => 40,  // ✅ Processing
            'approved'           => 42,  // ✅ Approved
            'not_approved'       => 43   // ✅ Rejected
        ];

            // 'awaiting_response'  => 41,  // ✅ Waiting for Seller
            // 'closed'             => 44,  // ✅ Closed
        $data = [];

        foreach ($status_ids as $key => $status_id) {
            $query = $this->db->query("
            SELECT COUNT(*) as total 
            FROM " . DB_PREFIX . "return_claim 
            WHERE vendor_id = '" . (int)$vendor_id . "' 
            AND claim_status_id = '" . (int)$status_id . "'
        ");
            $data[$key] = $query->row['total'];
        }

        // ✅ Total count of all claims
        $query = $this->db->query("
        SELECT COUNT(*) as total 
        FROM " . DB_PREFIX . "return_claim 
        WHERE vendor_id = '" . (int)$vendor_id . "'
    ");
        $data['all'] = $query->row['total'];

        return $data;
    }

  
        public function getClaimProduct($claim_id) {
            $query = $this->db->query("
                SELECT 
                    rc.claim_id,
                    rc.return_id,
                    rc.product_id,
                    rc.order_id,
                    r.date_added AS return_date,
                    r.customer_id,
                    r.product AS product,
                    r.model,
                    r.quantity,
                    r.opened,
                    r.comment,
                    r.date_ordered,
                    rc.claim_amount AS product_total,
                    p.image AS product_image,
                    rc.claim_issue_type_id,
                    cit.claim_issue_name AS claim_issue_type_name,
                    rc.claim_issue_area_id,
                    cia.claim_issue_area,
                    cd.description  -- Added description field
                    FROM " . DB_PREFIX . "return_claim rc

                LEFT JOIN " . DB_PREFIX . "return r ON rc.return_id = r.return_id
                LEFT JOIN " . DB_PREFIX . "order_product rp ON rc.product_id = rp.order_product_id AND rc.order_id = rp.order_id
                LEFT JOIN " . DB_PREFIX . "product p ON rp.product_id = p.product_id

                LEFT JOIN " . DB_PREFIX . "claim_issue cit ON rc.claim_issue_type_id = cit.claim_issue_type_id
                LEFT JOIN " . DB_PREFIX . "claim_issue_area cia ON rc.claim_issue_area_id = cia.claim_issue_area_id
                LEFT JOIN " . DB_PREFIX . "claim_description cd ON rc.claim_id = cd.claim_id
                WHERE rc.claim_id = '" . (int)$claim_id . "'
                LIMIT 1
    ");

    $result = $query->row;

    // Fetch claim images if needed
    $image_query = $this->db->query("SELECT claim_image FROM " . DB_PREFIX . "claim_images WHERE claim_id = '" . (int)$claim_id . "' ORDER BY claim_image_id ASC");
    $result['claim_images'] = $image_query->num_rows ? array_column($image_query->rows, 'claim_image') : [];

    
    return $result;
}
    //   returns
    public function getReturnOrders($vendor_id, $data = array())
    {
        $sql = "SELECT o.order_id, 
           vop.name, 
           vop.quantity, 
           o.total, 
           o.date_modified, 
           r.return_id, 
           vop.product_id, 
           r.customer_id,  
           os.name AS status_name,  
           MIN(pi.image) AS image 
        FROM " . DB_PREFIX . "vendor_order_product vop 
        LEFT JOIN " . DB_PREFIX . "order o ON vop.order_id = o.order_id 
        LEFT JOIN " . DB_PREFIX . "product_image pi ON vop.product_id = pi.product_id 
        LEFT JOIN " . DB_PREFIX . "order_status os ON vop.order_status_id = os.order_status_id 
        LEFT JOIN " . DB_PREFIX . "return r ON r.order_id = vop.order_id
        WHERE vop.vendor_id = '" . (int)$vendor_id . "' 
        AND r.order_status_id = '30'  -- Filter to only include order status 30
        AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'";;
        if (!empty($data['filter_order_id'])) {
          $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
        }
    
        if (!empty($data['filter_product_name'])) {
          $sql .= " AND vop.name LIKE '%" . $this->db->escape($data['filter_product_name']) . "%'";
        }
    
        if (!empty($data['filter_date_added'])) {
          $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }
    
        $sql .= " GROUP BY o.order_id ORDER BY o.order_id DESC";
    
        $query = $this->db->query($sql);
        return $query->rows;
      }
//     public function getReturnProduct($return_id)
//     {
//     $query = $this->db->query("
//       SELECT r.return_id, r.order_id, r.product_id, r.product, r.customer_id, r.date_added AS return_date 
//       FROM " . DB_PREFIX . "return r
//       WHERE r.return_id = '" . (int)$return_id . "'
//   ");

//     return $query->row;
//   }
public function getReturnProduct($return_id)
{
    $query = $this->db->query("
        SELECT 
            r.return_id, 
            r.order_id, 
            r.product_id, 
            r.product, 
            r.customer_id, 
            r.date_added AS return_date,
            op.total AS product_total,
            p.image AS product_image,
            p.model
        FROM " . DB_PREFIX . "return r
        LEFT JOIN " . DB_PREFIX . "order_product op 
            ON r.order_id = op.order_id AND r.product_id = op.product_id
        LEFT JOIN " . DB_PREFIX . "product p 
            ON r.product_id = p.product_id
        WHERE r.return_id = '" . (int)$return_id . "'
    ");

    return $query->row;
}

// update claim

public function getClaimImages($claim_id) {
    $query = $this->db->query("SELECT claim_image FROM " . DB_PREFIX . "claim_images WHERE claim_id = '" . (int)$claim_id . "'");

    $images = [];
    foreach ($query->rows as $row) {
        $images[] = 'claim/' . $row['claim_image']; // assuming images are stored in image/claim/
    }

    return $images;
}
public function getClaimLabelImage($claim_id) {
    $query = $this->db->query("SELECT image FROM " . DB_PREFIX . "claim_label_image WHERE claim_id = '" . (int)$claim_id . "' LIMIT 1");
    return $query->row;
}

// public function getAllIssueAreas() {
//     $query = $this->db->query("SELECT claim_issue_area_id, claim_issue_area FROM " . DB_PREFIX . "claim_issue_area ORDER BY claim_issue_area ASC");
//     return $query->rows;
// }

public function getAllIssueAreas() {
    $query = $this->db->query("SELECT claim_issue_area_id, claim_issue_area FROM " . DB_PREFIX . "claim_issue_area ORDER BY claim_issue_area ASC");
    return $query->rows;
}

public function getAllIssueTypes() {
    $query = $this->db->query("SELECT claim_issue_type_id, claim_issue_name FROM " . DB_PREFIX . "claim_issue ORDER BY claim_issue_name ASC");
    return $query->rows;
}




public function updateClaim($data)
{
    $claim_id = (int)$data['claim_id'];

    // 1. Update main return_claim table
    $this->db->query("UPDATE " . DB_PREFIX . "return_claim 
        SET claim_issue_type_id = '" . (int)$data['claim_issue_type_id'] . "',
            claim_issue_area_id = '" . (int)$data['claim_issue_area_id'] . "',
            date_modified = NOW()
        WHERE claim_id = '" . $claim_id . "'");

    // 2. Update claim description
    if (!empty($data['description'])) {
        $description = $this->db->escape($data['description']);
        $query = $this->db->query("SELECT claim_id FROM " . DB_PREFIX . "claim_description WHERE claim_id = '" . $claim_id . "'");
        if ($query->num_rows) {
            $this->db->query("UPDATE " . DB_PREFIX . "claim_description
                SET description = '" . $description . "',
                    date_modified = NOW()
                WHERE claim_id = '" . $claim_id . "'");
        }
    }

    // 3. Update or insert shipping label image
    if (!empty($data['shipping_label'])) {
        $label = $this->db->escape($data['shipping_label']);
        $query = $this->db->query("SELECT id FROM " . DB_PREFIX . "claim_label_image WHERE claim_id = '" . $claim_id . "'");
        
        if ($query->num_rows) {
            $this->db->query("UPDATE " . DB_PREFIX . "claim_label_image 
                SET image = '" . $label . "',
                    date_modified = NOW()
                WHERE claim_id = '" . $claim_id . "'");
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "claim_label_image 
                SET claim_id = '" . $claim_id . "',
                    image = '" . $label . "',
                    date_added = NOW()");
        }
    }
// 4. Update claim images only if they changed
if (!empty($data['images']) && is_array($data['images'])) {
    $new_images = array_map('trim', array_filter($data['images']));

    // Get existing images with their IDs from DB
    $existing_result = $this->db->query("SELECT claim_image_id, claim_image FROM " . DB_PREFIX . "claim_images WHERE claim_id = '" . (int)$claim_id . "'");
    $existing_images = [];
    foreach ($existing_result->rows as $row) {
        $existing_images[] = [
            'claim_image_id' => (int)$row['claim_image_id'],
            'claim_image' => trim($row['claim_image'])
        ];
    }

    // Update existing images if changed
    foreach ($existing_images as $index => $existing) {
        if (isset($new_images[$index])) {
            $new_image = $new_images[$index];
            if ($new_image !== $existing['claim_image']) {
                $this->db->query("UPDATE " . DB_PREFIX . "claim_images 
                    SET claim_image = '" . $this->db->escape($new_image) . "' 
                    WHERE claim_image_id = '" . (int)$existing['claim_image_id'] . "' 
                      AND claim_id = '" . (int)$claim_id . "'");
            }
        }
    }

    // Insert any additional new images that exceed existing count
    for ($i = count($existing_images); $i < count($new_images); $i++) {
        $img = $new_images[$i];
        if (!empty($img)) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "claim_images 
                SET claim_id = '" . (int)$claim_id . "',
                    language_id = '" . (int)$this->config->get('config_language_id') . "',
                    claim_image = '" . $this->db->escape($img) . "',
                    date_added = NOW()");
        }
    }
}
}

// update claim delete
}
