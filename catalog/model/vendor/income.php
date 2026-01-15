<?php
class ModelVendorincome extends Model {
	
	public function addAmount($data) {
		
		$sql="INSERT INTO " . DB_PREFIX . "vendor_amount_pay set vendor_id='".(int)$data['vendor_id']."',payment_method='".$this->db->escape($data['payment_method'])."',amount='".(float)$data['amount']."',comment='".$this->db->escape($data['comment'])."',date_added=now()";
		$this->db->query($sql);
	}

	public function getSellerTotal($vendor_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id ='".(int)$vendor_id. "'";
								
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getOrder($order_id){
		$sql = "SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id='".(int)$order_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getAmount($vendor_id){
		$sql = "SELECT sum(amount) AS total FROM " . DB_PREFIX . "vendor_amount_pay WHERE vendor_id='".(int)$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
				
 	public function getIncomes($data=array()){
		
		$sql = "SELECT *  FROM " . DB_PREFIX . "vendor_amount_pay WHERE pay_id<>0 ";
			
		if(isset($data['vendor_id'])){
			$sql .= " and vendor_id='".(int)$data['vendor_id']."'";
		}

		if (!empty($data['filter_date_form'])) {
			$sql .= " AND DATE(date_added) >= '" . $data['filter_date_form'] . "'";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(date_added) <= '" . $data['filter_date_to'] . "'";
		}

		$sort_data = array(
			'vendor_id'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY vendor_id";
		}
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}
			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		
		return $query->rows;	
 	}
	
 	public function getTotalIncome($data=array()) {
		
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_amount_pay WHERE pay_id<>0";
		
		if(isset($data['vendor_id'])){
			$sql .= " and vendor_id='".(int)$data['vendor_id']."'";
		}

		if (!empty($data['filter_date_form'])) {
			$sql .= " AND DATE(date_added) >= '" . $data['filter_date_form'] . "'";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(date_added) <= '" . $data['filter_date_to'] . "'";
		}

		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	// added changes on the 16-05-2025
	public function getVendorPayments($vendor_id) {
        $language_id = (int)$this->config->get('config_language_id');
    
        $query = $this->db->query("
			SELECT 
				ovs.*, 
				pd.name AS product_name, 
				vop.quantity AS amount,
				vop.price,
				vap.reference_number,
				vap.amount AS paid_amount
			FROM " . DB_PREFIX . "order_vendor_settlement ovs
			LEFT JOIN " . DB_PREFIX . "vendor_order_product vop 
				ON ovs.order_id = vop.order_id AND ovs.vendor_id = vop.vendor_id
			LEFT JOIN " . DB_PREFIX . "product_description pd 
				ON vop.product_id = pd.product_id AND pd.language_id = '" . $language_id . "'
			LEFT JOIN " . DB_PREFIX . "vendor_amount_pay vap 
				ON ovs.vendor_id = vap.vendor_id AND ovs.order_id = vop.order_id
			WHERE ovs.vendor_id = '" . (int)$vendor_id . "'
    	");
        return $query->rows;
    }
	
	
	//  added on the 16-05-2025 ------------------------------------------------------------------------------
	public function getVendorPaymentsByIncomeId($vendor_id, $income_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_vendor_settlement WHERE vendor_id = '" . (int)$vendor_id . "' AND income_id = '" . (int)$income_id . "'");
        return $query->rows;
    }

	public function getVendorPaymentSummary($vendor_id) {
        $query = $this->db->query("SELECT amount, reference_number FROM " . DB_PREFIX . "vendor_amount_pay WHERE vendor_id = '" . (int)$vendor_id . "'");
        return $query->rows;
    }

	// Updated the function to get paid rto and reverse orders on 16-07-2025
    public function getVendorPaidProductDetails($vendor_id, $reference_number, $payment_date) {
		$query = $this->db->query("
			SELECT 
				vop.order_id, 
				vop.product_id,
				vop.name AS product_name,
				vop.quantity,
				vop.total AS price,
				ovs.net_settlement_amount,
				ovs.payment_status,
				ovs.courier_rate,
				ovs.rto_charges,
				ovs.reverse_courier_charges,
				ovs.total_deduction,
				ovs.product_courier_charges,
				ovs.reference_number,
				ovs.payment_date
			FROM " . DB_PREFIX . "vendor_order_product vop
			LEFT JOIN " . DB_PREFIX . "order_vendor_settlement ovs 
				ON vop.order_id = ovs.order_id
			WHERE vop.vendor_id = '" . (int)$vendor_id . "' 
				AND ovs.payment_status = 'paid'
				AND ovs.reference_number = '" . $this->db->escape($reference_number) . "'
				AND DATE(ovs.payment_date) = '" . $this->db->escape($payment_date) . "'
				AND vop.order_status_id IN (5,30, 31, 12, 21, 22, 38)
			ORDER BY ovs.payment_date DESC
		");
	
		return $query->rows;
	}
    
    public function getVendorCompleteOrderData($vendor_id){
		// Query to sum up totals per order for the given vendor
		$sql = "SELECT vop.order_id, 
               SUM(vop.total) AS total, 
            --    SUM(vop.tax * vop.quantity) AS tax, 
               SUM(vop.totalcommission) AS commission, 
               SUM(vop.tmdshippingcost) AS shipping,
               IF(CHAR_LENGTH(vop.name) > 40, CONCAT(LEFT(vop.name, 40), '...'), vop.name) AS name,
               vop.quantity,
               ovs.courier_rate,
			   ovs.rto_charges,
			   ovs.total_deduction,
			   ovs.reverse_courier_charges,
			   ovs.payment_status,
			   ovs.net_settlement_amount,
			   ovs.product_courier_charges
			FROM " . DB_PREFIX . "vendor_order_product vop
			LEFT JOIN " . DB_PREFIX . "order_vendor_settlement ovs 
				ON vop.order_id = ovs.order_id
			WHERE vop.order_product_id <> 0 
			AND vop.vendor_id = '" . (int)$vendor_id . "' 
			AND vop.order_status_id = 5 
			AND ovs.payment_status IS NULL
			GROUP BY vop.order_id
			ORDER BY vop.date_modified DESC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getVendorCompleteOrderDataWithPaidStatus($vendor_id){
		// Query to sum up totals per order for the given vendor
		$sql = "SELECT vop.order_id, 
               SUM(vop.total) AS total, 
            --    SUM(vop.tax * vop.quantity) AS tax, 
               SUM(vop.totalcommission) AS commission, 
               SUM(vop.tmdshippingcost) AS shipping,
               IF(CHAR_LENGTH(vop.name) > 40, CONCAT(LEFT(vop.name, 40), '...'), vop.name) AS name,
               vop.quantity,
               ovs.courier_rate,
			   ovs.rto_charges,
			   ovs.total_deduction,
			   ovs.reverse_courier_charges,
			   ovs.payment_status,
			   ovs.net_settlement_amount,
			   ovs.product_courier_charges
			FROM " . DB_PREFIX . "vendor_order_product vop
			LEFT JOIN " . DB_PREFIX . "order_vendor_settlement ovs 
				ON vop.order_id = ovs.order_id
			WHERE vop.order_product_id <> 0 
			AND vop.vendor_id = '" . (int)$vendor_id . "' 
			AND vop.order_status_id = 5 
			AND ovs.payment_status = 'paid'
			GROUP BY vop.order_id
			ORDER BY vop.date_modified DESC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	// added the code for the rto and reverse orders on 25-06-2025
	public function getRTOAndReturnOrders($vendor_id) {
		$sql = "SELECT vop.order_id, 
					SUM(vop.total) AS total, 
					SUM(vop.totalcommission) AS commission, 
					SUM(vop.tmdshippingcost) AS shipping,
					IF(CHAR_LENGTH(vop.name) > 40, CONCAT(LEFT(vop.name, 40), '...'), vop.name) AS name,
					vop.quantity,
					ovs.courier_rate,
					ovs.rto_charges,
					ovs.total_deduction,
					ovs.reverse_courier_charges,
					ovs.payment_status,
					ovs.net_settlement_amount,
					ovs.product_courier_charges
				FROM " . DB_PREFIX . "vendor_order_product vop
				LEFT JOIN " . DB_PREFIX . "order_vendor_settlement ovs 
					ON vop.order_id = ovs.order_id
				WHERE vop.order_product_id <> 0 
				AND vop.vendor_id = '" . (int)$vendor_id . "' 
				AND vop.order_status_id IN (30, 31, 12, 21, 22, 38)
				AND ovs.payment_status IS NULL
				GROUP BY vop.order_id
				ORDER BY vop.date_modified DESC";

		$query = $this->db->query($sql);
		return $query->rows;
	}

	// added the code for the rto and reverse orders on 25-06-2025
	public function getRTOAndReturnPaidOrders($vendor_id) {
		$sql = "SELECT vop.order_id, 
					SUM(vop.total) AS total, 
					SUM(vop.totalcommission) AS commission, 
					SUM(vop.tmdshippingcost) AS shipping,
					IF(CHAR_LENGTH(vop.name) > 40, CONCAT(LEFT(vop.name, 40), '...'), vop.name) AS name,
					vop.quantity,
					ovs.courier_rate,
					ovs.rto_charges,
					ovs.total_deduction,
					ovs.reverse_courier_charges,
					ovs.payment_status,
					ovs.net_settlement_amount,
					ovs.product_courier_charges
				FROM " . DB_PREFIX . "vendor_order_product vop
				LEFT JOIN " . DB_PREFIX . "order_vendor_settlement ovs 
					ON vop.order_id = ovs.order_id
				WHERE vop.order_product_id <> 0 
				AND vop.vendor_id = '" . (int)$vendor_id . "' 
				AND vop.order_status_id IN (30, 31, 12, 21, 22, 38)
				AND ovs.payment_status = 'paid'
				GROUP BY vop.order_id
				ORDER BY vop.date_modified DESC";

		$query = $this->db->query($sql);
		return $query->rows;
	}

	//----------------------------------------------------------------------------------=-=-=----------------
	
	
}
?>