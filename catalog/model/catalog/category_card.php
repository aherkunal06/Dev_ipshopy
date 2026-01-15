<?php
class ModelCatalogCategoryCard extends Model {
    public function getCategoryCardProducts($category_id,$level, $limit = 4) {
        $this->load->model('tool/image');
        
         $columnLevels = [
        'category_level_1',
        'category_level_2',
        'category_level_3',
        'category_level_4',
        'category_level_5'
    ];
  $column_name = $columnLevels[$level] ?? 0;

      $sql = "SELECT p.product_id, pd.name, p.image 
        FROM " . DB_PREFIX . "product p
        LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
        LEFT JOIN " . DB_PREFIX . "vendor_product_category vpc ON (p.product_id = vpc.product_id)
        WHERE p.status = '1' 
          AND p.date_available <= NOW() 
          AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
          AND vpc." . $this->db->escape($column_name) . " = '" . (int)$category_id . "'
        ORDER BY p.sort_order ASC, LCASE(pd.name) ASC
        LIMIT " . (int)$limit;


        $query = $this->db->query($sql);
        $result = $query->rows;

        if (!$result) return [];

        $main = array_shift($result); // First = main

        $main_product = [
            'product_id' => $main['product_id'],
            'name'       => $main['name'],
            'image'      => $main['image'] ? $this->model_tool_image->resize($main['image'], 300, 300) : '',
            'href'       => $this->url->link('product/product', 'product_id=' . $main['product_id']),
        ];

        $related_products = [];
        foreach ($result as $product) {
            $related_products[] = [
                'product_id' => $product['product_id'],
                'name'       => $product['name'],
                'image'      => $product['image'] ? $this->model_tool_image->resize($product['image'], 100, 100) : '',
                'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id']),
            ];
        }

        return [
            'main_product' => $main_product,
            'related'      => $related_products
        ];
    }
}
