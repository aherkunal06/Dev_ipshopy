<?php
class ModelCatalogIndependenceDay extends Model {
    
    public function getActiveDiscountedSubcategories($parent_id) {
        $sql = "SELECT DISTINCT c.category_id, cd.name, c.image
                FROM " . DB_PREFIX . "category c
                INNER JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
                INNER JOIN " . DB_PREFIX . "product_to_category p2c ON (p2c.category_id = c.category_id)
                INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = p2c.product_id)
                INNER JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id)
                WHERE c.parent_id = '" . (int)$parent_id . "'
                  AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                  AND c.status = '1'
                  AND p.status = '1'
                  AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
                  AND ps.date_start <= NOW()
                  AND (ps.date_end = '0000-00-00' OR ps.date_end >= NOW())
                  AND ((p.price - ps.price) / p.price * 100) >= 40
                ORDER BY c.sort_order, LCASE(cd.name)";
        return $this->db->query($sql)->rows;
    }

    public function getCategoryName($category_id) {
        $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "category_description 
            WHERE category_id = '" . (int)$category_id . "' 
            AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
        return $query->row ? $query->row['name'] : '';
    }


public function getLowestDiscountedProductWithPrice($category_id, $min_discount = 40, $max_discount = 100) {
    $sql = "SELECT p.price AS original_price, ps.price AS special_price
            FROM " . DB_PREFIX . "product p
            LEFT JOIN " . DB_PREFIX . "product_to_category pc ON (p.product_id = pc.product_id)
            LEFT JOIN " . DB_PREFIX . "product_special ps ON (
                p.product_id = ps.product_id
                AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
                AND (ps.date_start = '0000-00-00' OR ps.date_start < NOW())
                AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())
            )
            WHERE pc.category_id = '" . (int)$category_id . "'
              AND p.status = 1
              AND p.date_available <= NOW()
              AND ps.price > 0
              AND ((p.price - ps.price) / p.price * 100) BETWEEN " . (int)$min_discount . " AND " . (int)$max_discount . "
            ORDER BY ps.price ASC
            LIMIT 1";

    $query = $this->db->query($sql);

    if ($query->num_rows) {
        return $query->row; 
    } else {
        return null;
    }
}


public function getCategoryListByParent($parent_id, $min_discount = 40) {
    $sql = "SELECT DISTINCT cd.category_id, cd.name
            FROM " . DB_PREFIX . "category_description cd
            LEFT JOIN " . DB_PREFIX . "category c ON (cd.category_id = c.category_id)
            LEFT JOIN " . DB_PREFIX . "product_to_category pc ON (c.category_id = pc.category_id)
            LEFT JOIN " . DB_PREFIX . "product p ON (pc.product_id = p.product_id)
            LEFT JOIN " . DB_PREFIX . "product_special ps ON (
                p.product_id = ps.product_id
                AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
                AND (ps.date_start = '0000-00-00' OR ps.date_start < NOW())
                AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())
            )
            WHERE c.parent_id = '" . (int)$parent_id . "'
              AND c.status = 1
              AND p.status = 1
              AND p.date_available <= NOW()
              AND ps.price > 0
              AND ((p.price - ps.price) / p.price * 100) >= " . (int)$min_discount . "
            ORDER BY cd.name ASC";

    $query = $this->db->query($sql);
    return $query->rows;
}



}