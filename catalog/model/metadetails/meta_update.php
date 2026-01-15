<?php
class ModelMetadetailsMetaUpdate extends Model {

    public function updateProductMeta($product_id, $meta_title, $meta_description, $meta_keyword, $product_tags, $language_id = 1) {
        $this->db->query("UPDATE " . DB_PREFIX . "product_description 
        SET 
            meta_title = '" . $this->db->escape($meta_title) . "', 
            meta_description = '" . $this->db->escape($meta_description) . "', 
            meta_keyword = '" . $this->db->escape($meta_keyword) . "', 
            tag = '" . $this->db->escape($product_tags) . "'
        WHERE 
            product_id = '" . (int)$product_id . "' 
            AND language_id = '" . (int)$language_id . "'
    ");

    }
}