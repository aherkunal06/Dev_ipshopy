<?php
class ModelVendorOrderReport extends Model {
	
	public function getTotalReport($data) {
	
		$implode = array();
		
		$vendorstatus = $this->config->get('vendor_showorder_status');
		$defaultstatus = $this->config->get('config_complete_status');
		
		if(!empty($vendorstatus)){
			foreach ($vendorstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}
		}
			
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) WHERE vop.vendor_id='".(int)$this->vendor->getId()."' AND vop.order_status_id IN(" . implode(",", $implode) . ")";
		
		
		if (isset($data['filter_seller'])){
		 	$sql .=" AND vop.vendor_id like '".$this->db->escape($data['filter_seller'])."%'";
		}

		if (isset($data['filter_customer'])){
		 	$sql .=" AND o.customer_id like '".$this->db->escape($data['filter_customer'])."%'";
		}
		
		if (isset($data['filter_status'])){
		 	$sql .=" AND vop.order_status_id like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		if (isset($data['filter_order_id'])){
		 	$sql .=" AND o.order_id like '".$this->db->escape($data['filter_order_id'])."%'";
		}
		
		if (isset($data['filter_date'])){
		 	$sql .=" AND vop.date_added like '".$this->db->escape($data['filter_date'])."%'";
		}
		
		$sql .= " GROUP by vop.order_id";
		
		$query = $this->db->query($sql);
		
