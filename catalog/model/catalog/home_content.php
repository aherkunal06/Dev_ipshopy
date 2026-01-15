<?php 
class ModelCatalogHomeContent extends Model {

        
    public function getSubCategoriesWithPriceAndRating($category_id) {
        $sql = "SELECT 
                    c.category_id, 
                    cd.name AS subcategory_name, 
                    c.image, 
                    MIN(p.price) AS lowest_price,
                    MAX(r.rating) AS highest_rating,
                    pcd.name AS parent_name
                FROM " . DB_PREFIX . "category c
                INNER JOIN " . DB_PREFIX . "category_description cd 
                    ON c.category_id = cd.category_id 
                    AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                INNER JOIN " . DB_PREFIX . "product_to_category p2c 
                    ON c.category_id = p2c.category_id
                INNER JOIN " . DB_PREFIX . "product p 
                    ON p.product_id = p2c.product_id 
                    AND p.status = '1' 
                    AND p.date_available <= NOW()
                LEFT JOIN " . DB_PREFIX . "review r
                    ON p.product_id = r.product_id
                    AND r.status = '1'
                INNER JOIN " . DB_PREFIX . "category_description pcd
                    ON c.parent_id = pcd.category_id
                    AND pcd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                WHERE c.parent_id = '" . (int)$category_id . "'
                GROUP BY c.category_id, cd.name, c.image, pcd.name
                ORDER BY cd.name ASC";

        $query = $this->db->query($sql);

        return $query->rows;
    }


public function getAllSubCategoriesByParent($parent_ids, $start = 0, $limit = 4) {
    if (!is_array($parent_ids)) {
        $parent_ids = [$parent_ids];
    }

    $parent_ids = array_map('intval', $parent_ids);
    $parent_ids_str = implode(',', $parent_ids);

    $sql = "SELECT 
                c.category_id, 
                cd.name, 
                c.image,
                (
                    SELECT MIN(
                        CASE 
                            WHEN ps.price IS NOT NULL 
                                 AND (ps.date_start = '0000-00-00' OR ps.date_start < NOW())
                                 AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())
                            THEN ps.price
                            ELSE p.price
                        END
                    ) 
                    FROM " . DB_PREFIX . "product p
                    INNER JOIN " . DB_PREFIX . "product_to_category p2c2 
                        ON (p.product_id = p2c2.product_id)
                    LEFT JOIN " . DB_PREFIX . "product_special ps 
                        ON (p.product_id = ps.product_id 
                            AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "')
                    WHERE p2c2.category_id = c.category_id 
                      AND p.status = '1'
                ) AS lowest_price
            FROM " . DB_PREFIX . "category c
            INNER JOIN " . DB_PREFIX . "category_description cd 
                ON (c.category_id = cd.category_id)
           
            INNER JOIN " . DB_PREFIX . "product_to_category p2c3 
                ON (c.category_id = p2c3.category_id)
            INNER JOIN " . DB_PREFIX . "product p3 
                ON (p2c3.product_id = p3.product_id AND p3.status = '1')
            WHERE c.parent_id IN (" . $parent_ids_str . ")
              AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
              AND c.status = '1'
            GROUP BY c.category_id
            HAVING lowest_price IS NOT NULL   
            ORDER BY c.sort_order ASC 
            LIMIT " . (int)$start . "," . (int)$limit;

    return $this->db->query($sql)->rows;
}




public function getProductsByCategory($category_id, $start = 0, $limit = 4, $order = 'ASC', $sort = 'price') {
    $sort_data = ['name', 'price', 'rating', 'sort_order'];
    if (!in_array($sort, $sort_data)) {
        $sort = 'price';
    }

    
    $child_categories = [];
    $query = $this->db->query("SELECT category_id 
                               FROM " . DB_PREFIX . "category_path 
                               WHERE path_id = '" . (int)$category_id . "' 
                                 AND category_id != '" . (int)$category_id . "'");
    foreach ($query->rows as $row) {
        $child_categories[] = (int)$row['category_id'];
    }

    $products = [];

    
    foreach ($child_categories as $child_id) {
        $sql = "SELECT 
                    p.product_id, 
                    p.image, 
                    pd.name, 
                    p.price,
                    (SELECT ps.price 
                     FROM " . DB_PREFIX . "product_special ps 
                     WHERE ps.product_id = p.product_id 
                       AND ps.customer_group_id = '1' 
                       AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) 
                       AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))
                     ORDER BY ps.priority ASC, ps.price ASC 
                     LIMIT 1) AS special,
                    IFNULL(MAX(r.rating),0) as rating
                FROM " . DB_PREFIX . "product p
                INNER JOIN " . DB_PREFIX . "product_to_category p2c ON p.product_id = p2c.product_id
                INNER JOIN " . DB_PREFIX . "product_description pd ON p.product_id = pd.product_id
                LEFT JOIN " . DB_PREFIX . "review r ON p.product_id = r.product_id AND r.status = '1'
                WHERE p2c.category_id = '" . (int)$child_id . "'
                  AND p.status = '1'
                  AND p.date_available <= NOW()
                GROUP BY p.product_id
                HAVING special IS NOT NULL AND special <> ''
                ORDER BY " . $sort . " " . $order . "
                LIMIT 1"; 

        $query = $this->db->query($sql);
        if ($query->num_rows) {
            $products[] = $query->row;
        }
    }

  
    if ($limit) {
        $products = array_slice($products, $start, $limit);
    }

    return $products;
}


}
?>
