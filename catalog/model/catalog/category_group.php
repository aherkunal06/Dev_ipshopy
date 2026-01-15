<?php
class ModelCatalogCategoryGroup extends Model {
    public function getChildCategoriesWithProduct($parent_id) {
        $categories = [];

       
        $query = $this->db->query("
            SELECT c.category_id, cd.name, c.image, c.parent_id
            FROM " . DB_PREFIX . "category c
            LEFT JOIN " . DB_PREFIX . "category_description cd 
                ON (c.category_id = cd.category_id)
            WHERE c.parent_id = '" . (int)$parent_id . "' 
              AND c.status = '1' 
              AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
              AND EXISTS (
                  SELECT 1 
                  FROM " . DB_PREFIX . "product_to_category p2c
                  LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)
                  WHERE p2c.category_id = c.category_id 
                    AND p.status = '1'
                    AND p.price > 0
              )
            ORDER BY c.sort_order ASC
            LIMIT 4
        ");

        foreach ($query->rows as $category) {
            $category_id = (int)$category['category_id'];

            // cheapest product
            $product_query = $this->db->query("
                SELECT p.product_id, p.price, p.image
                FROM " . DB_PREFIX . "product_to_category p2c
                LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)
                WHERE p2c.category_id = '" . $category_id . "' 
                  AND p.status = '1' 
                  AND p.price > 0
                ORDER BY p.price ASC, p.product_id ASC
                LIMIT 1
            ");

            $product = [];
            if ($product_query->num_rows) {
                $product = [
                    'product_id' => $product_query->row['product_id'],
                    'price'      => $product_query->row['price'],
                    'image'      => $product_query->row['image']
                ];
            }

            $categories[] = [
                'category_id' => $category_id,
                'name'        => $category['name'],
                'image'       => $category['image'],
                'parent_id'   => $category['parent_id'],
                'product'     => $product
            ];
        }

        return $categories;
    }
}
