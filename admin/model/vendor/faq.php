<?php
class ModelVendorFaq extends Model {
public function getAllProductFaqs($status = -1, $start = 0, $limit = 20, $answer_filter = '') {
    $sql = "SELECT pf.product_faq_id, pf.product_id, pf.question, pf.answer, pf.date_added, pf.status,
                   pd.name AS product_name,
                   CONCAT(c.firstname, ' ', c.lastname) AS customer_name,
                   vtp.vendor_id,
                   CONCAT(o.firstname, ' ', o.lastname) AS seller_name
            FROM " . DB_PREFIX . "product_faq pf
            INNER JOIN " . DB_PREFIX . "vendor_to_product vtp ON pf.product_id = vtp.product_id
            LEFT JOIN " . DB_PREFIX . "product_description pd ON pf.product_id = pd.product_id AND pd.language_id = 1
            LEFT JOIN " . DB_PREFIX . "customer c ON pf.customer_id = c.customer_id
            LEFT JOIN " . DB_PREFIX . "vendor o ON vtp.vendor_id = o.vendor_id
            WHERE 1";

    if ($status !== -1) {
        $sql .= " AND pf.status = '" . (int)$status . "'";
    }

    if ($answer_filter == 'answered') {
        $sql .= " AND pf.answer IS NOT NULL AND pf.answer != ''";
    } elseif ($answer_filter == 'unanswered') {
        $sql .= " AND (pf.answer IS NULL OR pf.answer = '')";
    }

    $sql .= " ORDER BY pf.product_faq_id DESC";
    $sql .= " LIMIT " . (int)$start . ", " . (int)$limit;

    return $this->db->query($sql)->rows;
}

public function countFaqsByStatus($status = -1, $answer_filter = '') {
    $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_faq WHERE 1";

    if ($status !== -1) {
        $sql .= " AND status = '" . (int)$status . "'";
    }

    if ($answer_filter == 'answered') {
        $sql .= " AND answer IS NOT NULL AND answer != ''";
    } elseif ($answer_filter == 'unanswered') {
        $sql .= " AND (answer IS NULL OR answer = '')";
    }

    $result = $this->db->query($sql);
    return $result->row['total'];
}





    public function saveAdminAnswer($faq_id, $answer) {
        $this->db->query("UPDATE " . DB_PREFIX . "product_faq 
                          SET answer = '" . $this->db->escape($answer) . "' 
                          WHERE product_faq_id = '" . (int)$faq_id . "'");
    }

    public function updateFaqStatus($faq_id, $status) {
        $this->db->query("UPDATE " . DB_PREFIX . "product_faq 
                          SET status = '" . (int)$status . "' 
                          WHERE product_faq_id = '" . (int)$faq_id . "'");
    }

    // public function countFaqsByStatus($status) {
    //     $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_faq";

    //     if ($status !== -1) {
    //         $sql .= " WHERE status = '" . (int)$status . "'";
    //     }

    //     $result = $this->db->query($sql);
    //     return $result->row['total'];
    // }
}
