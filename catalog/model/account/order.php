        <?php
class ModelAccountOrder extends Model {
	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND customer_id != '0' AND order_status_id > '0'");

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
			
			// 			16-06-2025---------------------
           // ✅ Fetch signatures vendor-wise per product_id
            // $vendor_signature_map = [];
            
            // $signature_query = $this->db->query("
            // 	SELECT vop.product_id, v.signature
            // 	FROM " . DB_PREFIX . "vendor_order_product vop
            // 	LEFT JOIN " . DB_PREFIX . "vendor v ON vop.vendor_id = v.vendor_id
            // 	WHERE vop.order_id = '" . (int)$order_id . "'
            // ");
            
            // foreach ($signature_query->rows as $row) {
            // 	$vendor_signature_map[$row['product_id']] = $row['signature'];
            // }

          // -------------------------========

			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'email'                   => $order_query->row['email'],
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
				'payment_method'          => $order_query->row['payment_method'],
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
				'shipping_method'         => $order_query->row['shipping_method'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'order_status_id'         => $order_query->row['order_status_id'],
				'language_id'             => $order_query->row['language_id'],
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'date_modified'           => $order_query->row['date_modified'],
				'date_added'              => $order_query->row['date_added'],
				'ip'                      => $order_query->row['ip'],
				'total_courier_charges'   => $order_query->row['total_courier_charges'],
				// 'gstin'                  => $order_query->row['gstin']
				// 'vendor_signatures' => $vendor_signature_map
			);
		} else {
			return false;
		}
	}