		return $query->num_rows;
	}
	
	public function getReports_n($data = []) {
		$sql = "SELECT vop.*, o.firstname, o.lastname,o.shipping_manifest, o.shipping_code, pd.name AS product_name, vop.date_added 
				FROM " . DB_PREFIX . "vendor_order_product vop 
				LEFT JOIN " . DB_PREFIX . "order o ON (vop.order_id = o.order_id) 
				LEFT JOIN " . DB_PREFIX . "order_product op ON (vop.order_product_id = op.order_product_id) 
				LEFT JOIN " . DB_PREFIX . "product_description pd ON (op.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "') 
				LEFT JOIN " . DB_PREFIX . "order_status os ON (vop.order_status_id = os.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') 
				WHERE vop.vendor_id = '" . (int)$this->vendor->getId() . "'";

		// Filter by order_id
		if (!empty($data['filter_order_id'])) {
			$sql .= " AND vop.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		// Filter by customer name
		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		// Filter by date
		if (!empty($data['filter_date'])) {
			$sql .= " AND DATE(vop.date_added) = '" . $this->db->escape($data['filter_date']) . "'";
		}

		// Filter by order statuses (by name)
		if (!empty($data['filter_order_statuses']) && is_array($data['filter_order_statuses'])) {
			$statuses = array_map([$this->db, 'escape'], $data['filter_order_statuses']);
			$statuses = array_map(function($s) { return "'" . $s . "'"; }, $statuses);
			$sql .= " AND os.name IN (" . implode(',', $statuses) . ")";
		}

		// Sorting
		$sort_data = ['vop.order_id', 'o.firstname', 'vop.date_added'];
		if (!empty($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY vop.order_id";
		}

		if (!empty($data['order']) && $data['order'] == 'ASC') {
			$sql .= " ASC";
		} else {
			$sql .= " DESC";
		}

		// Pagination
		if (isset($data['start']) || isset($data['limit'])) {
			$start = (int)$data['start'];
			$limit = (int)$data['limit'];

			if ($start < 0) $start = 0;
			if ($limit < 1) $limit = 20;

			$sql .= " LIMIT " . $start . "," . $limit;
		}

		$query = $this->db->query($sql);
		return $query->rows;
	}
	

    public function getReports($data) {
    		$vendorstatus  = $this->config->get('vendor_showorder_status');
    		$defaultstatus = $this->config->get('config_complete_status');
    
    		$implode = [];
    
    		if (!empty($vendorstatus)) {
    			foreach ($vendorstatus as $order_status_id) {
    				$implode[] = "'" . (int)$order_status_id . "'";
    			}
    		} else {
    			foreach ($defaultstatus as $order_status_id) {
    				$implode[] = "'" . (int)$order_status_id . "'";
    			}
    		}
    
    		// Language ID for status name
    		$language_id = (int)$this->config->get('config_language_id');
    
    		// SQL base with join to order_status table for status name filtering
    		$sql = "SELECT vop.*, o.*, vop.order_status_id 
    				FROM " . DB_PREFIX . "vendor_order_product vop 
    				LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id)
    				LEFT JOIN `" . DB_PREFIX . "order_status` os ON (vop.order_status_id = os.order_status_id AND os.language_id = " . $language_id . ") 
    				WHERE vop.order_status_id IN(" . implode(",", $implode) . ") 
    				AND vop.order_id IS NOT NULL";
    
    		// Optional filters
    		if (!empty($data['filter_seller'])) {
    			$sql .= " AND vop.vendor_id LIKE '" . $this->db->escape($data['filter_seller']) . "%'";
    		}
    
    		if (!empty($data['filter_customer'])) {
    			$sql .= " AND o.customer_id LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
    		}
    
    		if (!empty($data['filter_status'])) {
    			$sql .= " AND vop.order_status_id LIKE '" . $this->db->escape($data['filter_status']) . "%'";
    		}
    
    		if (!empty($data['filter_order_status'])) {
    			// Filter by status name
    			$sql .= " AND LOWER(os.name) = '" . $this->db->escape(strtolower($data['filter_order_status'])) . "'";
    		}
    
    		if (!empty($data['filter_order_id'])) {
    			$sql .= " AND vop.order_id LIKE '" . $this->db->escape($data['filter_order_id']) . "%'";
    		}
    
    		if (!empty($data['filter_date'])) {
    			$sql .= " AND vop.date_added LIKE '" . $this->db->escape($data['filter_date']) . "%'";
    		}
    
    		// Only for the logged-in vendor
    		$sql .= " AND vop.vendor_id = '" . (int)$this->vendor->getId() . "'";
    
    		// Group by order
    		$sql .= " GROUP BY vop.order_id";
    
    		// Sorting
    		$sort_data = [
    			'vop.order_product_id',
    			'vop.name',
    			'vop.date_added',
    			'vop.order_id',
    			'o.firstname'
    		];
    
    		if (!empty($data['sort']) && in_array($data['sort'], $sort_data)) {
    			$sql .= " ORDER BY " . $data['sort'];
    		} else {
    			$sql .= " ORDER BY vop.order_id";
    		}
    
    		$sql .= (!empty($data['order']) && $data['order'] == 'DESC') ? " DESC" : " ASC";
    
    		// Pagination
    		if (isset($data['start']) || isset($data['limit'])) {
    			$data['start'] = isset($data['start']) && $data['start'] >= 0 ? (int)$data['start'] : 0;
    			$data['limit'] = isset($data['limit']) && $data['limit'] > 0 ? (int)$data['limit'] : 20;
    
    			$sql .= " LIMIT " . $data['start'] . "," . $data['limit'];
    		}
    
    		$query = $this->db->query($sql);
    		return $query->rows;
    	}
    //------------------------------------------------------------------- 	
	public function getAdminOrderStatuss ($order_id) {
		$sql="SELECT * FROM `" . DB_PREFIX . "order` where order_id='".(int)$order_id."'";
		$query = $this->db->query($sql);
		
		return $query->row;
	}	
	
	public function getOrderStatus ($order_status_id) {
		/* 27 04 2020 update query */
		$sql="SELECT * FROM `" . DB_PREFIX . "order_status` where order_status_id='".(int)$order_status_id."' AND language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql);
		
		return $query->row;
	}
	
	public function getAdminOrderStatus ($order_status_id) {
		/* 27 04 2020 update query */
		$sql="SELECT * FROM `" . DB_PREFIX . "order_status` where order_status_id='".$order_status_id."'  AND language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql);
		
		return $query->row;
	}	


   // updated on 07-03-2025 
    public function updateBreachedOrders() {
        // Fetch orders older than 72 hours that are still in pending or processing state
		
		$sql = "SELECT vop.order_id 
				FROM " . DB_PREFIX . "vendor_order_product AS vop
				JOIN " . DB_PREFIX . "order AS o ON vop.order_id = o.order_id
				WHERE vop.order_status_id IN (2)
				AND vop.date_added < DATE_SUB(NOW(), INTERVAL 72 HOUR)";

		//AND vendor_id = " . $vendorId . "
        $query = $this->db->query($sql);

        if ($query->num_rows) {
            foreach ($query->rows as $order) {
                $this->changeOrderStatus($order['order_id'], 17); // Change to Breached (ID: 17)
            }
        }

        return "Breach order status updated successfully!"; // return $query->num_rows; // Return the number of affected orders
    }

	// Get the orders with status Breached
	public function getBreachedOrders() {
			$sql = "SELECT o.order_id,
					SUM(op.quantity) AS total_quantity,
					CONCAT(o.firstname, ' ', o.lastname) AS customer_name,
					o.total,
					o.date_added,
					o.order_status_id AS status
					FROM " . DB_PREFIX . "order o
					JOIN " . DB_PREFIX . "order_product op ON o.order_id = op.order_id
					WHERE o.order_status_id = '17'
					GROUP BY o.order_id, o.firstname, o.lastname, o.total, o.date_added, o.order_status_id";
	
			$query = $this->db->query($sql);
			return $query->rows;
		}
			
	// Update on 20-02-2025
	public function updateOrderStatusFromShipway() {
		$apiUrl = "https://app.shipway.com/api/getorders";
		$username = "ipshopy1@gmail.com";
		$licenseKey = "96V1f01z291K02U1jg35s5Sb93gB4QmY";
	
		// Fetch orders update the logic to get only orders not present into the clikpost on 14-06-2025
		$ordersQuery = $this->db->query("
			SELECT DISTINCT vop.order_id, vop.order_status_id 
			FROM " . DB_PREFIX . "vendor_order_product vop
			INNER JOIN " . DB_PREFIX . "order o ON vop.order_id = o.order_id
			WHERE vop.order_status_id NOT IN (0,1,2,5,7,10,14,15,17)
			AND vop.order_id NOT IN (
                SELECT ipshopy_order_id FROM " . DB_PREFIX . "clickpost_order
                WHERE ipshopy_order_id IS NOT NULL
            )
		");
	
		if (!$ordersQuery->num_rows) {
			return "No orders found for update.";
		}
	
		// Store valid order IDs and their current status
		$validOrders = [];
		foreach ($ordersQuery->rows as $row) {
			$validOrders[$row['order_id']] = (int)$row['order_status_id'];
		}
	
		// Step 2: Call Shipway API
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apiUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$username:$licenseKey");
	
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
	
		if ($httpCode != 200) {
			return "Failed to fetch data from Shipway API.";
		}
	
		$data = json_decode($response, true);
		if (!$data || empty($data['message'])) {
			return "No order data found in API response.";
		}
	
		// Step 3: Process Shipway response and update order status
		foreach ($data['message'] as $order) {
			$orderId = (int)$order['order_id'];
	
			// Skip if order_id is not in our fetched list
			if (!isset($validOrders[$orderId])) {
				continue;
			}
	
			// Get current order status from OpenCart
			$currentStatusId = $validOrders[$orderId];
	
			// Fetch status_id for the shipment status from Shipway
			$shipmentStatus = $order['shipment_status_name'];
			$trackingNumber = $order['tracking_number'];
	
			$statusQuery = $this->db->query("
				SELECT order_status_id 
				FROM " . DB_PREFIX . "order_status 
				WHERE name = '" . $this->db->escape($shipmentStatus) . "'
			");
	
			if (!$statusQuery->num_rows) {
				continue;
			}
	
			$shipwayStatusId = (int)$statusQuery->row['order_status_id'];
	
			// If the status is already the same, skip updating
			if ($shipwayStatusId === $currentStatusId) {
				continue;
			}
	
			// Fetch vendor_id
			$vendorQuery = $this->db->query("
				SELECT vendor_id 
				FROM " . DB_PREFIX . "vendor_order_product 
				WHERE order_id = '" . (int)$orderId . "'
			");
			$vendorId = $vendorQuery->num_rows ? (int)$vendorQuery->row['vendor_id'] : 0;
	
			// Update order status in oc_order
			$this->db->query("
				UPDATE " . DB_PREFIX . "order 
				SET order_status_id = '" . $shipwayStatusId . "', 
					tracking = '" . $this->db->escape($trackingNumber) . "', 
					date_modified = NOW() 
				WHERE order_id = '" . $orderId . "'
			");
	
			// Insert into oc_order_history
			$this->db->query("
				INSERT INTO " . DB_PREFIX . "order_history 
				SET order_id = '" . $orderId . "', 
					order_status_id = '" . $shipwayStatusId . "', 
					notify = 1, 
					comment = '" . $this->db->escape($shipmentStatus) . "', 
					date_added = NOW()
			");
	
			// Update order status in vendor_order_product
			$this->db->query("
				UPDATE " . DB_PREFIX . "vendor_order_product 
				SET order_status_id = '" . $shipwayStatusId . "', 
					tracking = '" . $this->db->escape($trackingNumber) . "', 
					date_modified = NOW() 
				WHERE order_id = '" . $orderId . "' 
				AND vendor_id = '" . (int)$vendorId . "'
			");
	
			// Insert into oc_order_vendorhistory
			$this->db->query("
				INSERT INTO " . DB_PREFIX . "order_vendorhistory 
				SET order_id = '" . $orderId . "', 
					order_status_id = '" . $shipwayStatusId . "',
					comment = '" . $this->db->escape($shipmentStatus) . "',
					vendor_id = '" . (int)$vendorId . "',
					date_added = NOW()
			");
		}
	
		return "Order statuses updated successfully.";
	}

    // updated on 07-03-2025
    
    // Change the order status updated on 20-05-2025
    public function changeOrderStatus($order_id, $status_id) {
		// Status Comments Mapping
		$status_comments = [
			8  => "Label Generated Successfully",
			13 => "Order Ready to Dispatch",
			16 => "Manifest Successfully", 
			1  => "Order in Pending State",
			7  => "Order Canceled Successfully",
			17 => "Order Breach After 72 hours",
			5  => "Order Completed Successfully",
			12 => "Return Sucessfully" // added code changes for product return on 20-05-2025
			
		];
	
		// Get the appropriate comment based on status_id
		$comment = isset($status_comments[$status_id]) ? $status_comments[$status_id] : "Status Updated";
	
		// Fetch vendor_id
		$vendorQuery = $this->db->query("SELECT vendor_id FROM " . DB_PREFIX . "vendor_order_product WHERE order_id = '" . (int)$order_id . "'");
		$vendorId = $vendorQuery->num_rows ? (int)$vendorQuery->row['vendor_id'] : 0;
	
		// Update order status in vendor_order_product
		$this->db->query("UPDATE " . DB_PREFIX . "vendor_order_product 
						  SET order_status_id = '" . (int)$status_id . "', date_modified = NOW() 
						  WHERE order_id = '" . (int)$order_id . "' 
						  AND vendor_id = '" . $vendorId . "'");
	
    	// Update the order status in oc_order added on 09-04-2025----------------------------------------------------
		$update_order_sql = "UPDATE " . DB_PREFIX . "order 
							SET order_status_id = '" . (int)$status_id . "', 
							date_modified = NOW()";

		if ((int)$status_id === 16) {
			$update_order_sql .= ", manifest_date = NOW()";
		}

		$update_order_sql .= " WHERE order_id = '" . (int)$order_id . "'";

		$this->db->query($update_order_sql);
		//-----------------------------------------------------------------------------------------------------------------

		// Log the status change in order_history
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history 
						  SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$status_id . "', 
						  notify = 1, comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
	
		// Insert into oc_order_vendorhistory (Vendor Order History)
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_vendorhistory SET 
						  order_id = '" . (int)$order_id . "', 
						  order_status_id = '" . (int)$status_id . "', 
						  vendor_id = '" . (int)$vendorId . "',
						  comment = '" . $this->db->escape($comment) . "', 
						  date_added = NOW()");
	}
	

    public function getManifestOrders($manifest_id) {
       $query = $this->db->query("
				SELECT 
					o.order_id, 
					CONCAT(o.firstname, ' ', o.lastname) AS customer_name, 
					CONCAT(o.shipping_address_1, ', ', o.shipping_city, ', ', o.shipping_zone, ', ', o.shipping_country) AS customer_address,
					op.name AS product_name,
					op.quantity,
					op.total AS amount,
					o.manifest_id,
					o.awbno,
					o.payment_code,
					o.email,
					o.date_added AS order_date,
					ocr.courier_name
				FROM " . DB_PREFIX . "order o
				LEFT JOIN " . DB_PREFIX . "order_product op ON o.order_id = op.order_id
				LEFT JOIN " . DB_PREFIX . "product p ON op.product_id = p.product_id
				LEFT JOIN `" . DB_PREFIX . "order_courier_rate` ocr ON o.order_id = ocr.order_id
				WHERE o.manifest_id = '" . (int)$manifest_id . "'
			");



		return $query->rows;
	}

	public function assignOrderToManifest($order_id, $manifest_id ) {
		$this->db->query("UPDATE " . DB_PREFIX . "order 
		SET manifest_id = '" . (int)$manifest_id . "'
		WHERE order_id = '" . (int)$order_id . "'");
	}

    // 	updated on 20-03-2025
	public function saveManifest($order_id, $manifest_id , $filename ) {
			$this->db->query("UPDATE " . DB_PREFIX . "order 
   			 SET shipping_manifest = '" . $filename . "', 
    	    manifest_date = NOW() 
 		    WHERE order_id = '" . (int)$order_id . "' 
   			AND manifest_id = '" . (int)$manifest_id . "'");
	}


	// added on 7 feb 2024
	public function getTodaysReport($filter_data) {
		$sql = "SELECT COUNT(DISTINCT `order_id`) AS total FROM `" . DB_PREFIX . "vendor_order_product` 
				WHERE `vendor_id` = '" . (int)$filter_data['vendor_id'] . "' AND order_status_id != '0'";
	
		if (!empty($filter_data['date_added'])) {
			$sql .= " AND DATE(`date_added`) = DATE('" . $this->db->escape($filter_data['date_added']) . "')";
		} else {
			$sql .= " AND DATE(`date_added`) = CURDATE()"; // Default to today's date if not provided
		}
	
		$query = $this->db->query($sql);
	
		return isset($query->row['total']) ? $query->row['total'] : 0;
	}
	
	
	public function getTotalCompletedOrders($data) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_status_id = '5'";

		if (!empty($data['vendor_id'])) {
			$sql .= " AND vendor_id = '" . (int)$data['vendor_id'] . "'";
		}
	
		// Debug: Log the SQL query to see if it's correct
		error_log("Completed Orders Query: " . $sql);
	
		$query = $this->db->query($sql);
		return $query->row ? $query->row['total'] : 0;
    }
    
	public function getTotalCompletedOrdersSale($data) {
	
		$sql = "SELECT IFNULL(SUM( ROUND(total , 0)), 0) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_status_id = '5'";

		if (!empty($data['vendor_id'])) {
			$sql .= " AND vendor_id = '" . (int)$data['vendor_id'] . "'";
		}
	
		// Debug: Log the SQL query
		error_log("Completed Orders Query: " . $sql);
	
		$query = $this->db->query($sql);
		return isset($query->row['total']) ? $query->row['total'] : 0;
    }
    
	public function getTotalShippedOrders($data) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_status_id = '3'"; // Assuming status_id 3 is 'Shipped'
	
		if (!empty($data['vendor_id'])) {
			$sql .= " AND vendor_id = '" . (int)$data['vendor_id'] . "'";
		}
	
		// Debug: Log the SQL query to check correctness
		error_log("Shipped Orders Query: " . $sql);
	
		$query = $this->db->query($sql);
		return $query->row ? $query->row['total'] : 0;
	}
	
	//Get shipping label
	public function getShippingLabel($order_id) {
		$query = $this->db->query("SELECT shipping_label FROM " . DB_PREFIX . "order WHERE order_id = '" . (int)$order_id . "'");
	
		if ($query->num_rows) {
			return $query->row['shipping_label']; // Ensure this field exists in your DB
		} else {
			return false;
		}
	}

	// fetch manifest list
    // 	update fetchManifestData query on 20-03-2025

	public function fetchManifestData($vendor_id) {
		$query = $this->db->query(" 
			SELECT 
				o.manifest_id,
				COUNT(o.order_id) AS order_count,
				manifest_date,
				o.shipping_manifest
			FROM " . DB_PREFIX . "order o
			JOIN " . DB_PREFIX . "vendor_order_product vop ON o.order_id = vop.order_id
			WHERE o.manifest_id IS NOT NULL 
			AND o.shipping_manifest IS NOT NULL
			AND vop.vendor_id = '" . (int)$vendor_id . "' 
			GROUP BY o.manifest_id
		");

	
		return $query->rows;
	}
	
	//   updated on 07-03-2025 
    // for cancel order
	public function updateCanceledOrders() {
		// Fetch orders that have been in 'Breached' (ID: 17) status for more than 1 days updated logic on the 17-07-2025
		$sql = "SELECT vop.order_id 
				FROM " . DB_PREFIX . "vendor_order_product AS vop
				JOIN " . DB_PREFIX . "order AS o ON vop.order_id = o.order_id
				WHERE vop.order_status_id = 17
				AND vop.date_modified < DATE_SUB(NOW(), INTERVAL 1 DAY)";
			
		$query = $this->db->query($sql);
	
		if ($query->num_rows) {
			foreach ($query->rows as $order) {
				$this->changeOrderStatus($order['order_id'], 7); // Change to Canceled (ID: 7)
			}
		}
	
		return "Cancel order status updated successfully!";// return $query->num_rows; // Return the number of affected orders
	}
	
    // updated on 07-03-2025 
    //Update the order Status Complete after 192 hours(8 days)
	public function updateOrderStatusAfter192Hours() {
		$db = $this->db; // OpenCart database connection
	
		// Get 'Complete' status ID
		$completeStatusQuery = $db->query("SELECT order_status_id FROM " . DB_PREFIX . "order_status WHERE name = 'Complete' LIMIT 1");
		$completeStatusId = (int)$completeStatusQuery->row['order_status_id'];
		
		$ordersQuery = $db->query("
		SELECT o.order_id, o.date_modified, v.vendor_id
		FROM " . DB_PREFIX . "order o
		LEFT JOIN " . DB_PREFIX . "vendor_order_product v ON o.order_id = v.order_id
		WHERE o.order_status_id = 18
		AND TIMESTAMPDIFF(HOUR, o.date_modified, NOW()) >= 192
		");

		if ($ordersQuery->num_rows > 0) {
			foreach ($ordersQuery->rows as $order) {
				$orderId = (int)$order['order_id'];
				$vendorId = isset($order['vendor_id']) ? (int)$order['vendor_id'] : 0;

				// Update order status to 'Complete' in `order` table
				$db->query("
					UPDATE " . DB_PREFIX . "order 
					SET order_status_id = $completeStatusId, date_modified = NOW() 
					WHERE order_id = $orderId
				");
	
				// Add history log for order status update in `order_history`
				$db->query("
					INSERT INTO " . DB_PREFIX . "order_history (order_id, order_status_id, notify, comment, date_added)
					VALUES ($orderId, $completeStatusId, 1, 'Order Completed Successfully', NOW())
				");
	
				// ⿡ Update `vendor_order_product` with 'Complete' status and date_modified
				$db->query("
					UPDATE " . DB_PREFIX . "vendor_order_product 
					SET order_status_id = $completeStatusId, date_modified = NOW() 
					WHERE order_id = $orderId AND vendor_id = $vendorId
				");
	
				// ⿢ Insert a new record into `order_vendorhistory`
				$db->query("
					INSERT INTO " . DB_PREFIX . "order_vendorhistory (order_id, order_status_id, vendor_id, comment, date_added)
					VALUES ($orderId, $completeStatusId, $vendorId, 'Order Completed Successfully', NOW())
				");
			}
		}

		return "Complete order status updated successfully!";
	}
	
	// 24-04-2025 estimated courier charges nikita updated changes----------------------------------
    public function saveOrderCharges($order_id, $estimated_courier_charges, $net_settlement) {
        $this->db->query("UPDATE " . DB_PREFIX . "order SET date_modified = Now(), estimated_courier_charges = '" . (float)$estimated_courier_charges . "', net_settlement = '" . (float)$net_settlement . "' WHERE order_id = '" . (int)$order_id . "'");
    }
    // ---------------------------------------------------------------------------
   
   
    // ----- start here added following function for the pagiation or search filter on 19-05-2025
   public function getTotalProcessingOrders($data = array()) {
		// Replace this with your actual "Pending" status ID
		$pending_status_id = 2;

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product vop 
				LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) 
				WHERE vop.vendor_id = '" . (int)$this->vendor->getId() . "' 
				AND vop.order_status_id = '" . (int)$pending_status_id . "'";

		if (!empty($data['filter_seller'])) {
			$sql .= " AND vop.vendor_id = '" . (int)$data['filter_seller'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND o.customer_id = '" . (int)$data['filter_customer'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_from'])) {
			$sql .= " AND DATE(vop.date_added) >= '" . $this->db->escape($data['filter_from']) . "'";
		}

		if (!empty($data['filter_to'])) {
			$sql .= " AND DATE(vop.date_added) <= '" . $this->db->escape($data['filter_to']) . "'";
		}

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	// for get the rtd status 
    public function getTotalRtdOrders($data = array()) {
		// Replace this with your actual RTD status ID
		$rtd_status_id = 8; // Example: RTD = 5 (update as per your DB config)

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product vop 
				LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) 
				WHERE vop.vendor_id = '" . (int)$this->vendor->getId() . "' 
				AND vop.order_status_id = '" . (int)$rtd_status_id . "'";

		if (!empty($data['filter_seller'])) {
			$sql .= " AND vop.vendor_id = '" . (int)$data['filter_seller'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND o.customer_id = '" . (int)$data['filter_customer'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_from'])) {
			$sql .= " AND DATE(vop.date_added) >= '" . $this->db->escape($data['filter_from']) . "'";
		}

		if (!empty($data['filter_to'])) {
			$sql .= " AND DATE(vop.date_added) <= '" . $this->db->escape($data['filter_to']) . "'";
		}

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	// ----

	// for cancel

	public function getTotalCancelledOrders($data = array()) {
		// Replace this with your actual "Cancelled" status ID
		$cancel_status_id = 7; // Example: Cancelled = 7 (update according to your database)

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product vop 
				LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) 
				WHERE vop.vendor_id = '" . (int)$this->vendor->getId() . "' 
				AND vop.order_status_id = '" . (int)$cancel_status_id . "'";

		if (!empty($data['filter_seller'])) {
			$sql .= " AND vop.vendor_id = '" . (int)$data['filter_seller'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND o.customer_id = '" . (int)$data['filter_customer'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_from'])) {
			$sql .= " AND DATE(vop.date_added) >= '" . $this->db->escape($data['filter_from']) . "'";
		}

		if (!empty($data['filter_to'])) {
			$sql .= " AND DATE(vop.date_added) <= '" . $this->db->escape($data['filter_to']) . "'";
		}

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	
	// -----=========-
	// for completed===----
	public function getTotalCompleteOrders($data = array()) {
	// Replace this with your actual "Completed" status ID
		$completed_status_id = 5; // Example: Completed = 3 (update according to your database)

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product vop 
				LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) 
				WHERE vop.vendor_id = '" . (int)$this->vendor->getId() . "' 
				AND vop.order_status_id = '" . (int)$completed_status_id . "'";

		if (!empty($data['filter_seller'])) {
			$sql .= " AND vop.vendor_id = '" . (int)$data['filter_seller'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND o.customer_id = '" . (int)$data['filter_customer'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_from'])) {
			$sql .= " AND DATE(vop.date_added) >= '" . $this->db->escape($data['filter_from']) . "'";
		}

		if (!empty($data['filter_to'])) {
			$sql .= " AND DATE(vop.date_added) <= '" . $this->db->escape($data['filter_to']) . "'";
		}

		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	// ---==
	public function getTotalManifestOrders($data = array()) {
	// Replace this with your actual "Manifest" status ID
		$manifest_status_id = 16; // Example: Manifest = 6 (update as per your DB)

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product vop 
				LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) 
				WHERE vop.vendor_id = '" . (int)$this->vendor->getId() . "' 
				AND vop.order_status_id = '" . (int)$manifest_status_id . "'";

		if (!empty($data['filter_seller'])) {
			$sql .= " AND vop.vendor_id = '" . (int)$data['filter_seller'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND o.customer_id = '" . (int)$data['filter_customer'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_from'])) {
			$sql .= " AND DATE(vop.date_added) >= '" . $this->db->escape($data['filter_from']) . "'";
		}

		if (!empty($data['filter_to'])) {
			$sql .= " AND DATE(vop.date_added) <= '" . $this->db->escape($data['filter_to']) . "'";
		}

		$query = $this->db->query($sql);
		return $query->row['total'];
	}


	// -====

	// fro brached order=------------
	public function getTotalBreachedOrders($data = array()) {
		// Replace this with your actual "Breached" status ID
		$breached_status_id = 17; // Example: Breached = 8 (update as per your DB)

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product vop 
				LEFT JOIN `" . DB_PREFIX . "order` o ON (vop.order_id = o.order_id) 
				WHERE vop.vendor_id = '" . (int)$this->vendor->getId() . "' 
				AND vop.order_status_id = '" . (int)$breached_status_id . "'";

		if (!empty($data['filter_seller'])) {
			$sql .= " AND vop.vendor_id = '" . (int)$data['filter_seller'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND o.customer_id = '" . (int)$data['filter_customer'] . "'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_from'])) {
			$sql .= " AND DATE(vop.date_added) >= '" . $this->db->escape($data['filter_from']) . "'";
		}

		if (!empty($data['filter_to'])) {
			$sql .= " AND DATE(vop.date_added) <= '" . $this->db->escape($data['filter_to']) . "'";
		}

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	// --------end here============---------------------------------------------------------------------------------
	
// 	public function getTotalTrackOrders($data) {
// 		$vendorstatus  = $this->config->get('vendor_showorder_status');
// 		$defaultstatus = $this->config->get('config_complete_status');

// 		$implode = [];

// 		if (!empty($vendorstatus)) {
// 			foreach ($vendorstatus as $order_status_id) {
// 				$implode[] = "'" . (int)$order_status_id . "'";
// 			}
// 		} else {
// 			foreach ($defaultstatus as $order_status_id) {
// 				$implode[] = "'" . (int)$order_status_id . "'";
// 			}
// 		}

// 		$language_id = (int)$this->config->get('config_language_id');

// 		$sql = "SELECT COUNT(*) AS total FROM (
// 			SELECT vop.order_id
// 			FROM " . DB_PREFIX . "vendor_order_product vop 
// 			LEFT JOIN " . DB_PREFIX . "order o ON (vop.order_id = o.order_id)
// 			LEFT JOIN " . DB_PREFIX . "order_status os ON (vop.order_status_id = os.order_status_id AND os.language_id = " . $language_id . ")
// 			WHERE vop.order_status_id IN (" . implode(",", $implode) . ") 
// 			AND vop.order_id IS NOT NULL";

// 		// Apply filters (same as getReports)
// 		if (!empty($data['filter_seller'])) {
// 			$sql .= " AND vop.vendor_id LIKE '" . $this->db->escape($data['filter_seller']) . "%'";
// 		}

// 		if (!empty($data['filter_customer'])) {
// 			$sql .= " AND o.customer_id LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
// 		}

// 		if (!empty($data['filter_status'])) {
// 			$sql .= " AND vop.order_status_id LIKE '" . $this->db->escape($data['filter_status']) . "%'";
// 		}

// 		if (!empty($data['filter_order_status'])) {
// 			$sql .= " AND LOWER(os.name) = '" . $this->db->escape(strtolower($data['filter_order_status'])) . "'";
// 		}

// 		if (!empty($data['filter_order_id'])) {
// 			$sql .= " AND vop.order_id LIKE '" . $this->db->escape($data['filter_order_id']) . "%'";
// 		}

// 		if (!empty($data['filter_date'])) {
// 			$sql .= " AND vop.date_added LIKE '" . $this->db->escape($data['filter_date']) . "%'";
// 		}

// 		$sql .= " AND vop.vendor_id = '" . (int)$this->vendor->getId() . "'";
// 		$sql .= " GROUP BY vop.order_id
// 		) AS grouped_orders";

// 		$query = $this->db->query($sql);
// 		return $query->row['total'];
// 	}

public function getTotalTrackOrders($data) {
	$vendorstatus  = $this->config->get('vendor_showorder_status');
	$defaultstatus = $this->config->get('config_complete_status');

	$implode = [];

	if (!empty($vendorstatus)) {
		foreach ($vendorstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}
	} else {
		foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}
	}

	$language_id = (int)$this->config->get('config_language_id');

	$sql = "SELECT COUNT(*) AS total FROM (
		SELECT vop.order_id
		FROM " . DB_PREFIX . "vendor_order_product vop 
		LEFT JOIN " . DB_PREFIX . "order o ON (vop.order_id = o.order_id)
		LEFT JOIN " . DB_PREFIX . "order_status os ON (vop.order_status_id = os.order_status_id AND os.language_id = " . $language_id . ")
		WHERE vop.order_status_id IN (" . implode(",", $implode) . ")
		AND vop.order_status_id NOT IN (2, 7, 8, 17)
		AND vop.order_id IS NOT NULL";

	// Apply filters
	if (!empty($data['filter_seller'])) {
		$sql .= " AND vop.vendor_id LIKE '" . $this->db->escape($data['filter_seller']) . "%'";
	}

	if (!empty($data['filter_customer'])) {
		$sql .= " AND o.customer_id LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
	}

	if (!empty($data['filter_status'])) {
		$sql .= " AND vop.order_status_id LIKE '" . $this->db->escape($data['filter_status']) . "%'";
	}

	if (!empty($data['filter_order_status'])) {
		$sql .= " AND LOWER(os.name) = '" . $this->db->escape(strtolower($data['filter_order_status'])) . "'";
	}

	if (!empty($data['filter_order_id'])) {
		$sql .= " AND vop.order_id LIKE '" . $this->db->escape($data['filter_order_id']) . "%'";
	}

	if (!empty($data['filter_date'])) {
		$sql .= " AND vop.date_added LIKE '" . $this->db->escape($data['filter_date']) . "%'";
	}

	$sql .= " AND vop.vendor_id = '" . (int)$this->vendor->getId() . "'";
	$sql .= " GROUP BY vop.order_id
	) AS grouped_orders";

	$query = $this->db->query($sql);
	return $query->row['total'];
}

	
	public function getTrackableStatuses() {
        $query = $this->db->query("
            SELECT name 
            FROM " . DB_PREFIX . "order_status 
            WHERE name NOT IN ('Cancelled','Processing','Pending','Breached','Label Generated')
        ");
        
        //added to return the array
    return array_column($query->rows, 'name');
    }


}	



	
	