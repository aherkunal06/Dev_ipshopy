<?php
class ModelCatalogAllCategories extends Model {

    public function getCategoryParentId($category_id) {
        $query = $this->db->query("SELECT parent_id FROM " . DB_PREFIX . "category 
                                   WHERE category_id = '" . (int)$category_id . "'");
        return $query->num_rows ? (int)$query->row['parent_id'] : 0;
    }

    public function getCategoryName($category_id) {
        $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "category_description 
                                   WHERE category_id = '" . (int)$category_id . "' 
                                   AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
        return $query->num_rows ? $query->row['name'] : '';
    }

    public function getSubcategories($category_id) {
        $query = $this->db->query("SELECT c.category_id, cd.name, c.image 
                                   FROM " . DB_PREFIX . "category c 
                                   LEFT JOIN " . DB_PREFIX . "category_description cd 
                                   ON (c.category_id = cd.category_id) 
                                   WHERE c.parent_id = '" . (int)$category_id . "' 
                                   AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                                   ORDER BY c.sort_order ASC");
        return $query->rows;
    }

    public function getProductCountByCategory($category_id) {
        $query = $this->db->query("SELECT COUNT(DISTINCT p.product_id) AS total 
                                   FROM " . DB_PREFIX . "product_to_category p2c 
                                   LEFT JOIN " . DB_PREFIX . "product p 
                                   ON (p.product_id = p2c.product_id) 
                                   WHERE p2c.category_id = '" . (int)$category_id . "' 
                                   AND p.status = '1'");
        return (int)$query->row['total'];
    }
}
