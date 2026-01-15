<?php
class ModelCatalogBreadcrumbpath extends Model {
    public function getBreadcrumbHierarchy($category_id) {
        $language_id = (int)$this->config->get('config_language_id');

        $sql = "
            SELECT 
                c1.category_id AS level1_id, cd1.name AS level1_name,
                c2.category_id AS level2_id, cd2.name AS level2_name,
                c3.category_id AS level3_id, cd3.name AS level3_name,
                c4.category_id AS level4_id, cd4.name AS level4_name,
                c5.category_id AS level5_id, cd5.name AS level5_name
            FROM " . DB_PREFIX . "category c1
            LEFT JOIN " . DB_PREFIX . "category_description cd1 
                ON cd1.category_id = c1.category_id AND cd1.language_id = '" . $language_id . "'
            LEFT JOIN " . DB_PREFIX . "category c2 
                ON c2.category_id = c1.parent_id
            LEFT JOIN " . DB_PREFIX . "category_description cd2 
                ON cd2.category_id = c2.category_id AND cd2.language_id = '" . $language_id . "'
            LEFT JOIN " . DB_PREFIX . "category c3 
                ON c3.category_id = c2.parent_id
            LEFT JOIN " . DB_PREFIX . "category_description cd3 
                ON cd3.category_id = c3.category_id AND cd3.language_id = '" . $language_id . "'
            LEFT JOIN " . DB_PREFIX . "category c4 
                ON c4.category_id = c3.parent_id
            LEFT JOIN " . DB_PREFIX . "category_description cd4 
                ON cd4.category_id = c4.category_id AND cd4.language_id = '" . $language_id . "'
            LEFT JOIN " . DB_PREFIX . "category c5 
                ON c5.category_id = c4.parent_id
            LEFT JOIN " . DB_PREFIX . "category_description cd5 
                ON cd5.category_id = c5.category_id AND cd5.language_id = '" . $language_id . "'
            WHERE c1.category_id = '" . (int)$category_id . "'
        ";

        $query = $this->db->query($sql);

        return $query->num_rows ? $query->row : [];
    }
}
