<?php
class ModelCatalogGaneshFestival extends Model {

    public function getFestivalCategories($category_ids = []) {
        $ids = implode(',', array_map('intval', $category_ids));

        $query = $this->db->query("
            SELECT c.*, cd.*
            FROM " . DB_PREFIX . "category c
            LEFT JOIN " . DB_PREFIX . "category_description cd 
                ON (c.category_id = cd.category_id)
            WHERE c.category_id IN ($ids) 
              AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY c.sort_order ASC
        ");

        return $query->rows;
    }

public function getSubcategories($parent_id) {
    $query = $this->db->query("
        SELECT 
            c.category_id,
            cd.name,
            c.image,
            (
                SELECT MIN(p.price)
                FROM " . DB_PREFIX . "product_to_category pc
                LEFT JOIN " . DB_PREFIX . "product p 
                    ON (pc.product_id = p.product_id AND p.status = 1)
                WHERE pc.category_id = c.category_id
            ) AS original_price,
            (
                SELECT MIN(ps.price)
                FROM " . DB_PREFIX . "product_to_category pc
                LEFT JOIN " . DB_PREFIX . "product p 
                    ON (pc.product_id = p.product_id AND p.status = 1)
                LEFT JOIN " . DB_PREFIX . "product_special ps 
                    ON (p.product_id = ps.product_id 
                        AND ps.date_start <= NOW() 
                        AND (ps.date_end = '0000-00-00' OR ps.date_end >= NOW())
                    )
                WHERE pc.category_id = c.category_id
            ) AS special_price
        FROM " . DB_PREFIX . "category c
        LEFT JOIN " . DB_PREFIX . "category_description cd 
            ON (c.category_id = cd.category_id)
        WHERE c.parent_id = '" . (int)$parent_id . "' 
          AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
          AND EXISTS (
              SELECT 1 
              FROM " . DB_PREFIX . "product_to_category pc2
              LEFT JOIN " . DB_PREFIX . "product p2 
                ON (pc2.product_id = p2.product_id)
              WHERE pc2.category_id = c.category_id
                AND p2.status = 1
          )
        ORDER BY c.sort_order ASC
    ");

    return $query->rows;
}

public function getAllSubcategories($parent_id) {
    $language_id = (int)$this->config->get('config_language_id');

    $sql = "
        SELECT c.category_id, c.parent_id, cd.name, c.image,
          (
            SELECT MIN(p.price)
            FROM " . DB_PREFIX . "product_to_category pc
            JOIN " . DB_PREFIX . "product p ON pc.product_id = p.product_id
            WHERE pc.category_id = c.category_id
              AND p.status = 1
          ) AS original_price,
          (
            SELECT MIN(ps.price)
            FROM " . DB_PREFIX . "product_to_category pc
            JOIN " . DB_PREFIX . "product p ON pc.product_id = p.product_id
            JOIN " . DB_PREFIX . "product_special ps
              ON p.product_id = ps.product_id
              AND ps.date_start <= NOW()
              AND (ps.date_end = '0000-00-00' OR ps.date_end >= NOW())
            WHERE pc.category_id = c.category_id
              AND p.status = 1
          ) AS special_price
        FROM " . DB_PREFIX . "category c
        JOIN " . DB_PREFIX . "category_description cd 
          ON c.category_id = cd.category_id AND cd.language_id = " . $language_id . "
        WHERE c.parent_id = " . (int)$parent_id . "

        UNION

        SELECT c.category_id, c.parent_id, cd.name, c.image,
          (
            SELECT MIN(p.price)
            FROM " . DB_PREFIX . "product_to_category pc
            JOIN " . DB_PREFIX . "product p ON pc.product_id = p.product_id
            WHERE pc.category_id = c.category_id
              AND p.status = 1
          ) AS original_price,
          (
            SELECT MIN(ps.price)
            FROM " . DB_PREFIX . "product_to_category pc
            JOIN " . DB_PREFIX . "product p ON pc.product_id = p.product_id
            JOIN " . DB_PREFIX . "product_special ps
              ON p.product_id = ps.product_id
              AND ps.date_start <= NOW()
              AND (ps.date_end = '0000-00-00' OR ps.date_end >= NOW())
            WHERE pc.category_id = c.category_id
              AND p.status = 1
          ) AS special_price
        FROM " . DB_PREFIX . "category c
        JOIN " . DB_PREFIX . "category_description cd 
          ON c.category_id = cd.category_id AND cd.language_id = " . $language_id . "
        WHERE c.parent_id IN (
          SELECT category_id FROM " . DB_PREFIX . "category WHERE parent_id = " . (int)$parent_id . "
        )

        UNION

        SELECT c.category_id, c.parent_id, cd.name, c.image,
          (
            SELECT MIN(p.price)
            FROM " . DB_PREFIX . "product_to_category pc
            JOIN " . DB_PREFIX . "product p ON pc.product_id = p.product_id
            WHERE pc.category_id = c.category_id
              AND p.status = 1
          ) AS original_price,
          (
            SELECT MIN(ps.price)
            FROM " . DB_PREFIX . "product_to_category pc
            JOIN " . DB_PREFIX . "product p ON pc.product_id = p.product_id
            JOIN " . DB_PREFIX . "product_special ps
              ON p.product_id = ps.product_id
              AND ps.date_start <= NOW()
              AND (ps.date_end = '0000-00-00' OR ps.date_end >= NOW())
            WHERE pc.category_id = c.category_id
              AND p.status = 1
          ) AS special_price
        FROM " . DB_PREFIX . "category c
        JOIN " . DB_PREFIX . "category_description cd 
          ON c.category_id = cd.category_id AND cd.language_id = " . $language_id . "
        WHERE c.parent_id IN (
          SELECT category_id FROM " . DB_PREFIX . "category WHERE parent_id IN (
            SELECT category_id FROM " . DB_PREFIX . "category WHERE parent_id = " . (int)$parent_id . "
          )
        )

        ORDER BY parent_id, category_id
    ";

    $query = $this->db->query($sql);

    return $query->rows;
}



public function getkidsSubcategories($parent_ids) {
    $language_id = (int)$this->config->get('config_language_id');


    if (is_array($parent_ids)) {
        $parent_ids = implode(",", array_map('intval', $parent_ids));
    } else {
        $parent_ids = (int)$parent_ids;
    }

    $sql = "
        SELECT DISTINCT c.category_id, c.parent_id, cd.name, c.image,
            (
                SELECT MIN(p.price)
                FROM " . DB_PREFIX . "product_to_category pc
                JOIN " . DB_PREFIX . "product p ON pc.product_id = p.product_id
                WHERE pc.category_id = c.category_id
                  AND p.status = 1
            ) AS original_price,
            (
                SELECT MIN(ps.price)
                FROM " . DB_PREFIX . "product_to_category pc
                JOIN " . DB_PREFIX . "product p ON pc.product_id = p.product_id
                JOIN " . DB_PREFIX . "product_special ps
                    ON p.product_id = ps.product_id
                   AND ps.date_start <= NOW()
                   AND (ps.date_end = '0000-00-00' OR ps.date_end >= NOW())
                WHERE pc.category_id = c.category_id
                  AND p.status = 1
            ) AS special_price
        FROM " . DB_PREFIX . "category c
        JOIN " . DB_PREFIX . "category_description cd 
            ON c.category_id = cd.category_id AND cd.language_id = " . $language_id . "
        WHERE c.parent_id IN (" . $parent_ids . ")
          AND EXISTS (
              SELECT 1
              FROM " . DB_PREFIX . "product_to_category pc
              JOIN " . DB_PREFIX . "product p ON pc.product_id = p.product_id
              WHERE pc.category_id = c.category_id
                AND p.status = 1
          )
        ORDER BY cd.name ASC
    ";

    $query = $this->db->query($sql);

    return $query->rows;
}

public function getCategoriesByIds($category_ids = []) {
    if (empty($category_ids)) {
        return [];
    }

   
    $ids = implode(',', array_map('intval', $category_ids));

    $query = $this->db->query("
        SELECT 
            c.category_id,
            cd.name,
            c.image,
            (
                SELECT MIN(p.price)
                FROM " . DB_PREFIX . "product_to_category pc
                LEFT JOIN " . DB_PREFIX . "product p 
                    ON (pc.product_id = p.product_id AND p.status = 1)
                WHERE pc.category_id = c.category_id
            ) AS original_price,
            (
                SELECT MIN(ps.price)
                FROM " . DB_PREFIX . "product_to_category pc
                LEFT JOIN " . DB_PREFIX . "product p 
                    ON (pc.product_id = p.product_id AND p.status = 1)
                LEFT JOIN " . DB_PREFIX . "product_special ps 
                    ON (p.product_id = ps.product_id 
                        AND ps.date_start <= NOW() 
                        AND (ps.date_end = '0000-00-00' OR ps.date_end >= NOW())
                    )
                WHERE pc.category_id = c.category_id
            ) AS special_price
        FROM " . DB_PREFIX . "category c
        LEFT JOIN " . DB_PREFIX . "category_description cd 
            ON (c.category_id = cd.category_id)
        WHERE c.category_id IN (" . $ids . ")
          AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
          AND c.status = 1
        ORDER BY c.sort_order ASC
    ");

    return $query->rows;
}



}