public function getOrderProducts_New($order_id)
	{
		$query = $this->db->query("
			SELECT op.name, p.image 
			FROM " . DB_PREFIX . "order_product op
			LEFT JOIN " . DB_PREFIX . "product p ON op.product_id = p.product_id
			WHERE op.order_id = '" . (int)$order_id . "'
		");

		return $query->rows;
	}

	public function getOrders($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 1;
		}

// 		$query = $this->db->query("SELECT o.order_id, o.firstname, o.lastname, os.name as status, o.date_added, o.total, o.currency_code, o.currency_value FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);
        // added on 19-03-2025
//         $query = $this->db->query("SELECT o.order_id,o.awbno, o.firstname, o.lastname, os.name as status,os.order_status_id, o.date_added, o.total, o.currency_code, o.currency_value FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);

// 		return $query->rows;
        $query = $this->db->query("SELECT o.order_id, o.firstname, o.lastname, o.total, o.currency_code, o.currency_value, o.date_added, os.name AS status, o.awbno, 
			(SELECT GROUP_CONCAT(op.name SEPARATOR ', ') FROM " . DB_PREFIX . "order_product op WHERE op.order_id = o.order_id) AS product_names
			FROM " . DB_PREFIX . "order o
			LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id)
			WHERE o.customer_id = '" . (int)$this->customer->getId() . "'
			ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);
			
		   return $query->rows;
	}

	public function getOrderProduct($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->row;
	}

	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}

	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getOrderHistories($order_id) {
	    // update the following query remove date_added from first of os.name on 14-06-1999
		$query = $this->db->query("SELECT os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added");

		return $query->rows;
	}

	public function getTotalOrders() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` o WHERE customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row['total'];
	}

	public function getTotalOrderProductsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	public function getTotalOrderVouchersByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}
	
    // 	added on 16-02-2025 by sagar

	// for customer cancel order 
	// Get the order status ID for 'Canceled'
	public function getOrderCancelId()
	{
		$query = $this->db->query("SELECT `order_status_id` FROM " . DB_PREFIX . "order_status WHERE name = 'Canceled'");
		return $query->row['order_status_id'];
	}

// commented on 20-03-2025
	// Update the order status and order history after cancellation
// 	public function cancelOrder($order_id, $order_status)
// 	{	
// 		// Check if the current status is not already cancelled
// 		$check = $this->db->query("SELECT * FROM " . DB_PREFIX . "order WHERE order_id = '" . (int)$order_id . "' AND order_status_id != '" . (int)$order_status . "'");
// 		if ($check->num_rows) {
// 			// Update order status
// 			$this->db->query("UPDATE " . DB_PREFIX . "order SET order_status_id = '" . (int)$order_status . "' WHERE order_id = '" . (int)$order_id . "'");
// 			// Add to order history
// 			$comment = 'Order canceled by customer.';
// 			$this->db->query("INSERT INTO " . DB_PREFIX . "order_history (`order_id`, `order_status_id`, `notify`, `comment`, `date_added`) VALUES ('" . (int)$order_id . "', '" . (int)$order_status . "', 1, '" . $this->db->escape($comment) . "', NOW())");
// 			$this->db->query("UPDATE " . DB_PREFIX . "vendor_order_product SET order_status_id = '" . (int)$order_status . "' WHERE order_id = '" . (int)$order_id . "'");
// 			$this->db->query("INSERT INTO " . DB_PREFIX . "order_vendorhistory (`order_id`, `order_status_id`, `comment`, `date_added`) VALUES ('" . (int)$order_id . "', '" . (int)$order_status . "','" . $this->db->escape($comment) . "', NOW())");
// 			return true;
// 		}
// 		return false;
// 	}
	
    // 	end here 
    
    // added on 19-03-2025
//     public function gettotalOrderTotal($order_id) {
// 		$query = $this->db->query("SELECT total FROM " . DB_PREFIX . "order WHERE order_id = '" . (int)$order_id . "'");
	
// 		if ($query->num_rows) {
// 			return $query->row['total'];
// 		} else {
// 			return false;
// 		}
// 	}
	
	
// 	added on 19-03-2025

public function getAwbNo($order_id)
	{
		// Query the oc_order table to get the awb_no for the given order_id.
		$query = $this->db->query("SELECT awbno FROM " . DB_PREFIX . "order WHERE order_id = '" . (int)$order_id . "'");

		// Check if the query returned a result.
		if ($query->num_rows) {
			// Return the AWB number from the result row.
			return $query->row['awbno'];
		} else {
			// Return false if no record is found.
			return false;
		}
	}

// 	added on 20-03-2025

    public function cancelOrder($order_id, $order_status_id) {
		// Update order status and reset AWB number in one query
		$this->db->query("UPDATE " . DB_PREFIX . "order 
						  SET order_status_id = '" . (int)$order_status_id . "', 
							awbno = NULL,  shipping_label=NULL,  date_modified = NOW()
						  WHERE order_id = '" . (int)$order_id . "'");
	
		// Update vendor order product status in one query
		$this->db->query("UPDATE " . DB_PREFIX . "vendor_order_product 
						  SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW()
						  WHERE order_id = '" . (int)$order_id . "'");
	
		$comment = 'Order canceled by customer.';
	
		// Insert order history in a single query
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history 
						  (order_id, order_status_id, notify, comment, date_added) 
						  VALUES ('" . (int)$order_id . "', '" . (int)$order_status_id . "', 1, '" . $this->db->escape($comment) . "', NOW())");
	
		// Fetch vendor ID safely
		$query = $this->db->query("SELECT vendor_id FROM " . DB_PREFIX . "vendor_order_product 
								   WHERE order_id = '" . (int)$order_id . "' LIMIT 1");
	
		if ($query->num_rows > 0) { // Check if vendor_id exists
			$vendor_id = (int)$query->row['vendor_id'];
	
			// Insert vendor history only if vendor exists
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_vendorhistory 
							  (order_id, order_status_id, vendor_id, comment, date_added) 
							  VALUES ('" . (int)$order_id . "', '" . (int)$order_status_id . "', '" . $vendor_id . "', '" . $this->db->escape($comment) . "', NOW())");
				
			//added on 20-03-2025			  
		    $this->db->query("UPDATE " . DB_PREFIX . "order_courier_rate 
							  SET courier_rate = 0, 
								  carrier_id = NULL, 
								  courier_name = NULL ,
								  rto_charges = 0 
							  WHERE order_id = '" . (int)$order_id . "' 
							  AND vendor_id = '" . (int)$vendor_id . "'");
		}
	
		return true;
	}
	
	
// 	added on 09-04-2025
    public function getOrderStatusId($order_id) {
        $query = $this->db->query("SELECT order_status_id FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
    
        if ($query->num_rows) {
            return $query->row['order_status_id'];
        } else {
            return false;
        }
        
    }
    
    public function InvoicePDF($order_id, $invoice_no, $invoice_path) {
	
		// $html = $this->load->controller('account/order/generateInvoicePDF&order_id=' . (int)$order_id);
	
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_invoice WHERE order_id = '" . (int)$order_id . "'");
	
		if ($query->num_rows) {
			$this->db->query("UPDATE " . DB_PREFIX . "order_invoice SET invoice_no = '" . $this->db->escape($invoice_no) . "', invoice_path = '" . $this->db->escape($invoice_path) . "', date_added = NOW() WHERE order_id = '" . (int)$order_id . "'");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_invoice SET order_id = '" . (int)$order_id . "', invoice_no = '" . $this->db->escape($invoice_no) . "', invoice_path = '" . $this->db->escape($invoice_path) . "', date_added = NOW()");
		}
	
	}
	
	
    public function getaccountcode($order_id) {
	
		// $html = $this->load->controller('account/order/generateInvoicePDF&order_id=' . (int)$order_id);
	
		$query = $this->db->query("SELECT cpe.account_code, cpe.courier_partner_id FROM " . DB_PREFIX . "clickpost_order co
		                           left join " . DB_PREFIX . "courier_partner_email cpe on co.courier_partner_id = cpe.courier_partner_id
		                           WHERE co.ipshopy_order_id = '" . (int)$order_id . "'");
	       
	    if ($query->num_rows) {
        return [
            'account_code' => $query->row['account_code'],
            'courier_partner_id' => $query->row['courier_partner_id']
        ];
        } else {
            return false;
        }
	
	}


}