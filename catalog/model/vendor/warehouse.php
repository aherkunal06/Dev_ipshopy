<?php
class ModelVendorWarehouse extends Model
{

    public function getWarehousesByVendorId($vendor_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seller_warehouse_address WHERE vendor_id = '" . (int)$vendor_id . "'");
        return $query->rows;
    }

    public function addWarehouse($data, $vendor_id)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "seller_warehouse_address SET 
            vendor_id   = '" . (int)$vendor_id . "',
            firstname   = '" . $this->db->escape($data['firstname']) . "',
            lastname    = '" . $this->db->escape($data['lastname']) . "',
            email       = '" . $this->db->escape($data['email']) . "',
            telephone   = '" . $this->db->escape($data['telephone'] ?? '') . "',
            address_1   = '" . $this->db->escape($data['address_1']) . "',
            address_2   = '" . $this->db->escape($data['address_2']) . "',
            city        = '" . $this->db->escape($data['city']) . "',
            postcode    = '" . $this->db->escape($data['postcode']) . "',
            country_id  = '" . (int)$data['country_id'] . "',
            zone_id     = '" . (int)$data['zone_id'] . "',
            is_default  = 0,
            date_added  = NOW()");
    }
}
