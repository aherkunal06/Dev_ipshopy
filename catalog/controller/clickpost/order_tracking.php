<?php
require_once DIR_SYSTEM . 'config/clickpost_constants.php';

class ControllerClickpostOrderTracking extends Controller {

    public function index() {
        // Optional: homepage or restricted call
        // $this->response->setOutput(json_encode([
        //     'message' => 'Use /trackAllOrders route to process AWB tracking.'
        // ]));
       
    }

    public function trackAllOrders() {
        require_once DIR_SYSTEM . 'config/clickpost_constants.php';

        // $this->load->model('extension/module/clickpost');
        // $this->load->model('checkout/order'); // For addOrderHistory if needed
    
        $specialOrderIds = [16030, 16031, 16037, 16072, 16068,16106,16102,16071,16090,16070,16130,16164,16138,16148,16157,15975,15976,15974,15964,15960,15965];
        
        // $username = CLICKPOST_USERNAME;
        // $key = CLICKPOST_API_KEY;
        $url = CLICKPOST_TRACKING_URL;

        // Step 1: Get valid orders
        $ordersQuery = $this->db->query("
            SELECT DISTINCT o.order_id, o.order_status_id, cpo.waybill, cpo.courier_partner_id
            FROM " . DB_PREFIX . "clickpost_order cpo
            INNER JOIN " . DB_PREFIX . "order o ON cpo.	ipshopy_order_id = o.order_id
            WHERE o.order_status_id NOT IN (0,1,2,5,7,10,14,15,17)
            AND cpo.waybill IS NOT NULL
        ");

        // if (!$ordersQuery->num_rows) {
        //     $this->response->setOutput(json_encode(['message' => 'No valid orders found.']));
        //     return;
        // }

        $results = [];

        foreach ($ordersQuery->rows as $row) {
            $order_id = (int)$row['order_id'];
            $awb = $row['waybill'];
            $cp_id = $row['courier_partner_id'];
            $current_status_id = (int)$row['order_status_id'];
            
            //added code changes for the test and live orders tracking on 28/06/2025
            if (in_array($order_id, $specialOrderIds)) {
                // Use different credentials for special orders
                $username = CLICKPOST_TEST_USERNAME; // Update with your special username
                $key = CLICKPOST_TEST_API_KEY; // Update with your special API key
            } else {
                // Reset to default credentials for other orders
                $username = CLICKPOST_USERNAME;
                $key = CLICKPOST_API_KEY;
            }
            
            // skip API call for the following order ids update the logic on 10/07/2025
            if (in_array($order_id, [16390, 16391, 16378, 16374, 16321,16316])) {
                continue;
            }


            $full_url = "{$url}?username={$username}&key={$key}&waybill={$awb}&cp_id={$cp_id}";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $full_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ));

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if ($error || !$response) continue;

            $api_result = json_decode($response, true);
            if (!$api_result || empty($api_result['result'][$awb]['latest_status']['clickpost_status_description'])) continue;

            $clickpost_status_raw = $api_result['result'][$awb]['latest_status']['clickpost_status_description'] ?? '';
            $clickpost_status = str_replace(' ', '', trim($clickpost_status_raw));

            $tracking_number = $awb;

            // Find the matching order_status_id in your DB
            // Query DB by removing spaces from both sides during comparison
            $statusQuery = $this->db->query("
                SELECT order_status_id FROM " . DB_PREFIX . "order_status 
                WHERE  REPLACE(name, ' ', '') = '" . $this->db->escape($clickpost_status) . "'
            ");
           ////name = '" . $this->db->escape(str_replace(' ', '', $clickpost_status)) . "'

            if (!$statusQuery->num_rows) continue;

            $new_status_id = (int)$statusQuery->row['order_status_id'];
            if ($new_status_id === $current_status_id) continue; // No change

            // Fetch vendor_id
            $vendorQuery = $this->db->query("
                SELECT vendor_id FROM " . DB_PREFIX . "vendor_order_product 
                WHERE order_id = '" . $order_id . "' LIMIT 1
            ");
            $vendor_id = $vendorQuery->num_rows ? (int)$vendorQuery->row['vendor_id'] : 0;

            // Update oc_order
            $this->db->query("
                UPDATE " . DB_PREFIX . "order 
                SET order_status_id = '" . $new_status_id . "', 
                    tracking = '" . $this->db->escape($tracking_number) . "',
                    date_modified = NOW()
                WHERE order_id = '" . $order_id . "'
            ");

            //update the oc_clickpost_order table
            $this->db->query("
                UPDATE " . DB_PREFIX . "clickpost_order 
                SET 
                    order_status_id = '" . (int)$new_status_id . "',
                    date_modified = NOW()
                WHERE ipshopy_order_id = '" . (int)$order_id . "'
            ");

            // Update vendor_order_product
            $this->db->query("
                UPDATE " . DB_PREFIX . "vendor_order_product 
                SET order_status_id = '" . $new_status_id . "',
                    tracking = '" . $this->db->escape($tracking_number) . "', 
                    date_modified = NOW()
                WHERE order_id = '" . $order_id . "' AND vendor_id = '" . $vendor_id . "'
            ");

            // Insert into oc_order_history
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "order_history 
                SET order_id = '" . $order_id . "', 
                    order_status_id = '" . $new_status_id . "',
                    notify = 1, 
                    comment = '" . $this->db->escape($clickpost_status) . "',
                    date_added = NOW()
            ");

            // Insert into order_vendorhistory
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "order_vendorhistory 
                SET order_id = '" . $order_id . "', 
                    order_status_id = '" . $new_status_id . "',
                    comment = '" . $this->db->escape($clickpost_status) . "',
                    vendor_id = '" . $vendor_id . "',
                    date_added = NOW()
            ");

            $results[] = [
                'order_id' => $order_id,
                'awb' => $awb,
                'from_status' => $current_status_id,
                'to_status' => $new_status_id,
                'clickpost_status' => $clickpost_status
            ];
        }

        // // Return the success message and updated count
        // return json_encode([
        //     'message' => 'Order statuses updated successfully for order ids:',
        //     'updated_orders' => count($results),
        //     'order_ids' => array_column($results, 'order_id')
        // ]);
        
        //check the orders if any
        $updated_orders = count($results);
        
        if($updated_orders> 0){
            return 'Order statuses updated successfully';
        }else{
            return 'No orders were updated.';
        }

    }

}
