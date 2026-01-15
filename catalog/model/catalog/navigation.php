<?php
class ModelCatalogNavigation extends Model {

   
    private function hasActiveProducts($category_id) {
        $sql = "SELECT COUNT(*) AS total 
                FROM " . DB_PREFIX . "product_to_category p2c
                LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)
                WHERE p2c.category_id = '" . (int)$category_id . "'
                AND p.status = '1'";

        $query = $this->db->query($sql);

        return ($query->row['total'] > 0);
    }

  
    public function getCategories($parent_id = 0) {
        $sql = "SELECT c.category_id, cd.name 
                FROM " . DB_PREFIX . "category c
                LEFT JOIN " . DB_PREFIX . "category_description cd 
                       ON (c.category_id = cd.category_id)
                WHERE c.parent_id = '" . (int)$parent_id . "' 
                AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                AND c.status = '1'
                ORDER BY c.sort_order, LCASE(cd.name)";

        $query = $this->db->query($sql);

        $categories = [];

        foreach ($query->rows as $row) {
           
            $children = $this->getCategories($row['category_id']);

     
            if ($this->hasActiveProducts($row['category_id']) || !empty($children)) {
                $categories[] = [
                    'category_id' => $row['category_id'],
                    'name'        => $row['name'],
                    'href'        => $this->url->link('product/category', 'path=' . $row['category_id']),
                    'children'    => $children
                ];
            }
        }

        return $categories;
    }
}
