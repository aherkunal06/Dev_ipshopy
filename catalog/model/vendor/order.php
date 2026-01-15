<?php
class ModelVendorOrder extends Model
{
	public function getOrderDetails($order_id)
	{
		$query = $this->db->query("
            SELECT 
                o.order_id, o.total, o.date_added, 
                o.firstname, o.lastname, o.email, o.telephone,
                o.payment_address_1, o.payment_address_2, o.payment_city, o.payment_zone, o.payment_country, o.payment_postcode,
                o.shipping_firstname , o.shipping_lastname , o.shipping_address_1, o.shipping_address_2, o.shipping_city,
                o.shipping_zone, o.shipping_country, o.shipping_postcode , 
                CASE 
                WHEN o.payment_code = 'cod' THEN 'C'
                ELSE 'P'
                END AS payment_status
            FROM `" . DB_PREFIX . "order` o
            WHERE o.order_id = '" . (int)$order_id . "'");

		return $query->row;
	}

	public function getOrderProducts($order_id)
	{
		$query = $this->db->query("
            SELECT 
                op.name, 
                op.model, 
                op.quantity, 
                op.price, 
                p.length AS box_length, 
                p.width AS box_breadth, 
                p.height AS box_height, 
                p.weight AS order_weight,
                p.volumetric_weight As order_volumetric_weight
            FROM `" . DB_PREFIX . "order_product` op
            LEFT JOIN `" . DB_PREFIX . "product` p ON op.product_id = p.product_id
            WHERE op.order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}


	public function saveShippingLabel($order_id, $label_url, $awbno, $carrier_id)
	{
		$this->db->query("UPDATE " . DB_PREFIX . "order SET shipping_label = '" . $this->db->escape($label_url) . "', awbno = '" . $this->db->escape($awbno) . "', courier_id = '" . $this->db->escape($carrier_id) . "'  WHERE order_id = '" . (int)$order_id . "'");
	}




	// for retriving warehouse id 
	public function getWarehouseId($order_id)
	{
		$query = $this->db->query("
        SELECT 
            v.shipway_warehouse_id
        FROM `" . DB_PREFIX . "vendor` v
        INNER JOIN `" . DB_PREFIX . "vendor_order_product` vop ON v.vendor_id = vop.vendor_id
        WHERE vop.order_id =  '" . (int)$order_id . "'");

		return $query->row ? $query->row['shipway_warehouse_id'] : null;
	}




	public function getShipwayRates($shipment_data)
	{
		$url = "https://app.shipway.com/api/getshipwaycarrierrates";


		// if((int)$productQuantity > 1 ){
		// 	$totalProductweight  =  (int)$productweight * (int)$productQuantity;
		// }else {
		// 	$totalProductweight = $productweight;
		// }

		// Build API parameters dynamically
		$params = array(
			"fromPincode" => isset($shipment_data['fromPincode']) ? $shipment_data['fromPincode'] : '',
			"toPincode" => isset($shipment_data['toPincode']) ? $shipment_data['toPincode'] : '',
			"paymentType" => isset($shipment_data['paymentType']) ? strtolower(trim($shipment_data['paymentType'])) : 'prepaid',
			"Length" => isset($shipment_data['Length']) ? $shipment_data['Length'] : '0',
			"Breadth" => isset($shipment_data['Breadth']) ? $shipment_data['Breadth'] : '0',
			"Height" => isset($shipment_data['Height']) ? $shipment_data['Height'] : '0',
			"Weight" => isset($shipment_data['TotalWeight']) ? $shipment_data['TotalWeight'] : '0',
			"shipmentType" => "1",
			"cummulativePrice" => isset($shipment_data['cumulativePrice']) ? $shipment_data['cumulativePrice'] : '0'
		);

		// Convert array to query string
		$query_string = http_build_query($params);
		$this->last_api_url = $url . '?' . $query_string;

		// Authentication for Shipway API
		$username = "ipshopy1@gmail.com";
		$api_key = "96V1f01z291K02U1jg35s5Sb93gB4QmY";
		$auth_token = base64_encode("$username:$api_key");

		// Send API request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->last_api_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Authorization: Basic $auth_token",
			"Content-Type: application/json"
		));

		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		// Log API response
		error_log("Shipway API Request URL: " . $this->last_api_url);
		error_log("Shipway API Response: " . $response);

		// Return response
		if ($http_code == 200) {
			return json_decode($response, true);
		} else {
			return array("error" => "Failed to fetch data. HTTP Code: " . $http_code);
		}
	}




    // update the query add greatest weight logic into the query on 22-04-2025
	public function getShipmentData($order_id)
	{
		$query = $this->db->query("
			SELECT 
				v.postcode AS fromPincode,   -- Vendor's Pincode (Pickup)
				o.shipping_postcode AS toPincode,  -- Customer's Pincode (Delivery)
				CASE 
					WHEN o.payment_code = 'cod' THEN 'cod'
					ELSE 'prepaid'
				END AS paymentType,
				p.length AS Length,
				p.width AS Breadth,
				p.height AS Height,
    			GREATEST(p.weight, p.volumetric_weight) AS Weight,
    			vop.vendor_id,
    			op.quantity,
    			(GREATEST(p.weight, p.volumetric_weight) * op.quantity) AS TotalWeight,
				'forward' AS shipmentType,
				CASE 
					WHEN o.payment_code = 'cod' THEN o.total
					ELSE 0
				END AS cumulativePrice
			FROM " . DB_PREFIX . "order o
			JOIN " . DB_PREFIX . "order_product op ON o.order_id = op.order_id
			JOIN " . DB_PREFIX . "product p ON op.product_id = p.product_id
			JOIN " . DB_PREFIX . "vendor_order_product vop ON o.order_id = vop.order_id  
			JOIN " . DB_PREFIX . "vendor v ON vop.vendor_id = v.vendor_id  
			WHERE o.order_id = '" . (int)$order_id . "'
		");

		return $query->row;
	}
// -----------------------------------------------------------------

	public function getCourierIdByOrderId($order_id)
	{
		// Ensure order_id is an integer
		$order_id = (int)$order_id;
		// Query to fetch courier_id
		$query = $this->db->query("	SELECT courier_id FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "'");
		// Return courier_id if found, otherwise return null
		return isset($query->row['courier_id']) ? $query->row['courier_id'] : null;
	}


	// for saving data in table oc_order_courier_rate 
// 	saveCourierRate updated on 20-03-2025 
	public function saveCourierRate($order_id, $vendor_id, $fowardCharge, $carrier_id , $courier_name , $rto_charges)
	{
		// Ensure all inputs are properly formatted
		$order_id = (int)$order_id;
		$vendor_id = (int)$vendor_id;
		$fowardCharge = (float)$fowardCharge;
		$carrier_id = (int)$carrier_id;

		// Insert or update the courier rate in the database
		$this->db->query("
			INSERT INTO `" . DB_PREFIX . "order_courier_rate` (order_id, vendor_id, courier_rate, carrier_id, courier_name, rto_charges)
			VALUES ('$order_id', '$vendor_id', '$fowardCharge','$carrier_id', '$courier_name' , '$rto_charges')
			ON DUPLICATE KEY UPDATE 
			courier_rate = VALUES(courier_rate), 
			carrier_id = VALUES(carrier_id)
		");

		return true;
	}

	// for updating rates 

	// public function updateDeductionCharges($order_id, $reverse_courier_charges, $rto_charges, $totalDeduction, $payableAmount, $previous_deduction)
	// {
	// 	// Sanitize the input to prevent SQL injection
	// 	$order_id = (int)$order_id;
	// 	$reverse_courier_charges = $reverse_courier_charges;
	// 	$rto_charges = $rto_charges;
	// 	$totalDeduction = $totalDeduction;
	// 	$payableAmount = $payableAmount;
	// 	$previous_deduction = $previous_deduction;

	// 	// Prepare the SQL query to update the existing records
	// 	$sql = "UPDATE `" . DB_PREFIX . "order_courier_rate` 
    //         SET reverse_courier_charges = '$reverse_courier_charges', 
    //             rto_charges = '$rto_charges', 
    //             total_deduction = '$totalDeduction', 
    //             payble_amount = '$payableAmount', 
    //             previous_deduction = '$previous_deduction' 
    //         WHERE order_id = '$order_id'";

	// 	// Execute the query
	// 	$this->db->query($sql);
	// }



	public function getBreachedOrders()
	{
		$sql = "SELECT o.order_id, o.firstname, o.lastname, o.total, o.date_added, os.name AS status_name 
                FROM " . DB_PREFIX . "vendor_order_product vop 
                LEFT JOIN " . DB_PREFIX . "order o ON (vop.order_id = o.order_id)
                LEFT JOIN " . DB_PREFIX . "order_status os ON (vop.order_status_id = os.order_status_id)
                WHERE vop.order_status_id = 17 
                AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                ORDER BY o.date_added DESC";

		$query = $this->db->query($sql);
		return $query->rows;
	}
}
