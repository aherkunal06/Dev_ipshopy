<?php
class ModelCatalogRecentView extends Model {

public function getRecentProductsByCategory($product_ids = [], $limit = 10) {
    if (empty($product_ids)) {
        return [];
    }

    $product_ids = array_map('intval', $product_ids);
    $product_ids_str = implode(",", $product_ids);

    $sql = "SELECT p.product_id, pd.name, p.image, p.price,
                   -- special price
                   (SELECT price FROM " . DB_PREFIX . "product_special ps 
                    WHERE ps.product_id = p.product_id 
                      AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' 
                      AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) 
                      AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) 
                    ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special,
                   -- average rating
                   (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 
                    WHERE r1.product_id = p.product_id 
                      AND r1.status = '1' 
                    GROUP BY r1.product_id) AS rating
            FROM " . DB_PREFIX . "product p
            LEFT JOIN " . DB_PREFIX . "product_description pd 
                   ON (p.product_id = pd.product_id)
            WHERE p.product_id IN (" . $product_ids_str . ")
              AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
              AND p.status = '1'
              AND p.date_available <= NOW()
            GROUP BY p.product_id
            ORDER BY p.date_added DESC
            LIMIT " . (int)$limit;

    $query = $this->db->query($sql);

    return $query->rows;
}



public function getProductsByCategoryId($category_id, $limit = 10) {
    $sql = "SELECT p.product_id, pd.name, p.image, p.price, 
                   (SELECT price FROM " . DB_PREFIX . "product_special ps 
                    WHERE ps.product_id = p.product_id 
                      AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' 
                      AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) 
                      AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) 
                    ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special,
                   (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 
                    WHERE r1.product_id = p.product_id 
                      AND r1.status = '1' 
                    GROUP BY r1.product_id) AS rating
            FROM " . DB_PREFIX . "product p
            LEFT JOIN " . DB_PREFIX . "product_description pd 
                   ON (p.product_id = pd.product_id)
            LEFT JOIN " . DB_PREFIX . "product_to_category p2c 
                   ON (p.product_id = p2c.product_id)
            WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
              AND p.status = '1' 
              AND p.date_available <= NOW()
              AND p2c.category_id = '" . (int)$category_id . "'
            GROUP BY p.product_id
            ORDER BY p.date_added DESC
            LIMIT " . (int)$limit;

    $query = $this->db->query($sql);

    return $query->rows;
}


 public function getCategoryInfoByRecent($product_ids = []) {
        if (empty($product_ids)) {
            return [];
        }

        $product_ids_str = implode(',', array_map('intval', $product_ids));

    
        $sql = "SELECT c.category_id, cd.name AS category_name, c.image AS category_image, 
                       MIN(p.price) AS lowest_price
                FROM " . DB_PREFIX . "product_to_category pc
                INNER JOIN " . DB_PREFIX . "category c ON (pc.category_id = c.category_id)
                INNER JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
                INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = pc.product_id)
                WHERE pc.product_id IN ($product_ids_str)
                GROUP BY c.category_id";

        $query = $this->db->query($sql);

        return $query->rows;
    }

}