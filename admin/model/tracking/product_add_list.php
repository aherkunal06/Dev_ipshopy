<?php
class ModelTrackingProductAddList extends Model {

    // public function getProductsByAddedByAndDate($added_by, $date_added) {
    //     $language_id = 1; // You can replace this with dynamic language_id if needed

    //     $sql = "SELECT 
    //                 p.product_id,
    //                 p.model,
    //                 p.price,
    //                 p.status,
    //                 p.quantity,
    //                 p.added_by,
    //                 p.edited_by,
    //                 p.date_added,
    //                 pd.name AS product_name,
    //                 (
    //                     SELECT image 
    //                     FROM " . DB_PREFIX . "product_image 
    //                     WHERE product_id = p.product_id 
    //                     ORDER BY sort_order ASC, product_image_id ASC 
    //                     LIMIT 1
    //                 ) AS product_image
    //             FROM " . DB_PREFIX . "product p
    //             LEFT JOIN " . DB_PREFIX . "product_description pd 
    //                 ON p.product_id = pd.product_id AND pd.language_id = '" . (int)$language_id . "'
    //             WHERE p.added_by = '" . $this->db->escape($added_by) . "'
    //             AND DATE(p.date_added) = '" . $this->db->escape($date_added) . "'
    //             ORDER BY p.date_added DESC";

    //     $query = $this->db->query($sql);
    //     return $query->rows;
    // }
    
    public function getProductsByAddedByAndDate($added_by, $date_added) {
        $language_id = 1; // You can replace this with dynamic language_id if needed

        $sql = "SELECT 
                    p.product_id,
                    p.model,
                    p.price,
                    p.status,
                    p.quantity,
                    p.added_by,
                    p.edited_by,
                    p.date_added,
                    pd.name AS product_name,
                    (
                        SELECT image 
                        FROM " . DB_PREFIX . "product_image 
                        WHERE product_id = p.product_id 
                        ORDER BY sort_order ASC, product_image_id ASC 
                        LIMIT 1
                    ) AS product_image
                FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_description pd 
                    ON p.product_id = pd.product_id AND pd.language_id = '" . (int)$language_id . "'
                WHERE p.added_by = '" . $this->db->escape($added_by) . "'
                AND DATE(p.date_added) = '" . $this->db->escape($date_added) . "'
                ORDER BY p.date_added DESC";

        $query = $this->db->query($sql);
        return $query->rows;
    }


    // for filter
    public function getFilteredProducts($data = []) {
        $language_id = 1; // or dynamic language id if needed

        $sql = "SELECT 
                    p.product_id,
                    p.model,
                    p.price,
                    p.status,
                    p.quantity,
                    p.added_by,
                    p.edited_by,
                    p.date_added,
                    pd.name AS product_name,
                    (
                        SELECT image 
                        FROM " . DB_PREFIX . "product_image 
                        WHERE product_id = p.product_id 
                        ORDER BY sort_order ASC, product_image_id ASC 
                        LIMIT 1
                    ) AS product_image
                FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_description pd 
                    ON p.product_id = pd.product_id AND pd.language_id = '" . (int)$language_id . "'
                WHERE 1 ";

        // Mandatory filters
        if (!empty($data['added_by'])) {
            $sql .= " AND p.added_by = '" . $this->db->escape($data['added_by']) . "' ";
        }
        if (!empty($data['date_added'])) {
            $sql .= " AND DATE(p.date_added) = '" . $this->db->escape($data['date_added']) . "' ";
        }

        // Optional filters
        if (!empty($data['filter_name'])) {
            $sql .= " AND pd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%' ";
        }
        if (!empty($data['filter_model'])) {
            $sql .= " AND p.model LIKE '%" . $this->db->escape($data['filter_model']) . "%' ";
        }
        if ($data['filter_price'] !== '') {
            $sql .= " AND p.price = '" . (float)$data['filter_price'] . "' ";
        }
        if ($data['filter_quantity'] !== '') {
            $sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "' ";
        }
        if ($data['filter_status'] !== '') {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "' ";
        }
        if (!empty($data['filter_added_by'])) {
            $sql .= " AND p.added_by LIKE '%" . $this->db->escape($data['filter_added_by']) . "%' ";
        }
        if (!empty($data['filter_edited_by'])) {
            $sql .= " AND p.edited_by LIKE '%" . $this->db->escape($data['filter_edited_by']) . "%' ";
        }

        $sql .= " ORDER BY p.date_added DESC";

        $query = $this->db->query($sql);

        return $query->rows;
    }
    // ------------=

}
