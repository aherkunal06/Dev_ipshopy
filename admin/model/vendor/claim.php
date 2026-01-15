<?php
class ModelVendorClaim extends Model {
    public function getClaimIssuesWithAreas() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "claim_issue");

        $results = array();

        foreach ($query->rows as $issue) {
            $area_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "claim_issue_area WHERE claim_issue_type_id = '" . (int)$issue['claim_issue_type_id'] . "'");

            $results[] = array(
                'claim_issue_name' => $issue['claim_issue_name'],
                'areas' => $area_query->rows
            );
        }

        return $results;
    }

   
   public function getAllReturnClaims($filter_status = '', $vendor_id = 0)
{
$sql = "
    SELECT 
        rc.*,
        os.name AS status,
        CONCAT(v.firstname, ' ', v.lastname) AS vendor_name,
        p.image AS product_image
    FROM " . DB_PREFIX . "return_claim rc
    LEFT JOIN " . DB_PREFIX . "order_status os 
        ON rc.claim_status_id = os.order_status_id
    LEFT JOIN " . DB_PREFIX . "vendor_order_product vop
        ON vop.order_id = rc.order_id
    LEFT JOIN " . DB_PREFIX . "vendor v
        ON v.vendor_id = vop.vendor_id
    LEFT JOIN " . DB_PREFIX . "product p
        ON p.product_id = rc.product_id
    WHERE 1
";



    // Filter by vendor ID if provided
    if ((int)$vendor_id > 0) {
        $sql .= " AND rc.vendor_id = '" . (int)$vendor_id . "'";
    }

    // Apply status filter
    if (!empty($filter_status)) {
        $status_map = [
            'claim_request' => 40,
            'approved' => 42,
            'not_approved' => 43
        ];
        if (isset($status_map[$filter_status])) {
            $sql .= " AND rc.claim_status_id = '" . (int)$status_map[$filter_status] . "'";
        }
    }

    $sql .= " ORDER BY rc.return_id DESC";

    $query = $this->db->query($sql);
    return $query->rows;
}






public function getClaimCounts($vendor_id = 0) {
    $status_ids = [
        'claim_request'      => 40,
        'approved'           => 42,
        'not_approved'       => 43
    ];

    $data = [];

    foreach ($status_ids as $key => $status_id) {
        $sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "return_claim WHERE claim_status_id = '" . (int)$status_id . "'";
        if ((int)$vendor_id > 0) {
            $sql .= " AND vendor_id = '" . (int)$vendor_id . "'";
        }

        $query = $this->db->query($sql);
        $data[$key] = $query->row['total'];
    }

    $sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "return_claim";
    if ((int)$vendor_id > 0) {
        $sql .= " WHERE vendor_id = '" . (int)$vendor_id . "'";
    }

    $query = $this->db->query($sql);
    $data['all'] = $query->row['total'];

    return $data;
}

}
