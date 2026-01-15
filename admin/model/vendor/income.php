<?php
class ModelVendorincome extends Model {	
	
	public function addAmount($data) {
		
		if(isset($data['amount'])){
			$amount = $data['amount'];
		} else {
			$amount ='';
		}
		
		//added on the 15-05-2025-----
		$random_code = isset($data['payment_code']) ? $data['payment_code'] : '';

		$sql="INSERT INTO " . DB_PREFIX . "vendor_amount_pay set vendor_id='".(int)$data['vendor_id']."',payment_method='".$this->db->escape($data['payment_method'])."',amount='".(float)$amount."',comment='".$this->db->escape($data['comment'])."',reference_number= '" . $this->db->escape($random_code) . "',date_added=NOW()";
		
		$this->db->query($sql);
		
		// ✅ Callback (mail trigger) if defined
		if (isset($data['send_callback']) && is_callable($data['send_callback'])) {
			$payment_info = $this->getamountmail($data['vendor_id']);
			if ($payment_info) {
				call_user_func($data['send_callback'], $payment_info);
			}
		}
		
		return $amount;
	}
		
	public function getTotal($data,$vendor_id){
		
		$implode = array();

		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}	
		$sql = "SELECT sum(total) AS total, sum(tax*quantity) as tax FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
			
		$sql .= " AND vendor_id ='".(int)$vendor_id. "' AND order_status_id IN(5)";// . implode(",", $implode) . ")";
		
		$query = $this->db->query($sql);
		
		return $query->row['total']+$query->row['tax'];
	}

	public function getTotalCommission($data,$vendor_id){
		$implode = array();
		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}

		$sql = "SELECT sum(totalcommission) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
						
		$sql .= " AND vendor_id ='".(int)$vendor_id. "'";

				
		$sql .= " AND vendor_id ='".(int)$vendor_id. "' AND order_status_id IN(5)";// . implode(",", $implode) . ")";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getPay($vendor_id){
		
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_amount_pay WHERE vendor_id='".(int)$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getAmount($vendor_id){
		$sql = "SELECT sum(amount) AS total FROM " . DB_PREFIX . "vendor_amount_pay WHERE vendor_id='".(int)$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
				
 	public function getIncomes($data=array()){

		$implode = array();

		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}
			
