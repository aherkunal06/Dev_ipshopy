<?php 
require_once(DIR_SYSTEM . "/library/phonepePG/PhonepeConfig.php");
class ModelExtensionPaymentPhonepePG extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/phonepePG');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_phonepePG_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('payment_phonepePG_total') > 0 && $this->config->get('payment_phonepePG_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_phonepePG_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$status = (isset($this->session->data['currency']) && $this->session->data['currency'] != 'INR' && PhonepeConstantsPG::ONLY_SUPPORTED_INR) ? false : $status;

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'phonepePG',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_phonepePG_sort_order')
			);
		}

		return $method_data;
	}
	/**
	* save response in db
	*/
	public function saveTxnResponse($data  = array(),$order_id){
		if(empty($data['code'])) return false;

		$status 			= (!empty($data['code']) && $data['code'] =='PAYMENT_SUCCESS') ? PhonepeConstantsPG::SUCCESS : PhonepeConstantsPG::ERROR;
		$phonepe_order_id 	= (!empty($data['data']['providerReferenceId'])? $data['data']['providerReferenceId']:'');
		$transaction_id 	= (!empty($data['data']['transactionId'])? $data['data']['transactionId']:'');

		$sql =  "INSERT INTO " . DB_PREFIX . "phonepePG_payment_data SET order_id = '" . $order_id . "', phonepe_order_id = '" . $phonepe_order_id . "', transaction_id = '" . $this->db->escape($transaction_id) . "', status = '" . $status . "', phonepe_response = '" . $this->db->escape(json_encode($data['message'])) . "', date_added = NOW(), date_modified = NOW()";
		//echo $sql;
		$this->db->query($sql);
		return $this->db->getLastId();
	}
}
?>