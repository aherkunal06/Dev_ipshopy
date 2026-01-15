<?php
class ModelTrackingProductApprovalList extends Model {
   
    // public function getProductGroupedByApprovedByAndDate(): array {
    //     $sql = "
    //         SELECT
    //             p.approved_by,
    //             DATE(p.approved_date) AS approved_date,
    //             COUNT(*) AS total
    //         FROM `" . DB_PREFIX . "product` p
    //         WHERE p.approved_by <> ''
    //         GROUP BY p.approved_by, DATE(p.approved_date)
    //         ORDER BY DATE(p.approved_date) DESC, p.approved_by ASC
    //     ";

    //     $query = $this->db->query($sql);

    //     return $query->rows;
    // }

    // public function getProductsByApprovedByAndDate(string $approved_by, string $approved_date): array {
    //     $language_id = (int)$this->config->get('config_language_id');

    //     $sql = "
    //         SELECT
    //             p.product_id,
    //             pd.name,
    //             p.model,
    //             p.price,
    //             p.quantity,
    //             p.status,
    //             p.image,
    //             p.added_by,
    //             p.edited_by,
    //             p.approved_by,
    //             p.approved_date
                

    //         FROM `" . DB_PREFIX . "product` p
    //         LEFT JOIN `" . DB_PREFIX . "product_description` pd
    //           ON p.product_id = pd.product_id
    //          AND pd.language_id = {$language_id}
    //         WHERE p.approved_by = '" . $this->db->escape($approved_by) . "'
    //           AND DATE(p.approved_date) = '" . $this->db->escape($approved_date) . "'
    //         ORDER BY p.approved_date DESC, pd.name ASC
    //     ";

    //     $query = $this->db->query($sql);

    //     return $query->rows;
    // }
    
    public function getProductGroupedByApprovedByAndDate(): array {
        $sql = "
            SELECT
                p.approved_by,
                DATE(p.approved_date) AS approved_date,
                COUNT(*) AS total
            FROM " . DB_PREFIX . "product p
            WHERE p.approved_by <> ''
            GROUP BY p.approved_by, DATE(p.approved_date)
            ORDER BY DATE(p.approved_date) DESC, p.approved_by ASC
        ";

        $query = $this->db->query($sql);

        return $query->rows;
    }


    public function getProductsByApprovedByAndDate(string $approved_by, string $approved_date): array {
        $language_id = (int)$this->config->get('config_language_id');

        $sql = "
            SELECT
                p.product_id,
                pd.name,
                p.model,
                p.price,
                p.quantity,
                p.status,
                p.image,
                p.added_by,
                p.edited_by,
                p.approved_by,
                p.approved_date
                

            FROM " . DB_PREFIX . "product p
            LEFT JOIN " . DB_PREFIX . "product_description pd
              ON p.product_id = pd.product_id
             AND pd.language_id = {$language_id}
            WHERE p.approved_by = '" . $this->db->escape($approved_by) . "'
              AND DATE(p.approved_date) = '" . $this->db->escape($approved_date) . "'
            ORDER BY p.approved_date DESC, pd.name ASC
        ";

        $query = $this->db->query($sql);

        return $query->rows;
    }
}
