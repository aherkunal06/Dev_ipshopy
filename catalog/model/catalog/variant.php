<?php
class ModelCatalogVariant extends Model {
  // Get this product's row in oc_product_variants (to read its group)
  public function getVariantRowByProductId($product_id) {
    $sql = "SELECT variant_id, product_id, variant_name, variant_image, size_value, variant_group_id
            FROM " . DB_PREFIX . "product_variants
            WHERE product_id = " . (int)$product_id . " LIMIT 1";
    $query = $this->db->query($sql);
    return $query->row ?: null;
  }

  // Get all variants in the same group
  public function getVariantsByGroupId($variant_group_id) {
    $sql = "SELECT pv.variant_id, pv.product_id, pv.variant_name, pv.variant_image, pv.size_value, pv.variant_group_id,
                   p.quantity, p.status
            FROM " . DB_PREFIX . "product_variants pv
            LEFT JOIN " . DB_PREFIX . "product p ON p.product_id = pv.product_id
            WHERE pv.variant_group_id = " . (int)$variant_group_id . "
            ORDER BY pv.variant_name ASC, pv.size_value ASC";
    $query = $this->db->query($sql);
    return $query->rows;
  }
}
