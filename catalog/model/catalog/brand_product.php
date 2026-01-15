<?php
class ModelCatalogBrandProduct extends Model {
    
    // Get latest products by brand/manufacturer
public function getProductsByBrand($manufacturer_id, $limit = 6) {
    $sql = "
        SELECT 
            p.product_id,
            pd.name,
            p.image,
            p.price,
            (
                SELECT price FROM " . DB_PREFIX . "product_special ps 
                WHERE ps.product_id = p.product_id 
                  AND ps.customer_group_id = '1' 
                  AND ps.date_start <= NOW() 
                  AND (ps.date_end = '0000-00-00' OR ps.date_end >= NOW())
                ORDER BY ps.priority ASC, ps.price ASC
                LIMIT 1
            ) AS special
        FROM " . DB_PREFIX . "product p
        LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
        WHERE p.status = 1
          AND p.manufacturer_id = '" . (int)$manufacturer_id . "' 
          AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ORDER BY p.date_added DESC
        LIMIT " . (int)$limit;

    $query = $this->db->query($sql);
    return $query->rows;
}

    // Get brand name by manufacturer_id
    public function getBrandName($manufacturer_id) {
        $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        return $query->num_rows ? $query->row['name'] : '';
    }



}
