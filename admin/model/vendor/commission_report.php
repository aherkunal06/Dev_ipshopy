<?php
class ModelVendorCommissionreport extends Model {
 	public function deleteCommissionReport($order_product_id){		
		$sql="delete  from " . DB_PREFIX . "vendor_order_product where order_product_id='".(int)$order_product_id."'";
		$query=$this->db->query($sql);
 	}
	
	public function getTotaCommission($data,$vendor_id){
		$sql = "SELECT sum(totalcommission) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
		
		if(!empty($data['filter_from']) && !empty($data['filter_to'])){
			$sql .= " and date_added>='".$data['filter_from']."' and  date_added<='".$data['filter_to']."'";
		}
		
		$sql .= " AND vendor_id ='".(int)$vendor_id. "'";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getOrderCurrency($order_id){
		$sql = "SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id='".(int)$order_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}	
				
 	public function getCommissionReports($data=array()){
		
		$implode = array();

		foreach ($this->config->get('vendor_earnpayment_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}
		
		// $sql="SELECT * FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0 AND order_status_id IN(" . implode(",", $implode) . ") "; commneted on 03-05-2025
		// Build base query with JOIN on 03-04-2025
		$sql = "SELECT vop.*, ovs.payment_status 
				FROM " . DB_PREFIX . "vendor_order_product vop
				LEFT JOIN " . DB_PREFIX . "order_vendor_settlement ovs 
					ON vop.order_id = ovs.order_id
				WHERE vop.order_product_id <> 0 
				AND vop.order_status_id IN (" . implode(",", $implode) . ")";
		// ------------------------------------------------------------------
		// added by sagar  09 - 02 -2025
		// $sql = "SELECT vop.*, vop.order_id, pd.name, p.model
        // FROM " . DB_PREFIX . "vendor_order_product vop
        // LEFT JOIN " . DB_PREFIX . "product p ON (vop.product_id = p.product_id)
        // LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
        // WHERE pd.language_id = '1'
        // AND vop.order_product_id<>0 
        // AND vop.order_status_id = '5'";

		/* 11 02 2020 */
		
		if (!empty($data['filter_vendor'])){
		 	$sql .=" and vop.vendor_id='".$this->db->escape($data['filter_vendor'])."'";
		}
		/* 11 02 2020 */
		
		if (!empty($data['filter_from'])) {
			$sql .= " AND DATE(vop.date_added) >= '" . $this->db->escape($data['filter_from']) . "'";
		}

		if (!empty($data['filter_to'])) {
			$sql .= " AND DATE(vop.date_added) <= '" . $this->db->escape($data['filter_to']) . "'";
		}
		
		//  added on 03-05-2025------
		// Filter by Order Status (filter_status)
		if (isset($data['filter_status']) && !empty($data['filter_status'])) {
			$sql .= " AND vop.order_status_id = '" . (int)$data['filter_status'] . "'";
		}
	
		// Filter by Order ID
		if (isset($data['filter_order_id']) && !empty($data['filter_order_id'])) {
			$sql .= " AND vop.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		// // Filter by Payment Status (NEW)
		// if (!empty($data['filter_payment_status'])) {
		// 	$sql .= " AND ovs.payment_status = '" . $this->db->escape($data['filter_payment_status']) . "'";
		// }

		// Filter by Payment Status (Enhanced)
		if (!empty($data['filter_payment_status'])) {
			if (strtolower(trim($data['filter_payment_status'])) == 'unpaid') {
				$sql .= " AND (ovs.payment_status IS NULL)";
			}
			 else {
				$sql .= " AND LOWER(ovs.payment_status) = '" . $this->db->escape(strtolower($data['filter_payment_status'])) . "'";
			}
		}
		// --------------------------------------------------------------

		$sort_data = array(
			'vop.order_product_id'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY vop.order_product_id";
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
		// echo "<pre>$query</pre>";
		// exit;
		return $query->rows;	
 	}
	
 	public function getTotalCommissionReport($data=array()) {
	
		$implode = array();

		foreach ($this->config->get('vendor_earnpayment_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}
		
		
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0 AND order_status_id IN(" . implode(",", $implode) . ") ";
		
		/* 11 02 2020 */
		
		if (!empty($data['filter_vendor'])){
		 	$sql .=" and vendor_id='".$this->db->escape($data['filter_vendor'])."'";
		}
		/* 11 02 2020 */
		
		if (!empty($data['filter_from'])) {
			$sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_from']) . "'";
		}

		if (!empty($data['filter_to'])) {
			$sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_to']) . "'";
		}
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	// public function getCourierRateByOrderId($order_id) {
	// 	$query = $this->db->query("
	// 		SELECT courier_rate 
	// 		FROM `" . DB_PREFIX . "order_courier_rate` 
	// 		WHERE order_id = '" . (int)$order_id . "'
	// 	");
	
	// 	return isset($query->row['courier_rate']) ? $query->row['courier_rate'] : 0;
	// }
	
	
	public function getStatusById($order_status_id) {
		$query = $this->db->query("
			SELECT name 
			FROM `" . DB_PREFIX . "order_status` 
			WHERE order_status_id = '" . (int)$order_status_id	 . "'
		");
		return isset($query->row['name']) ? $query->row['name'] : '-';
	}

	public function getVendorId($order_id)
	{
		$query = $this->db->query("
			SELECT vendor_id 
			FROM `" . DB_PREFIX . "vendor_order_product` 
			WHERE order_id = '" . (int)$order_id	 . "'
		");
		return isset($query->row['vendor_id']) ? $query->row['vendor_id'] : '-';
	}

	public function getPreviousBalance($vendor_id) {
		// Prepare the SQL query to fetch the previous balance from the vendor_balances table
		$query = $this->db->query("SELECT previous_deduction FROM " . DB_PREFIX . "order_courier_rate WHERE vendor_id = '" . (int)$vendor_id . "'");

		// Check if the previous balance exists and return it, otherwise return 0
		return isset($query->row['previous_deduction']) ? $query->row['previous_deduction'] : 0;
	}

	// added 22-03-2025
	public function insertSettlementValues($order_id, $vendor_id, $courier_rate, $carrier_id, $reverse_courier_charges, $rto_charges, $totalDeductionAmount, $netSettlementAmount, $product_courier_charges)
	{
		
		// Insert the data into the database
		$this->db->query("
			INSERT INTO `" . DB_PREFIX . "order_vendor_settlement` 
			(order_id, vendor_id, courier_rate, carrier_id, reverse_courier_charges, rto_charges, total_deduction, net_settlement_amount,date_added,product_courier_charges)
			VALUES ('$order_id', '$vendor_id', '$courier_rate', '$carrier_id', '$reverse_courier_charges', '$rto_charges', '$totalDeductionAmount', '$netSettlementAmount',NOW(),$product_courier_charges)
		");

		return true;
	}

	//updated on 23-03-2025
	public function getCourierRateByOrderId($order_id) {
		$query = $this->db->query("
			SELECT 
			    ocr.carrier_id,
				ocr.courier_rate,
				ocr.courier_name,
				ROUND(IF(o.order_status_id IN (21, 22, 38), ocr.rto_charges, 0)) AS rto_charge,
				ROUND(IF(ocr.reverse_courier_charges > 0, ocr.reverse_courier_charges, IF(o.order_status_id IN (30, 31, 12), ocr.reverse_courier_charges, 0))) AS reverse_courier_charge
			FROM `" . DB_PREFIX . "order_courier_rate` ocr
			LEFT JOIN `" . DB_PREFIX . "order` o ON ocr.order_id = o.order_id
			WHERE ocr.order_id = '" . (int)$order_id . "'
		");
	
		if ($query->num_rows) {
			return [
				'courier_rate'          => $query->row['courier_rate'],
				'rto_charge'            => $query->row['rto_charge'],
				'reverse_courier_charge' => $query->row['reverse_courier_charge'],
				'carrier_id'			 => $query->row['carrier_id'],
				'courier_name'   => $query->row['courier_name']
			];
		} else {
			return [
				'courier_rate'          => 0,
				'rto_charge'            => 0,
				'reverse_courier_charge' => 0,
				'carrier_id' => 0,
				'courier_name'   => ''
			];
		}
	}

	public function checkOrderExists($order_id) {
		$query = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "order_vendor_settlement` WHERE order_id = '" . (int)$order_id . "'");
		return $query->row['count'] > 0;
	}

	// added on 03-05-2025 ---------------------------------
	public function getPaymentStatusByOrderId($order_id)
	{
		$sql = "SELECT payment_status FROM `" . DB_PREFIX . "order_vendor_settlement` WHERE order_id='" . (int)$order_id . "'";
		$query = $this->db->query($sql);
		return $query->row['payment_status'];
	}
	// -------------------------------------------------------

	// updated on the 26-04-2025
	public function updateReverseCourierCharge($order_id, $charge) {
		
		$this->db->query("UPDATE `" . DB_PREFIX . "order_vendor_settlement` 
		SET reverse_courier_charges = '" . (float)$charge . "',
		date_modified = NOW()
		WHERE order_id = '" . (int)$order_id . "'");

		// Update in order_courier_rate table
		$this->db->query("UPDATE `" . DB_PREFIX . "order_courier_rate` 
		SET reverse_courier_charges = '" . (float)$charge . "',
		date_modified = NOW() 
		WHERE order_id = '" . (int)$order_id . "'");
	}

	// added function to get the product courier charges no 11-05-2025
	public function getCourierChargesByOrderId($order_id) {
		$query = $this->db->query("SELECT total_courier_charges FROM " . DB_PREFIX . "order WHERE order_id = '" . (int)$order_id . "'");
	
		if ($query->num_rows) {
			return round($query->row['total_courier_charges']);
		} else {
			return 0; // or null, depending on your requirement
		}
	}

	// update the function for the account section on 11-05-2025
	public function updateSettlementValues($order_id, $vendor_id, $courier_rate, $carrier_id, $reverse_courier_charges, $rto_charges, $totalDeductionAmount, $netSettlementAmount, $product_courier_charges)
	{
        $this->db->query("
            UPDATE `" . DB_PREFIX . "order_vendor_settlement`
            SET 
                courier_rate = '" . (float)$courier_rate . "',
                carrier_id = '" . (int)$carrier_id . "',
                reverse_courier_charges = '" . (float)$reverse_courier_charges . "',
                rto_charges = '" . (float)$rto_charges . "',
                total_deduction = '" . (float)$totalDeductionAmount . "',
                net_settlement_amount = '" . (float)$netSettlementAmount . "',
                product_courier_charges = '" .(float)$product_courier_charges . "',
                date_modified = NOW()
            WHERE 
                order_id = '" . (int)$order_id . "' 
        ");
    	
                // AND vendor_id = '" . (int)$vendor_id . "'
    
        return true;
    }

    // added mon the 14-05-2025
    public function getVendorPaymentDetails($order_id) {
    	// Query to fetch reference_number and payment_date from order_vendor_settlement table
    	$query = $this->db->query("
    		SELECT reference_number, payment_date
    		FROM " . DB_PREFIX . "order_vendor_settlement
    		WHERE order_id = '" . (int)$order_id . "'
    	");
    
    	// Return the result
    	// Return in structured array format
    	if ($query->num_rows) {
    		$payment_date = $query->row['payment_date'];
    		return [
    			'reference_number' => $query->row['reference_number'],
    			'payment_date'     => $payment_date ? date('d-m-Y', strtotime($payment_date)) : 'N/A'
    		];
    	} else {
    		return [
    			'reference_number' => 'N/A',
    			'payment_date'     => 'N/A'
    		];
    	}
    }
    
    
    //fcuntion to get the HSN Code added on 31-05-2025
	public function getHSNCodeByProductId($product_id) {
        $query = $this->db->query("SELECT hsn_code FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
    
        if ($query->num_rows) {
            return $query->row['hsn_code'];
        } else {
            return '';
        }
    }
    
    // Shubham Sir Changes - 05/06/2025
    
    
    
    public function getCommissionsByOrderIds($order_ids = []) {
        if (empty($order_ids)) {
            return [];
        }
    
        $order_ids = array_map('intval', $order_ids); // Sanitize input
        $ids_str = implode(',', $order_ids);
    
        $sql = "SELECT 
                    vop.*, 
                    ovs.payment_status, 
                    ovs.payment_date, 
                    ovs.reference_number, 
                    p.hsn_code
                FROM " . DB_PREFIX . "vendor_order_product vop
                LEFT JOIN " . DB_PREFIX . "order_vendor_settlement ovs 
                    ON vop.order_id = ovs.order_id AND vop.vendor_id = ovs.vendor_id
                LEFT JOIN " . DB_PREFIX . "product p 
                    ON vop.product_id = p.product_id
                WHERE vop.order_product_id <> 0 
                AND vop.order_id IN ($ids_str)";
    
        $query = $this->db->query($sql);
    
        return $query->rows;
    }


		
}
?>