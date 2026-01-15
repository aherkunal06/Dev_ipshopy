<?php
class ModelExtensionMpImporterExporterExportOrder extends \MpImporterExporter\Model {
	public function getExtraFields() {
		$tables = [];

		// Product
		$default = ['order_id', 'invoice_no', 'invoice_prefix', 'store_id', 'store_name', 'store_url', 'customer_id', 'customer_group_id', 'firstname', 'lastname', 'email', 'telephone', 'fax', 'custom_field', 'payment_firstname', 'payment_lastname', 'payment_company', 'payment_address_1', 'payment_address_2', 'payment_city', 'payment_postcode', 'payment_country', 'payment_country_id', 'payment_zone', 'payment_zone_id', 'payment_address_format', 'payment_custom_field', 'payment_method', 'payment_code', 'shipping_firstname', 'shipping_lastname', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode', 'shipping_country', 'shipping_country_id', 'shipping_zone', 'shipping_zone_id', 'shipping_address_format', 'shipping_custom_field', 'shipping_method', 'shipping_code', 'comment', 'total', 'order_status_id', 'affiliate_id', 'commission', 'marketing_id', 'tracking', 'language_id', 'currency_id', 'currency_code', 'currency_value', 'ip', 'forwarded_ip', 'user_agent', 'accept_language', 'date_added', 'date_modified'];
		$query = $this->db->query("SHOW COLUMNS FROM `". DB_PREFIX ."order` ");
		$all_fields = [];

		foreach($query->rows as $order) {
			$all_fields[] = $order['Field'];
		}

		$extra_fields = array_diff($all_fields,$default);
		if($extra_fields) {
			$tables[] = [
				'title'			=> $this->language->get('table_order'),
				'tablename'		=> 'order',
				'fields'		=> $extra_fields,
			];
		}

		return $tables;
	}

	public function getOrders($data = []) {
		// Find Orders
		$sql = "SELECT o.*, CONCAT(o.firstname, ' ', o.lastname) AS customer, (SELECT os.name FROM `" . DB_PREFIX . "order_status` os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status FROM `" . DB_PREFIX . "order` o";

		// Find Product Left Join
		if (!empty($data['find_product'])) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "order_product` op ON (o.order_id = op.order_id) ";
		}

		if (isset($data['find_order_status'])) {
			$implode = [];

			$order_statuses = array_map('trim', explode(',', $data['find_order_status']));

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['find_order_id'])) {
			$comma_ids = array_map('trim', explode(',', $data['find_order_id']));

			if($comma_ids) {
				$implode = [];
				$implode1 = [];

				$sql .= " AND (";

				foreach($comma_ids as $comma_id) {
					if($comma_id) {
						$explode_comma_ids = array_map('trim', explode('-', $comma_id));
						if(count($explode_comma_ids) > 1)  {
							$implode[] = " (o.order_id BETWEEN '". $this->db->escape($explode_comma_ids[0]) ."' AND '". $this->db->escape($explode_comma_ids[1]) ."')";
						} else{
							$implode1[] = $comma_id;
						}
					}
				}

				$or = '';

				if ($implode) {
					$sql .= " " . implode(" OR ", $implode) . "";
					$or = ' OR ';
				}

				if ($implode1) {
					$sql .= $or . " o.order_id IN (". $this->db->escape(implode(',', $implode1)) .")";
				}

				$sql .= ")";
			}
		}

		if (!empty($data['find_total'])) {
			$sql .= " AND FORMAT(o.total, 2) = '" . number_format($data['find_total'], 2) . "'";
		}

		if (!empty($data['find_customer_group'])) {
			$sql .= " AND o.customer_group_id = '" . (int)$data['find_customer_group'] . "'";
		}

		// Find Customer
		if (!empty($data['find_customer'])) {
			$sql .= " AND (";

			foreach ($data['find_customer'] as $key => $customer) {
				if($key != '0') {
					$sql .= " OR ";
				}

				$sql .= "CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($customer) . "%'";
			}

			$sql .= ")";
		}

		// Find Product
		if (!empty($data['find_product'])) {
			$sql .= " AND (";

			foreach ($data['find_product'] as $key => $product) {
				if($key != '0') {
					$sql .= " OR ";
				}

				$sql .= "op.product_id = '" . $this->db->escape($product) . "'";
			}

			$sql .= ")";
		}

		// Find Price Range
		if ((isset($data['find_date_start']) && $data['find_date_start'] != '') && (isset($data['find_date_end']) && $data['find_date_end'] != '')) {
			// BETWEEN
			$sql .= " AND DATE(o.date_added) BETWEEN DATE('" . $this->db->escape($data['find_date_start']) . "') AND DATE('" . $this->db->escape($data['find_date_end']) . "')";
		}else if((isset($data['find_date_start']) && $data['find_date_start'] != '') && (isset($data['find_date_end']) && $data['find_date_end'] == '')) {
			// START FROM LIMIT (EMPTY)
			$sql .= " AND DATE(o.date_added) >= DATE('" . $this->db->escape($data['find_date_start']) . "')";
		}else if((isset($data['find_date_start']) && $data['find_date_start'] == '') && (isset($data['find_date_end']) && $data['find_date_end'] != '')) {
			// START (EMPTY) LIMIT TO
			$sql .= " AND DATE(o.date_added) <= DATE('" . $this->db->escape($data['find_date_end']) . "')";
		}

