<?php
class ModelCatalogCategoryList extends Model {

    public function getChildCategoriesWithProduct($parent_id) {
        $categories = [];

        // Step 1: Get all child categories of the given parent
        $query = $this->db->query("
            SELECT * FROM oc_all_category 
            WHERE parent_id = '" . (int)$parent_id . "'
        ");

        foreach ($query->rows as $category) {
            $category_id = (int)$category['category_id'];
            $level = (int)$category['level'];
                $columnLevels = [
        'category_level_1',
        'category_level_2',
        'category_level_3',
        'category_level_4',
        'category_level_5'
    ];

    $column_name = $columnLevels[$level] ?? 0;

    // if ($column_name) {
        // Step 2: Get ANY one product in this category (latest)
        $product = $this->db->query("
            SELECT p.product_id, p.price, p.image
            FROM " . DB_PREFIX . "vendor_product_category vpc
            LEFT JOIN " . DB_PREFIX . "product p ON (vpc.product_id = p.product_id)
            WHERE vpc." . $column_name . " = '" . $category_id . "' AND p.status = '1'
            ORDER BY p.price ASC
            LIMIT 1
        ");

            if ($product->num_rows > 0) {
                $product_id = (int)$product->row['product_id'];
                $product_image = $product->row['image']; // main image

                // Step 3: Fallback to product_image if main image is missing
                if (empty($product_image)) {
                    $image_query = $this->db->query("
                        SELECT image FROM " . DB_PREFIX . "product_image 
                        WHERE product_id = '" . $product_id . "' 
                        ORDER BY sort_order ASC LIMIT 1
                    ");

                    if ($image_query->num_rows) {
                        $product_image = $image_query->row['image'];
                    }
                }

                // Step 4: Append product info to category
                $category['product'] = [
                    'product_id' => $product_id,
                    'price'      => $product->row['price'],
                    'image'      => $product_image
                ];
            } else {
                $category['product'] = [];
            }

            $categories[] = $category;
        }

        return $categories;
    }
}