// 		$sql="SELECT * FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_order_product vop ON (v.vendor_id = vop.vendor_id) WHERE v.vendor_id<>0 And  vop.order_status_id IN(" . implode(",", $implode) . ")";
        $sql = "SELECT * FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_order_product vop ON (v.vendor_id = vop.vendor_id) WHERE v.vendor_id<>0 And  vop.order_status_id IN(5)";
		
		if (!empty($data['filter_vendor'])){
		 	$sql .=" and v.vendor_id='".$this->db->escape($data['filter_vendor'])."'";
		}
		if (!empty($data['filter_date_added_from'])) {
			$sql .= " AND DATE(vop.date_added) >= '" . $data['filter_date_added_from'] . "'";
		}

		if (!empty($data['filter_date_added_to'])) {
			$sql .= " AND DATE(vop.date_added) <= '" . $data['filter_date_added_to'] . "'";
		}
		
		$sql .= " group by v.vendor_id";
		
		$sort_data = array(
			'v.vendor_id'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY v.vendor_id";
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
	
		$implode = array();

		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}
		
		$sql = "SELECT *  FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_order_product vop ON (v.vendor_id = vop.vendor_id) WHERE v.vendor_id<>0 And  vop.order_status_id IN(5)"; // . implode(",", $implode) . ")";
		
		
		if (!empty($data['filter_vendor'])){
		 	$sql .=" and v.vendor_id='".$this->db->escape($data['filter_vendor'])."'";
			}
		
		if (!empty($data['filter_date_added_from'])) {
			$sql .= " AND DATE(vop.date_added) >= '" . $data['filter_date_added_from'] . "'";
		}

		if (!empty($data['filter_date_added_to'])) {
			$sql .= " AND DATE(vop.date_added) <= '" . $data['filter_date_added_to'] . "'";
		}
		
		$sql .=" group by v.vendor_id";
		$query = $this->db->query($sql);
		if(isset($query->num_rows)){
		return $query->num_rows;
		
		}
	}

	public function getVendorTotal($vendor_id){
	
		$implode = array();

		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}
			
		$sql = "SELECT sum(total) AS total,sum(tax*quantity) as tax FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id ='".(int)$vendor_id. "' And order_status_id IN(5)"; //. implode(",", $implode) . ")";
								
		$query = $this->db->query($sql);
		 $query->row['total'] += $query->row['tax'];	
		 
		return $query->row['total'];
	}

	public function getTotalAmount($vendor_id){
		$implode = array();

		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}
		$sql = "SELECT sum(totalcommission) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
						
		$sql .= " AND vendor_id ='".(int)$vendor_id. "'";
				
		$sql .= " AND vendor_id ='".(int)$vendor_id. "' AND order_status_id IN(5)"; // . implode(",", $implode) . ")";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getShippingAmount($vendor_id){
		$implode = array();

		foreach ($this->config->get('vendor_earnpayment_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}
		
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product  WHERE vendor_id='".(int)$vendor_id."' AND  order_status_id IN(5)"; // . implode(",", $implode) . ")";
		
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getTotalShipping($data=array(),$vendor_id){
		$implode = array();

		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}
		$sql = "SELECT sum(tmdshippingcost) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
						
		$sql .= " AND vendor_id ='".(int)$vendor_id. "'";
				
		$sql .= " AND vendor_id ='".(int)$vendor_id. "' AND order_status_id IN(5)"; // . implode(",", $implode) . ")";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	//added function on the 15-05-2025 -------------------------------------------------------
	public function getCourierRateByOrderId($order_id)
	{
		$query = $this->db->query("SELECT courier_rate FROM " . DB_PREFIX . "order_courier_rate WHERE order_id = '" . (int)$order_id . "'");

		if ($query->num_rows) {
			return $query->row['courier_rate'];
		} else {
			return 0;
		}
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
	
	public function markVendorSettlementsPaidByOrderIds($order_ids = [],$transaction_code) {
		if (!empty($order_ids)) {
			$escaped_ids = array_map('intval', $order_ids); // Sanitize each ID
			$ids_string = implode(',', $escaped_ids);
	
			$this->db->query("UPDATE " . DB_PREFIX . "order_vendor_settlement 
							  SET payment_status = 'paid', 
							  reference_number = '" . $this->db->escape($transaction_code) . "',
							  payment_date = NOW()
							  WHERE order_id IN (" . $ids_string . ")");
		}
	}
	
	//update the status ids into the condition on 16/07/2025
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
				AND vop.order_status_id IN (5, 30, 31, 12, 21, 22, 38)
			ORDER BY ovs.payment_date DESC
		");
	
		return $query->rows;
	}
	
    // ---------------------------------------------------------------------------------------------
    
    
	// added the code for the rto and reverse orders on 26-06-2025
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

	// added the code for the rto and reverse orders on 26-06-2025
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
	
// 	Krishna Salve Changes - 09/07/2025
	public function getamountmail($vendor_id) {
		$query = $this->db->query("SELECT vap.*, v.firstname, v.email 
			FROM " . DB_PREFIX . "vendor_amount_pay vap 
			LEFT JOIN " . DB_PREFIX . "vendor v ON vap.vendor_id = v.vendor_id 
			WHERE vap.vendor_id = '" . (int)$vendor_id . "' 
			ORDER BY vap.pay_id DESC 
			LIMIT 1");

		if ($query->num_rows) {
			$row = $query->row;

			return [
				'pay_id'         => $row['reference_number'], 
				'vendor_id'      => $row['vendor_id'],
				'firstname'      => $row['firstname'],
				'email'          => $row['email'],
				'amount'         => round((float)$row['amount'], 2),
				'payment_method' => $row['payment_method'],
				'date_added'     => $row['date_added']
			];
		}

		return false;
	}

	
}
?>