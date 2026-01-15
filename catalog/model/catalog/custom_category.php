<?php
class ModelCatalogCustomCategory extends Model {
    public function getCategoriesByIds($category_ids) {
        $ids = implode(',', array_map('intval', $category_ids));
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "category c
            LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
            WHERE c.category_id IN ($ids) 
              AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY c.sort_order ASC
        ");
        return $query->rows;
    }

    public function getSubcategories($parent_id) {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "category c
            LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
            WHERE c.parent_id = '" . (int)$parent_id . "' 
              AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY c.sort_order ASC
        ");
        return $query->rows;
    }
}
?>
