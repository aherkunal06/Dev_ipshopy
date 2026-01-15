<?php
class ModelVendorFaq extends Model {
    public function getVendorProductFaqs($vendor_id, $start = 0, $limit = 20, $status = null) {
        if ($start < 0) $start = 0;

        $sql = "SELECT 
                  pf.product_faq_id, 
                  pf.product_id, 
                  pf.question, 
                  pf.answer, 
                  pf.date_added,
                  CONCAT(c.firstname, ' ', c.lastname) AS customer_name,
                  pd.name AS product_name
                FROM " . DB_PREFIX . "product_faq pf
                INNER JOIN " . DB_PREFIX . "vendor_to_product vtp ON pf.product_id = vtp.product_id
                LEFT JOIN " . DB_PREFIX . "product_description pd ON pf.product_id = pd.product_id AND pd.language_id = 1
                LEFT JOIN " . DB_PREFIX . "customer c ON pf.customer_id = c.customer_id
                WHERE vtp.vendor_id = '" . (int)$vendor_id . "'";

        // Filter by status if provided (answered/unanswered)
        if ($status !== null) {
            $sql .= " AND pf.status = " . (int)$status;
        }

        $sql .= " ORDER BY pf.product_faq_id DESC
                  LIMIT " . (int)$start . "," . (int)$limit;

        return $this->db->query($sql)->rows;
    }

    public function getTotalVendorProductFaqs($vendor_id, $status = null) {
        $sql = "SELECT COUNT(*) AS total 
                FROM " . DB_PREFIX . "product_faq pf
                INNER JOIN " . DB_PREFIX . "vendor_to_product vtp ON pf.product_id = vtp.product_id
                WHERE vtp.vendor_id = '" . (int)$vendor_id . "'";

        // Filter by status if provided
        if ($status !== null) {
            $sql .= " AND pf.status = " . (int)$status;
        }

        $query = $this->db->query($sql);
        return $query->row['total'];
    }

    public function updateAnswerForVendor($faq_id, $vendor_id, $product_id, $answer) {
        $exists = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_to_product 
            WHERE vendor_id = '" . (int)$vendor_id . "' AND product_id = '" . (int)$product_id . "'");

        if ($exists->num_rows) {
            $this->db->query("UPDATE " . DB_PREFIX . "product_faq 
                SET answer = '" . $this->db->escape($answer) . "' 
                WHERE product_faq_id = '" . (int)$faq_id . "'");
        }
    }
}
