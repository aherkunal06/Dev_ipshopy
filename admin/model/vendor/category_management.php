<?php
class ModelVendorCategoryManagement extends Model {

    public function __construct($registry) {
        parent::__construct($registry);
    }

    // --- Core Category Management Functions (all_category table) ---

    public function addCategory($data) {
        $level = 0;
        if (isset($data['parent_id']) && $data['parent_id'] > 0) {
            $parent_category = $this->getCategory($data['parent_id']);
            if ($parent_category) {
                $level = $parent_category['level'] + 1;
            }
        }

        // Using 'all_category' as per your existing code for category hierarchy
        $this->db->query("INSERT INTO `" . DB_PREFIX . "all_category` SET
            `category_name` = '" . $this->db->escape($data['category_name']) . "',
            `parent_id` = '" . (int)$data['parent_id'] . "',
            `level` = '" . (int)$level . "'
        ");

        return $this->db->getLastId();
    }

    public function getCategoryByName($name) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "all_category` WHERE category_name = '" . $this->db->escape($name) . "' LIMIT 1");
        return $query->row;
    }

    public function editCategory($category_id, $data) {
        $level = 0;
        if (isset($data['parent_id']) && $data['parent_id'] > 0) {
            $parent_category = $this->getCategory($data['parent_id']);
            if ($parent_category) {
                $level = $parent_category['level'] + 1;
            }
        }

        $this->db->query("UPDATE `" . DB_PREFIX . "all_category` SET
            `category_name` = '" . $this->db->escape($data['category_name']) . "',
            `parent_id` = '" . (int)$data['parent_id'] . "',
            `level` = '" . (int)$level . "'
            WHERE `category_id` = '" . (int)$category_id . "'
        ");
    }

    public function getCategory($category_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "all_category` WHERE `category_id` = '" . (int)$category_id . "'");
        return $query->row;
    }

    public function getAllCategories($data = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "all_category` WHERE 1";

        if (!empty($data['filter_name'])) {
            $sql .= " AND `category_name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = [
            'category_id',
            'category_name',
            'parent_id',
            'level'
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY category_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getTotalCategories($data = []) {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "all_category` WHERE 1";

        if (!empty($data['filter_name'])) {
            $sql .= " AND `category_name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        $query = $this->db->query($sql);
        return $query->row['total'];
    }

    public function getCategoryChildren($parent_id) {
        $query = $this->db->query("SELECT category_id, category_name FROM `" . DB_PREFIX . "all_category` WHERE `parent_id` = '" . (int)$parent_id . "' ORDER BY category_name ASC");
        return $query->rows;
    }

    public function getCategoryPath($category_id) {
        // This function now returns an array of category IDs in the path, from root to the given category.
        $path = [];
        $current_id = (int)$category_id;

        while ($current_id != 0) {
            $query = $this->db->query("SELECT category_id, parent_id FROM `" . DB_PREFIX . "all_category` WHERE category_id = '" . (int)$current_id . "'");
            if ($query->num_rows) {
                array_unshift($path, $query->row['category_id']); // Add to the beginning to maintain hierarchy order
                $current_id = $query->row['parent_id'];
            } else {
                $current_id = 0; // Break if category not found or root reached
            }
        }
        return $path;
    }

    public function deleteAllCategoriesData() {
        $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "all_category`");
        return true;
    }

  

    // --- Product-to-Category (OpenCart Standard) ---

    // This function is for assigning product to standard OpenCart categories,
    // which are different from your custom 5-level 'all_category' structure.
    // If you intend to use the standard OpenCart product_to_category table,
    // you'll need to pass $data['product_category'] from the controller.
    public function addProductCategories($product_id, $product_categories) {
        // Clear existing standard categories for the product
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

        // Insert new standard categories
        if (isset($product_categories)) {
            foreach ($product_categories as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
            }
        }
    }

    // This function retrieves standard OpenCart categories assigned to a product.
    public function getProductCategories($product_id) {
        $product_category_data = [];
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
        foreach ($query->rows as $result) {
            $product_category_data[] = $result['category_id'];
        }
        return $product_category_data;
    }

    public function getProductsByCategoryId($category_id) {
        // This queries standard OpenCart product data linked via product_to_category
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

        return $query->rows;
    }


    // --- Custom 5-Level Product Category Functions (vendor_product_category table) ---

    // This function is likely meant for getting categories from the 'all_category' table,
    // not 'oc_category', as implied by your usage pattern.
    // It retrieves all categories from your custom 'all_category' table.
    public function getCategories() {
        $query = $this->db->query("SELECT c.category_id, c.category_name, c.parent_id
                                   FROM " . DB_PREFIX . "all_category c
                                   ORDER BY c.parent_id, c.category_name");
        if ($query->num_rows) {
            return $query->rows;
        } else {
            return [];
        }
    }

    // This function gets direct children of a given parent_id from 'all_category'.
    public function getCategoriesByParentId($parent_id = 0) {
        $query = $this->db->query("SELECT category_id, category_name, level FROM " . DB_PREFIX . "all_category WHERE parent_id = '" . (int)$parent_id . "'");
        return $query->rows;
    }

    // This function retrieves the 5-level category IDs assigned to a product from `vendor_product_category`.
    public function getProductCategoryLevels($product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_product_category WHERE product_id = '" . (int)$product_id . "'");
        return $query->row;
    }

    // This function gets the 5-level category IDs for a product and formats them.
    public function getProductCategoriesinfo($product_id) {
        $query = $this->db->query("SELECT category_level_1, category_level_2, category_level_3, category_level_4, category_level_5
                                 FROM " . DB_PREFIX . "vendor_product_category
                                 WHERE product_id = '" . (int)$product_id . "'");

        $categories = ['level_1' => 0, 'level_2' => 0, 'level_3' => 0, 'level_4' => 0, 'level_5' => 0];

        if ($query->num_rows) {
            $row = $query->row;
            for ($i = 1; $i <= 5; $i++) {
                if (!empty($row['category_level_' . $i])) {
                    $categories['level_' . $i] = (int)$row['category_level_' . $i];
                }
            }
        }
        return $categories;
    }

    // This function saves/updates the 5-level category assignment for a product.
    public function saveProductCategoryLevels($product_id, $data) {
        $category_level_1 = isset($data['category_level_1']) ? (int)$data['category_level_1'] : 0;
        $category_level_2 = isset($data['category_level_2']) ? (int)$data['category_level_2'] : 0;
        $category_level_3 = isset($data['category_level_3']) ? (int)$data['category_level_3'] : 0;
        $category_level_4 = isset($data['category_level_4']) ? (int)$data['category_level_4'] : 0;
        $category_level_5 = isset($data['category_level_5']) ? (int)$data['category_level_5'] : 0;
        $vendor_id = isset($data['vendor_id']) ? (int)$data['vendor_id'] : 0; // Ensure vendor_id is passed if needed

        // Delete existing 5-level categories for the product
        $this->db->query("DELETE FROM " . DB_PREFIX . "vendor_product_category WHERE product_id = '" . (int)$product_id . "'");

        // Insert new 5-level categories
        $this->db->query("INSERT INTO " . DB_PREFIX . "vendor_product_category SET
            vendor_id = '" . (int)$vendor_id . "',
            product_id = '" . (int)$product_id . "',
            category_level_1 = '" . $category_level_1 . "',
            category_level_2 = '" . $category_level_2 . "',
            category_level_3 = '" . $category_level_3 . "',
            category_level_4 = '" . $category_level_4 . "',
            category_level_5 = '" . $category_level_5 . "'
        ");
    }

    // Returns the maximum category_id in the all_category table
    public function getMaxCategoryId() {
        $query = $this->db->query("SELECT MAX(category_id) as max_id FROM `" . DB_PREFIX . "all_category`");
        return $query->row['max_id'] ? (int)$query->row['max_id'] : 0;
    }

    public function getCategoriesByLevel($level) {
        $query = $this->db->query("SELECT category_id, category_name FROM `" . DB_PREFIX . "all_category` WHERE level = '" . (int)$level . "' ORDER BY category_name ASC");
        return $query->rows;
    }

    // Returns a category by name and parent_id (for uniqueness in hierarchy)
    public function getCategoryByNameAndParent($name, $parent_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "all_category` WHERE category_name = '" . $this->db->escape($name) . "' AND parent_id = '" . (int)$parent_id . "' LIMIT 1");
        return $query->row;
    }

    public function hasChildCategories($category_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "all_category WHERE parent_id = '" . (int)$category_id . "'");
        return ($query->row['total'] > 0);
    }

    public function deleteCategory($category_id) {
        // First check if category exists
        $category = $this->getCategory($category_id);
        if (!$category) {
            return false;
        }

        // Check for child categories
        if ($this->hasChildCategories($category_id)) {
            return false;
        }

        // Delete the category
        $this->db->query("DELETE FROM " . DB_PREFIX . "all_category WHERE category_id = '" . (int)$category_id . "'");
        return true;
    }

    // public function deleteCategory($category_id) {
    //     // This query deletes the category and its direct children.
    //     // For full hierarchical deletion, you'd need a more complex recursive query or loop.
    //     $this->db->query("DELETE FROM `" . DB_PREFIX . "all_category` WHERE `category_id` = '" . (int)$category_id . "' OR `parent_id` = '" . (int)$category_id . "'");
    // }


}