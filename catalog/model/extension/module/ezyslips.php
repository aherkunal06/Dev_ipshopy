<?php

error_reporting(0);
ini_set('display_errors' ,0);

class ModelExtensionModuleEzyslips extends Model {
	public function authenticate($key_hash) {
		$loginCreds	= $this->db->query("SELECT `value` FROM ".DB_PREFIX."setting WHERE `code` = 'module_ezyslips_connector' AND `key` = 'module_ezyslips_connector' ")->row;

		if(!isset($loginCreds['value'])){
				return false;
		}else{				
			
			$loginCredsDecoded = json_decode($loginCreds['value'] ,true);
			
			if(empty($loginCredsDecoded)){
				$loginCredsDecoded = unserialize($loginCreds['value'] );
			}
							
			if(!isset($loginCredsDecoded['email']) || !isset($loginCredsDecoded['password'] ) ){
				return false;
			}
			
			$expectedHash = strtoupper( md5($loginCredsDecoded['email'].':'.$loginCredsDecoded['password']) );
			
			if($expectedHash == $key_hash ){
				return true;
			}
		}	
	}
	public function getOrderList($params){
				

		$conditions = "";
		$order_status_id = implode(',', $params['order_status']);
		
		if( isset($params['time_from']) && !empty($params['time_from']) ){
			$conditions .= " AND date_added > '".date("Y-m-d H:i:s" , $params['time_from']) ."' ";
		}
		
		if( isset($params['order_id_since']) && !empty($params['order_id_since']) ){
			$conditions .= " AND order_id > '" . addslashes( $params['order_id_since'] ) . "' ";
		}
		
		if( isset($params['payment_method']) && !empty($params['payment_method']) ){
			$conditions .= " AND payment_method = '" . addslashes( $params['payment_method'] ) . "' ";
		}
		
		if( isset($params['payment_code']) && !empty($params['payment_code']) ){
			$conditions .= " AND payment_code = '" . addslashes( $params['payment_code'] ) . "' ";
		}
		
		if( isset($params['total']) && !empty($params['total']) ){
			$conditions .= " AND total = '" . (float)$params['total'] . "' ";
		}
		
		if( isset($params['total_min']) && !empty($params['total_min']) ){
			$conditions .= " AND total >= '" . (float)$params['total_min'] . "' ";
		}
		
		if( isset($params['total_max']) && !empty($params['total_max']) ){
			$conditions .= " AND total <= '" . (float)$params['total_max'] . "' ";
		}
		if( isset($order_status_id) && !empty($order_status_id) ){
		  	$conditions .= " AND order_status_id IN(".$order_status_id.") ";
		}		
		
		$limit_cond = '';
		
		$limit 	= ( isset($params['limit']) ) ? (int)$params['limit'] : 20;
		$page 	= ( isset($params['page']) ) ? (int) $params['page'] : 1;
		
		if( !empty($limit) ){
			
			$start 	= ($page - 1) * $limit;			
			$limit_cond = " LIMIT $start , " . (int)$limit;			
		}
		
		$params['limit']	= $limit;
		$params['page']		= $page;
		
		$sql = "SELECT DISTINCT order_id FROM `". DB_PREFIX ."order` WHERE order_status_id > '0'";
		
		$sql .= $conditions.$limit_cond;			
		
		$order_count = $this->db->query("SELECT COUNT(*) AS total FROM `".DB_PREFIX."order` WHERE order_status_id > '0' ".$conditions )->row;
		
		$params['total_count'] = $order_count['total'];
		
		$ordersList = $this->db->query($sql)->rows;
		
		return  array( $ordersList , $params );
	}
	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

		if ($order_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

			$reward = 0;

			$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach ($order_product_query->rows as $product) {
				$reward += $product['reward'];
			}
			
			if ($order_query->row['affiliate_id']) {
				$affiliate_id = $order_query->row['affiliate_id'];
			} else {
				$affiliate_id = 0;
			}

			//$affiliate_info = $this->getAffiliate($affiliate_id);

			/* if ($affiliate_info) {
				$affiliate_firstname = $affiliate_info['firstname'];
				$affiliate_lastname = $affiliate_info['lastname'];
			} else {
				$affiliate_firstname = '';
				$affiliate_lastname = '';
			} */
			$affiliate_firstname = '';
				$affiliate_lastname = '';

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}
//echo "<pre>";print_r($order_query);die;
			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'customer'                => $order_query->row['customer'],
				'customer_group_id'       => $order_query->row['customer_group_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'email'                   => $order_query->row['email'],
				'telephone'               => $order_query->row['telephone'],
				'fax'                     => $order_query->row['fax'],
				'custom_field'            => json_decode($order_query->row['custom_field'], true),
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_custom_field'    => json_decode($order_query->row['payment_custom_field'], true),
				'payment_method'          => $order_query->row['payment_method'],
				'payment_code'            => $order_query->row['payment_code'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_custom_field'   => json_decode($order_query->row['shipping_custom_field'], true),
				'shipping_method'         => $order_query->row['shipping_method'],
				'shipping_code'           => $order_query->row['shipping_code'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'reward'                  => $reward,
				'order_status_id'         => $order_query->row['order_status_id'],
				'order_status'            => $order_query->row['order_status'],
				'affiliate_id'            => $order_query->row['affiliate_id'],
				'affiliate_firstname'     => $affiliate_firstname,
				'affiliate_lastname'      => $affiliate_lastname,
				'commission'              => $order_query->row['commission'],
				'language_id'             => $order_query->row['language_id'],
				'language_code'           => $language_code,
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'ip'                      => $order_query->row['ip'],
				'forwarded_ip'            => $order_query->row['forwarded_ip'],
				'user_agent'              => $order_query->row['user_agent'],
				'accept_language'         => $order_query->row['accept_language'],
				'date_added'              => $order_query->row['date_added'],
				'date_modified'           => $order_query->row['date_modified']
			);
		} else {
			return;
		}
	}
	
	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}
	
	public function getAffiliate($affiliate_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "affiliate WHERE affiliate_id = '" . (int)$affiliate_id . "'");

		return $query->row;
	}
	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT op.*, oo.`name` AS option_name, oo.`value` AS option_value FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "order_option oo ON (op.order_product_id = oo.order_product_id) WHERE op.order_id = '" . (int)$order_id . "' GROUP BY op.order_product_id ");

		return $query->rows;
	}
	public function getProductDimensionWeight($product_id) {
		$query = $this->db->query("SELECT p.weight, p.weight_class_id, p.length, p.width, p.height,p.length_class_id, lc.unit as length_unit, wc.unit as weight_unit  FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "length_class_description lc ON (lc.length_class_id = p.length_class_id AND lc.language_id = '" . (int)$this->config->get('config_language_id') . "' ) LEFT JOIN " . DB_PREFIX . "weight_class_description wc ON (wc.weight_class_id = p.weight_class_id AND wc.language_id = '" . (int)$this->config->get('config_language_id') . "' ) WHERE p.product_id = '" . (int)$product_id . "' limit 1 ");

		return $query->row;
	}
}