<?php

class ModelCatalogHsn extends Model {
    public function getHsnData($hsn_code) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "hsn_data WHERE hsn_code = '" . $this->db->escape($hsn_code) . "' LIMIT 1");
        return $query->row;
    }
}

?>