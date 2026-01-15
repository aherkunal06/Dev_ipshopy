<?php
class ModelVendorCommission extends Model
{

	public $last_api_url;  // Define the property

	/* update 06-03-2019 update function and query */
	public function getTotaCommission($data, $vendor_id, $order_product_id)
	{
		$sql = "SELECT sum(totalcommission) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id='" . (int)$order_product_id . "' ";
		/* update 06-03-2019 update function and query */
		if (isset($data['vendor_id'])) {
			$sql .= " and vendor_id='" . (int)$data['vendor_id'] . "'";
		}

		$sql .= " AND vendor_id ='" . (int)$vendor_id . "'";

		$query = $this->db->query($sql);
		return $query->row['total'];
	}


	public function getOrderCurrency($order_id)
	{
		$sql = "SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id='" . (int)$order_id . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}

    //update the following code on 05-06-2025
	public function getCommissionReports($data = array())
	{	
		/* update 08-04-2019 start */
		$implode = array();

		foreach ($this->config->get('vendor_earnpayment_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}

// 		$sql="SELECT * FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0 AND order_status_id IN(" . implode(",", $implode) . ") ";
// 		/* update 08-04-2019 end */
        // update the following query with JOIN on 05-06-2025
		$sql = "SELECT vop.*, ovs.payment_status 
				FROM " . DB_PREFIX . "vendor_order_product vop
				LEFT JOIN " . DB_PREFIX . "order_vendor_settlement ovs 
					ON vop.order_id = ovs.order_id
				WHERE vop.order_product_id <> 0 
				AND vop.order_status_id IN (" . implode(",", $implode) . ")";
		// ------------------------------------------------------------------

// 		$sql = "SELECT vop.*, vop.order_id, pd.name, p.model
//         FROM " . DB_PREFIX . "vendor_order_product vop
//         LEFT JOIN " . DB_PREFIX . "product p ON (vop.product_id = p.product_id)
//         LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
//         WHERE pd.language_id = '1'
//         AND vop.order_product_id<>0 
//         AND vop.order_status_id = '5'";
        
        
        // added by sagar on 14-02-2025 for filter on commission
        
        	// Apply filters
		if (!empty($data['filter_order_id'])) {
			$sql .= " AND vop.order_id = '" . (int)$data['filter_order_id'] . "'";
		}
		
		if (!empty($data['filter_status'])) {
			$sql .= " AND vop.order_status_id = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($data['filter_product_name'])) {
			$sql .= " AND vop.name LIKE '%" . $this->db->escape($data['filter_product_name']) . "%'";
		}


		if (!empty($data['filter_date_added'])) {
			$sql .= " AND vop.DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}
		
		// Filter by Payment Status (Enhanced)
				// Ashwini Changes Code
// 		if (!empty($data['filter_payment_status'])) {
// 			if (strtolower(trim($data['filter_payment_status'])) == 'unpaid') {
// 				$sql .= " AND (ovs.payment_status = 'unpaid')";
// 			} else {
// 				$sql .= " AND LOWER(ovs.payment_status) = '" . $this->db->escape(strtolower($data['filter_payment_status'])) . "'";
// 			}
// 		}	
		// Ashwini Changes end Code 
		// --------------------------------------------------------------
		
		// Filter by Payment Status (Enhanced)
		// Ashwini Addes Code
		if (!empty($data['filter_payment_status'])) {
			if (strtolower(trim($data['filter_payment_status'])) == 'unpaid') {
				$sql .= " AND (ovs.payment_status = 'unpaid' OR ovs.payment_status IS NULL)";
			} else if (strtolower(trim($data['filter_payment_status'])) == 'paid') {
				$sql .= " AND ovs.payment_status = 'paid'";
			} else {
				$sql .= " AND (ovs.payment_status = 'paid' OR ovs.payment_status = 'unpaid' OR ovs.payment_status IS NULL)";
			}
		}
		// Ashwini End Code
		// --------------------------------------------------------------
		
		
		// filter ends 	



		if (isset($data['vendor_id'])) {
			$sql .= " and vop.vendor_id='" . (int)$data['vendor_id'] . "'";
		}

		$sort_data = array(
			'order_product_id',
			// new added by at 28-04-2025
			'order_id',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY order_product_id";
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
	public function getTotalCommissionReport($data = array())
	{
		/* update 08-04-2019 start */
		$implode = array();

		foreach ($this->config->get('vendor_earnpayment_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN " . DB_PREFIX . "order_vendor_settlement ovs ON vop.order_id = ovs.order_id WHERE vop.order_product_id<>0 AND vop.order_status_id IN(" . implode(",", $implode) . ") ";

		if (isset($data['vendor_id'])) {
			$sql .= " and vop.vendor_id='" . (int)$data['vendor_id'] . "'";
		}

		if (!empty($data['filter_payment_status'])) {
			if (strtolower(trim($data['filter_payment_status'])) == 'unpaid') {
				$sql .= " AND (ovs.payment_status = 'unpaid' OR ovs.payment_status IS NULL)";
			} else {
				$sql .= " AND LOWER(ovs.payment_status) = '" . $this->db->escape(strtolower($data['filter_payment_status'])) . "'";
			}
		}
		
		if (!empty($data['filter_status'])) {
        	$sql .= " AND vop.order_status_id = '" . (int)$data['filter_status'] . "'";
        }
        
        if (!empty($data['filter_order_id'])) {
        	$sql .= " AND vop.order_id = '" . (int)$data['filter_order_id'] . "'";
        }

		$query = $this->db->query($sql);
		return $query->row['total'];
	}


	// public function getShipwayRates($shipment_data)
	// {
	// 	$url = "https://app.shipway.com/api/getshipwaycarrierrates";

	// 	// Build API parameters dynamically
	// 	$params = array(
	// 		"fromPincode" => isset($shipment_data['fromPincode']) ? $shipment_data['fromPincode'] : '',
	//         "toPincode" => isset($shipment_data['toPincode']) ? $shipment_data['toPincode'] : '',
	//         "paymentType" => isset($shipment_data['paymentType']) ? strtolower(trim($shipment_data['paymentType'])) : 'prepaid',
	//         "Length" => isset($shipment_data['Length']) ? $shipment_data['Length'] : '0',
	//         "Breadth" => isset($shipment_data['Breadth']) ? $shipment_data['Breadth'] : '0',
	//         "Height" => isset($shipment_data['Height']) ? $shipment_data['Height'] : '0',
	//         "Weight" => isset($shipment_data['Weight']) ? $shipment_data['Weight'] : '0',
	//         "shipmentType" => "1",
	//         "cummulativePrice" => isset($shipment_data['cumulativePrice']) ? $shipment_data['cumulativePrice'] : '0'
	// 	);

	// 	// Convert array to query string
	// 	$query_string = http_build_query($params);
	// 	$this->last_api_url = $url . '?' . $query_string;

	// 	// Authentication for Shipway API
	// 	$username = "ipshopy1@gmail.com";
	// 	$api_key = "96V1f01z291K02U1jg35s5Sb93gB4QmY";
	// 	$auth_token = base64_encode("$username:$api_key");

	// 	// Send API request
	// 	$ch = curl_init();
	// 	curl_setopt($ch, CURLOPT_URL, $this->last_api_url);
	// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// 	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	// 		"Authorization: Basic $auth_token",
	// 		"Content-Type: application/json"
	// 	));

	// 	$response = curl_exec($ch);
	// 	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	// 	curl_close($ch);

	// 	// Log API response
	// 	error_log("Shipway API Request URL: " . $this->last_api_url);
	// 	error_log("Shipway API Response: " . $response);

	// 	// Return response
	// 	if ($http_code == 200) {
	// 		return json_decode($response, true);
	// 	} else {
	// 		return array("error" => "Failed to fetch data. HTTP Code: " . $http_code);
	// 	}
	// }
	// public function getShipmentData($order_id)
	// {
	// 	$query = $this->db->query("
	// 		SELECT 
	// 			v.postcode AS fromPincode,   -- Vendor's Pincode (Pickup)
	// 			o.shipping_postcode AS toPincode,  -- Customer's Pincode (Delivery)
	// 			CASE 
	// 				WHEN o.payment_code = 'cod' THEN 'cod'
	// 				ELSE 'prepaid'
	// 			END AS paymentType,
	// 			p.length AS Length,
	// 			p.width AS Breadth,
	// 			p.height AS Height,
	// 			p.weight AS Weight,
	// 			'forward' AS shipmentType,
	// 			CASE 
	// 				WHEN o.payment_code = 'cod' THEN o.total
	// 				ELSE 0
	// 			END AS cumulativePrice
	// 		FROM " . DB_PREFIX . "order o
	// 		JOIN " . DB_PREFIX . "order_product op ON o.order_id = op.order_id
	// 		JOIN " . DB_PREFIX . "product p ON op.product_id = p.product_id
	// 		JOIN " . DB_PREFIX . "vendor_order_product vop ON o.order_id = vop.order_id  
	// 		JOIN " . DB_PREFIX . "vendor v ON vop.vendor_id = v.vendor_id  
	// 		WHERE o.order_id = '" . (int)$order_id . "'
	// 	");

	// 	return $query->row;
	// }
	// public function getCourierIdByOrderId($order_id) {
	// 	// Ensure order_id is an integer
	// 	$order_id = (int)$order_id;
	// 	// Query to fetch courier_id
	// 	$query = $this->db->query("	SELECT courier_id FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "'");
	// 	// Return courier_id if found, otherwise return null
	// 	return isset($query->row['courier_id']) ? $query->row['courier_id'] : null;
	// }

	// fetch courrier rate from db 

	// public function getCourierRateByOrderId($order_id)
	// {
	// 	$query = $this->db->query("
	// 		SELECT courier_rate , carrier_id 
	// 		FROM `" . DB_PREFIX . "order_courier_rate` 
	// 		WHERE order_id = '" . (int)$order_id . "'
	// 	");

	// 	return isset($query->row['courier_rate']) ? $query->row['courier_rate'] : 0;
	// }


	//updated changes on the 15-05-2025
	public function getCourierRateByOrderId($order_id) {
		// IF(o.order_status_id IN (30, 31, 12), ocr.reverse_courier_charges, 0) changes the query
		$query = $this->db->query("
			SELECT 
				ocr.carrier_id,
				ocr.courier_rate, 
				ROUND(IF(o.order_status_id IN (21, 22, 38), ocr.rto_charges, 0)) AS rto_charge,
				ROUND(IF(ocr.reverse_courier_charges > 0, ocr.reverse_courier_charges, IF(o.order_status_id IN (30, 31, 12), ocr.reverse_courier_charges, 0))) AS
				reverse_courier_charge
			FROM `" . DB_PREFIX . "order_courier_rate` ocr
			LEFT JOIN `" . DB_PREFIX . "order` o ON ocr.order_id = o.order_id
			WHERE ocr.order_id = '" . (int)$order_id . "'
		");

		if ($query->num_rows) {
			return [
				'courier_rate'          => $query->row['courier_rate'],
				'rto_charge'            => $query->row['rto_charge'],
				'reverse_courier_charge' => $query->row['reverse_courier_charge'],
				'carrier_id'			 => $query->row['carrier_id']
			];
		} else {
			return [
				'courier_rate'          => 0,
				'rto_charge'            => 0,
				'reverse_courier_charge' => 0,
				'carrier_id' => 0
			];
		}
	}

	public function getStatusById($order_status_id)
	{
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
			FROM `" . DB_PREFIX . "order_courier_rate` 
			WHERE order_id = '" . (int)$order_id	 . "'
		");
		return isset($query->row['vendor_id']) ? $query->row['vendor_id'] : '-';
	}

	public function getPreviousBalance($vendor_id) {
		$vendor_id = (int)$vendor_id;
		// Query to sum all previous deductions for a specific vendor_id
		$query = $this->db->query("SELECT SUM(previous_deduction) AS total_deduction FROM " . DB_PREFIX . "order_vendor_settlement WHERE vendor_id = '" . $vendor_id . "'");
		// Check if the result contains a non-null total_deduction and return it, otherwise return 0
		return ($query->row && $query->row['total_deduction'] !== null) ? (float)$query->row['total_deduction'] : 0;
	}

    // updated on the 15-05-2025
	public function insertSettlementValues($order_id, $vendor_id, $courier_rate, $carrier_id, $reverse_courier_charges, $rto_charges, $totalDeductionAmount, $netSettlementAmount,$product_courier_charges)
	{
		
		// Insert the data into the database
		$this->db->query("
			INSERT INTO `" . DB_PREFIX . "order_vendor_settlement` 
			(order_id, vendor_id, courier_rate, carrier_id, reverse_courier_charges, rto_charges, total_deduction, net_settlement_amount, date_added,product_courier_charges)
			VALUES ('$order_id', '$vendor_id', '$courier_rate', '$carrier_id', '$reverse_courier_charges', '$rto_charges', '$totalDeductionAmount', '$netSettlementAmount', NOW(),$product_courier_charges)
		");

		return true;
	}

	public function checkOrderExists($order_id) {
		$query = $this->db->query("SELECT COUNT(*) as count FROM `" . DB_PREFIX . "order_vendor_settlement` WHERE order_id = '" . (int)$order_id . "'");
		return $query->row['count'] > 0;
	}
	
	// added changes on the 15-05-2025
	public function getPaymentStatusByOrderId($order_id)
	{
		$sql = "SELECT payment_status FROM `" . DB_PREFIX . "order_vendor_settlement` WHERE order_id='" . (int)$order_id . "'";
		$query = $this->db->query($sql);
		return $query->row['payment_status'];
	}
	
	
	// added function to get the product courier charges no 15-05-2025
	public function getCourierChargesByOrderId($order_id) {
		$query = $this->db->query("SELECT total_courier_charges FROM " . DB_PREFIX . "order WHERE order_id = '" . (int)$order_id . "'");
	
		if ($query->num_rows) {
			return round($query->row['total_courier_charges']);
		} else {
			return 0; // or null, depending on your requirement
		}
	}

//     //------ added the code changes on the 15-05-2025-----
// 	public function updateDeductionCharges($order_id, $vendorId)
// 	{
// 	// 	// Sanitize the input to prevent SQL injection
// 		$order_id = (int)$order_id;
	

// 	// 	// Prepare the SQL query to update the existing records
// 		$sql = "UPDATE `" . DB_PREFIX . "order_courier_rate` 
//             SET vendor_id = '$vendorId'
//             WHERE order_id = '$order_id'";

// 	// 	// Execute the query
// 		$this->db->query($sql);
// 	}


    // update the function for the account section on 15-05-2025
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
            product_courier_charges = '" . (float)$product_courier_charges . "',
            date_modified = NOW()
        WHERE 
            order_id = '" . (int)$order_id . "' 
			
    ");
	}


	// added changes on 15-05-2025
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

	
}
