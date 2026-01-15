<?php

class ModelExtensionFeedMergadoMarketingPackGenerator extends Model {

    const DB_GENERATOR_TABLE = 'mergado_marketing_pack_generator';
  
    public function getData($hash_code) {
        $result = $this->db->query("SELECT * FROM " . DB_PREFIX . self::DB_GENERATOR_TABLE  . " WHERE hash_code='{$hash_code}'" );
        
        return $result->rows;
    }

    public function delete($hash_code) {
        return $this->db->query("DELETE FROM " . DB_PREFIX . self::DB_GENERATOR_TABLE  . " WHERE hash_code='{$hash_code}'" );
    }

}
