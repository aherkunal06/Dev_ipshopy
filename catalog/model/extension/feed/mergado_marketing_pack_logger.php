<?php

class ModelExtensionFeedMergadoMarketingPackLogger extends Model {

    const DB_LOG_TABLE = 'mergado_marketing_pack_log';
  
    public function log($label, $log, $store_id = 0, $lang = '', $currency = '') {

        if (MERGADO_LOGGER_ENABLED == 0) { return; }

        if (is_array($log)) { //convert array to json string
            if(!empty($log)) {
                $log = json_encode($log);
            } else {
                $log = '';
            }
        } else {
            $log = '{"msg": ' . (is_numeric($log) ? $log :  '"' . $log . '"') . '}';
        }

        $this->db->query("INSERT INTO " . DB_PREFIX . self::DB_LOG_TABLE ." (`log_label`, `log_msg`, `log_date`, `store_id`, `currency`, `lang_code`) VALUES ('". $label ."', '".  $this->db->escape($log) ."', NOW(), {$store_id}, '" . $currency . "', '" . $lang . "') ");
    }

    public function getLogs($filter = array(), $limit = '', $orderby = 'ASC') {

        $where = "";
        if(!empty($filter)) {
            $where = " WHERE";
            foreach($filter as $key=>$value) {
                if($value['type'] == "int") {
                    $where .= " " . $key . "=" . $value["value"];
                } elseif($value['type'] == "string") {
                    $where .= " " . $key . "='" . $value["value"]. "'";
                }

                if(isset($value['relation'])) {
                    $where .= ' ' . strtoupper($value['relation']) . ' ';
                }
            }
        }

        if($limit != '') {
            $limit = ' LIMIT '. $limit;
        }

        $order = ' ORDER BY log_id ' .$orderby;

        $result = $this->db->query("SELECT * FROM " . DB_PREFIX . self::DB_LOG_TABLE  . $where . $order . $limit  );
        
        return $result->rows;
    }

}