		if (!empty($data['find_payment_method'])) {
			$sql .= " AND o.payment_code IN ('". implode("','", (array)$data['find_payment_method']) ."')";
			// $implode = [];

			// $payment_methods = array_map('trim', explode(',', $data['find_payment_method']));

			// foreach ($payment_methods as $payment_method) {
			// 	if(!empty($payment_method)) {
			// 		$implode[] = "LCASE(o.payment_method) LIKE '%" . $this->db->escape(utf8_strtolower($payment_method)) . "%'";
			// 	}
			// }

			// if ($implode) {
			// 	$sql .= " AND (" . implode(" OR ", $implode) . ")";
			// }
		}

		if (!empty($data['find_shipping_method'])) {
			$sql .= " AND o.shipping_code IN ('". implode("','", (array)$data['find_shipping_method']) ."')";
			// $implode = [];

			// $shipping_methods = array_map('trim', explode(',', $data['find_shipping_method']));

			// foreach ($shipping_methods as $shipping_method) {
			// 	if(!empty($shipping_method)) {
			// 		$implode[] = "LCASE(o.shipping_method) LIKE '%" . $this->db->escape(utf8_strtolower($shipping_method)) . "%'";
			// 	}
			// }

			// if ($implode) {
			// 	$sql .= " AND (" . implode(" OR ", $implode) . ")";
			// }
		}

		if (isset($data['find_store_id']) && $data['find_store_id'] != '') {
			$sql .= " AND o.store_id = '" . (int)$data['find_store_id'] . "'";
		}

		if (isset($data['find_payment_country_id']) && $data['find_payment_country_id'] != '') {
			$sql .= " AND o.payment_country_id = '" . (int)$data['find_payment_country_id'] . "'";
		}

		if (isset($data['find_payment_zone_id']) && $data['find_payment_zone_id'] != '') {
			$sql .= " AND o.payment_zone_id = '" . (int)$data['find_payment_zone_id'] . "'";
		}

		if (isset($data['find_payment_postcode']) && $data['find_payment_postcode'] != '') {
			$sql .= " AND o.payment_postcode = '" . $this->db->escape($data['find_payment_postcode']) . "'";
		}

		if (isset($data['find_shipping_country_id']) && $data['find_shipping_country_id'] != '') {
			$sql .= " AND o.shipping_country_id = '" . (int)$data['find_shipping_country_id'] . "'";
		}

		if (isset($data['find_shipping_zone_id']) && $data['find_shipping_zone_id'] != '') {
			$sql .= " AND o.shipping_zone_id = '" . (int)$data['find_shipping_zone_id'] . "'";
		}

		if (isset($data['find_shipping_postcode']) && $data['find_shipping_postcode'] != '') {
			$sql .= " AND o.shipping_postcode = '" . $this->db->escape($data['find_shipping_postcode']) . "'";
		}

		if (isset($data['find_currency_id']) && $data['find_currency_id'] != '') {
			$sql .= " AND o.currency_id = '" . (int)$data['find_currency_id'] . "'";
		}

		if (isset($data['find_language_id']) && $data['find_language_id'] != '') {
			$sql .= " AND o.language_id = '" . (int)$data['find_language_id'] . "'";
		}

		if (isset($data['find_invoice_prefix']) && $data['find_invoice_prefix'] != '') {
			$sql .= " AND o.invoice_prefix = '" . $this->db->escape($data['find_invoice_prefix']) . "'";
		}

		if (isset($data['find_invoice']) && $data['find_invoice'] != '') {
			$sql .= " AND o.invoice_no = '" . $this->db->escape($data['find_invoice']) . "'";
		}

		$sort_data = [
			'o.order_id',
			'customer',
			'order_status',
			'o.date_added',
			'o.date_modified',
			'o.total'
		];

		if (isset($data['find_sort']) && in_array($data['find_sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['find_sort'];
		} else {
			$sql .= " ORDER BY o.order_id";
		}

		if (isset($data['find_order']) && ($data['find_order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		// Find Limit Range
		if ((isset($data['find_limit_start']) && $data['find_limit_start'] != '') && (isset($data['find_limit_end']) && $data['find_limit_end'] != '')) {
			// Limit 0, 100;
			$sql .= " LIMIT " . (int)$data['find_limit_start'] . "," . (int)$data['find_limit_end'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM `" . DB_PREFIX . "customer` c WHERE c.customer_id = o.customer_id) AS customer, (SELECT os.name FROM `" . DB_PREFIX . "order_status` os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

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

			$order_product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_id . "'");

			foreach ($order_product_query->rows as $product) {
				$reward += $product['reward'];
			}

			if ($order_query->row['affiliate_id']) {
				$affiliate_id = $order_query->row['affiliate_id'];
			} else {
				$affiliate_id = 0;
			}

			/*$this->load->model('marketing/affiliate');
			$affiliate_info = $this->model_marketing_affiliate->getAffiliate($affiliate_id);
			if ($affiliate_info) {
				$affiliate_firstname = $affiliate_info['firstname'];
				$affiliate_lastname = $affiliate_info['lastname'];
			} else {
				$affiliate_firstname = '';
				$affiliate_lastname = '';
			}*/

			$affiliate_firstname = '';
			$affiliate_lastname = '';

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			return [
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
				'marketing_id'            => $order_query->row['marketing_id'],
				'tracking'            		=> $order_query->row['tracking'],
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
			];
		} else {
			return;
		}
	}

	public function getCustomField($custom_field_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field` cf LEFT JOIN `" . DB_PREFIX . "custom_field_description` cfd ON (cf.custom_field_id = cfd.custom_field_id) WHERE cf.custom_field_id = '" . (int)$custom_field_id . "' AND cfd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}
}

if (VERSION <= '2.2.0.0') {
	class ModelMpImporterExporterExportOrder extends ModelExtensionMpImporterExporterExportOrder { }
}