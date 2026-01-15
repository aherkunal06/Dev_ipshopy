<?php
class ModelCatalogIndependence extends Model {
    public function getDiscountedProductsByCategory($category_id, $discount_percentage = 40) {
        $query = $this->db->query("
            SELECT p.product_id, pd.name, p.image, p.price, p.tax_class_id, p.quantity, p.status, p.date_available, p.sort_order, p.model, p.sku, p.upc, p.ean, p.jan, p.isbn, p.mpn, 
            (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special
            FROM " . DB_PREFIX . "product p
            LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
            LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
            WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            AND p.status = '1'
            AND p.date_available <= NOW()
            AND p2c.category_id IN (
                SELECT category_id FROM " . DB_PREFIX . "category_path WHERE path_id = '" . (int)$category_id . "'
            )
        ");

        $products = [];
        foreach ($query->rows as $product) {
            if (!empty($product['special']) && $product['price'] > 0) {
                $discount = (($product['price'] - $product['special']) / $product['price']) * 100;
                if ($discount >= $discount_percentage) {
                    $products[] = $product;
                }
            }
        }

        return $products;
    }
}
