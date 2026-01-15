<?php
require_once DIR_SYSTEM . 'config/clickpost_constants.php';

class ControllerClickpostCancel extends Controller {

    // public function clickpostcancel() {
    //     $json = [];

    //     // Static values
    //     $username     = "ipshopy-test";
    //     $key          = "6cb47441-af83-4d3f-bc49-cbbece04a4c0";
    //     $waybill      = "27401010002811";
    //     $account_code = "Delhivery one"; // Ensure exact spelling
    //     $cp_id        = "4"; // Optional, only if required by ClickPost
        
    //     var_dump($username);

    //     if (!$waybill || !$account_code) {
    //         $json['error'] = 'Waybill or account code missing.';
    //     } else {
    //         // Encode and build final URL
    //         $url = "https://www.clickpost.in/api/v1/cancel-order/?" .
    //             "username=" . urlencode($username) .
    //             "&key=" . urlencode($key) .
    //             "&waybill=" . urlencode($waybill) .
    //             "&account_code=" . urlencode($account_code) .
    //             "&cp_id=" . urlencode($cp_id);

    //         // Initialize CURL
    //         $ch = curl_init();
    //         curl_setopt($ch, CURLOPT_URL, $url);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ❗ Only for local testing (XAMPP)

    //         // Execute
    //         $response = curl_exec($ch);
    //         $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //         $curl_error = curl_error($ch);
    //         curl_close($ch);

    //         // Debug log
    //         file_put_contents(DIR_LOGS . 'clickpost_cancel_debug.log', "HTTP Code: $http_code\nCURL Error: $curl_error\nResponse:\n$response");

    //         // Handle errors
    //         if ($response === false) {
    //             $json['error'] = 'CURL request failed: ' . $curl_error;
    //         } else {
    //             $response_data = json_decode($response, true);

    //             if ($http_code == 200 && isset($response_data['meta']['success']) && $response_data['meta']['success']) {
    //                 $json['success'] = 'Order cancelled successfully via ClickPost.';
    //             } else {
    //                 $error_message = isset($response_data['meta']['message']) ? $response_data['meta']['message'] : 'Unknown error';
    //                 $json['error'] = 'ClickPost cancellation failed: ' . $error_message;
    //             }
    //         }
    //     }

    //     // Return JSON
    //     $this->response->addHeader('Content-Type: application/json');
    //     $this->response->setOutput(json_encode($json));
    // }
    
    public function clickpostcancel() {
        require_once DIR_SYSTEM . 'config/clickpost_constants.php';
		$json = array();
	
		if (!isset($this->request->post['order_id'])) {
			$json['error'] = 'Invalid Order ID';
		} else {
			$this->load->model('account/order');
	
			$order_id = (int)$this->request->post['order_id'];

			$order_data = $this->model_account_order->getOrder($order_id);
	        // $post_data ="";
			if ($order_data) {
				// $awb_number = $this->$order_data["awbno"];
			    $awb_number = $this->model_account_order->getAwbNo($order_id); // Assuming awb_number is in order table

			 //   $account_code = $this->model_account_order->getaccountcode($order_id); // Assuming awb_number is in order table
			    
			    $result = $this->model_account_order->getaccountcode($order_id);
			    
			    $account_code = $result ['account_code'];
			 
			    $cp_id = $result['courier_partner_id'];

				if (!$awb_number || !$account_code || !$cp_id) {
                    $json['error'] = 'Missing shipment details (AWB, Account Code, or CP ID).';
                } else {
                    $username = CLICKPOST_USERNAME;
                    $key = CLICKPOST_API_KEY;

                    $url = CLICKPOST_CancelAPI_URL .
                        "username=" . urlencode($username) .
                        "&key=" . urlencode($key) .
                        "&waybill=" . urlencode($awb_number) .
                        "&account_code=" . urlencode($account_code) .
                        "&cp_id=" . urlencode($cp_id);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                    $response = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curl_error = curl_error($ch);
                    curl_close($ch);

                    file_put_contents(DIR_LOGS . 'clickpost_cancel_debug.log', "HTTP Code: $http_code\nCURL Error: $curl_error\nResponse:\n$response");

                    if ($response === false) {
                        $json['error'] = 'CURL request failed: ' . $curl_error;
                    } else {
                        $response_data = json_decode($response, true);
                        if ($http_code == 200 && isset($response_data['meta']['success']) && $response_data['meta']['success']) {
                            $json['success'] = 'Order cancelled successfully.';
                        } else {
                            $error_message = isset($response_data['meta']['message']) ? $response_data['meta']['message'] : 'Unknown error';
                            $json['error'] = 'Shipment cancellation failed: ' . $error_message;
                        }
                    }
                }
            } else {
                $json['error'] = 'Order not found.';
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
}
