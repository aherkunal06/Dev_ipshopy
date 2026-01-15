<?php
class ControllerClickpostPincodeServiceability extends Controller {
    // API credentials 
    private $api_username = 'ipshopy';
    private $api_key = 'e24653d6-275f-425f-900a-0247bbd0f24b';
    
    // API endpoints
    private $serviceability_url = 'https://serviceability.clickpost.in/api/v3/serviceability_api/';
    private $sla_url = 'https://ds.clickpost.in/api/v2/predicted_sla_api/';
    
    /**
     * Check pincode serviceability and SLA
     * 
     * @return void
     */


    public function checkPincode() {
        $json = [];
        
        // Validate required parameters
        if (!isset($this->request->post['pincode']) || !isset($this->request->post['product_id'])) {
            $json['success'] = false;
            $json['error'] = "Missing required parameters.";
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        
        $drop_pincode = trim($this->request->post['pincode']);
        $product_id = (int)$this->request->post['product_id'];
        
        // Clean pincode - remove any non-numeric characters
        $drop_pincode = preg_replace('/\D/', '', $drop_pincode);
        
        if (empty($drop_pincode) || !preg_match('/^\d{6}$/', $drop_pincode)) {
            $json['success'] = false;
            $json['error'] = "Please enter a valid 6-digit pincode.";
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        
        try {
            $vendor_query = $this->db->query("SELECT v.vendor_id, v.postcode 
                FROM " . DB_PREFIX . "vendor_to_product vp
                LEFT JOIN " . DB_PREFIX . "vendor v ON vp.vendor_id = v.vendor_id 
                WHERE vp.product_id = '" . (int)$product_id . "'");
            
            // Check if vendor and postcode exist
            if (!$vendor_query->num_rows || empty($vendor_query->row['postcode'])) {
                $this->log->write("Vendor data not found for product ID: $product_id");
                $json['success'] = false;
                $json['error'] = "Vendor information not available.";
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }
            
            // Get and clean pickup pincode
            $pickup_pincode = $vendor_query->row['postcode'];
            $vendor_id = $vendor_query->row['vendor_id'];
            $pickup_pincode = preg_replace('/\D/', '', $pickup_pincode);
            
            // Validate pickup pincode
            if (empty($pickup_pincode) || !preg_match('/^\d{6}$/', $pickup_pincode)) {
                $this->log->write("Invalid vendor pincode: $pickup_pincode for vendor ID: $vendor_id");
                $json['success'] = false;
                $json['error'] = "Unable to check delivery for this product.";
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }
            
            // Step 1: Check serviceability
            $serviceability_result = $this->checkServiceability($pickup_pincode, $drop_pincode);
            
            // If not serviceable, return error
            if (!$serviceability_result['success'] || !isset($serviceability_result['serviceable']) || $serviceability_result['serviceable'] !== true) {
                $json['success'] = false;
                $json['error'] = $serviceability_result['error'] ?? 'Not serviceable to this location.';
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }
            
            // Step 2: Get SLA estimation if serviceable
            $sla_result = $this->getDeliveryEstimate($pickup_pincode, $drop_pincode);
            
            // Save pincode to session history
            $this->savePincodeToHistory($drop_pincode);
            
            // Prepare the success response
            $json['success'] = true;
            $json['pickup_pincode'] = $pickup_pincode;
            $json['drop_pincode'] = $drop_pincode;
            $json['vendor_id'] = $vendor_id;
            $json['serviceable'] = true;
            
            // Include pincode history in the response for frontend UI
            $json['pincode_history'] = isset($this->session->data['pincode_history']) ? $this->session->data['pincode_history'] : [];
            
            // Set the estimated delivery date from SLA API
            if ($sla_result['success'] && !empty($sla_result['sla'])) {
                // Use the exact delivery estimate from the SLA API
                $json['estimated_delivery'] = $sla_result['sla'];
                
                // Include the raw SLA data for debugging if needed
                if (isset($sla_result['raw_data'])) {
                    $json['sla_data'] = $sla_result['raw_data'];
                }
            } else {
                // If SLA API call failed, provide a reasonable delivery estimate
                // Calculate estimated delivery based on current date + standard delivery time
                $min_days = 3;
                $max_days = 5;
                
                $today = new DateTime();
                $min_date = clone $today;
                $min_date->modify("+{$min_days} weekday");
                $max_date = clone $today;
                $max_date->modify("+{$max_days} weekday");
                
                $min_formatted = $min_date->format('j M');
                $max_formatted = $max_date->format('j M');
                
                if ($min_formatted == $max_formatted) {
                    $json['estimated_delivery'] = "Estimated by {$min_formatted}";
                } else {
                    $json['estimated_delivery'] = "Estimated between {$min_formatted} - {$max_formatted}";
                }
                
                // Log the SLA API error
                if (isset($sla_result['error'])) {
                    $this->log->write("SLA API error: " . $sla_result['error']);
                }
            }
            
        } catch (Exception $e) {
            // Log any exceptions
            $this->log->write("Exception in Clickpost pincode check: " . $e->getMessage());
            
            $json['success'] = false;
            $json['error'] = "An unexpected error occurred. Please try again.";
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }
        
        // Send the final response
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Save pincode to session history
     *
     * @param string $pincode
     * @return void
     */
    private function savePincodeToHistory($pincode) {
        $pincode_history = isset($this->session->data['pincode_history']) ? $this->session->data['pincode_history'] : [];
        
        if (!in_array($pincode, $pincode_history)) {
            array_unshift($pincode_history, $pincode);
            
            $pincode_history = array_slice($pincode_history, 0, 5);
            
            // Save back to session
            $this->session->data['pincode_history'] = $pincode_history;
            
            // If user is logged in, also save to database
            if ($this->customer->isLogged()) {
                $customer_id = $this->customer->getId();
                
                $pincode_history_json = json_encode($pincode_history);
                
                $this->db->query("UPDATE " . DB_PREFIX . "customer SET pincode_history = '" . 
                    $this->db->escape($pincode_history_json) . "' WHERE customer_id = '" . 
                    (int)$customer_id . "'");
                    
                $this->log->write("Updated pincode history for customer ID: $customer_id with pincode: $pincode");
            }
        }
    }
    
    /**
     * Check pincode serviceability with Clickpost API
     *
     * @param string $pickup_pincode Pickup pincode
     * @param string $drop_pincode Delivery pincode
     * @return array Result with success flag and error if any
     */
    public function checkServiceability($pickup_pincode, $drop_pincode) {
        // Validate pincodes
        if (!preg_match('/^\d{6}$/', $pickup_pincode) || !preg_match('/^\d{6}$/', $drop_pincode)) {
            return ['success' => false, 'error' => 'Invalid pincode format.'];
        }
        
        try {
            // Prepare payload according to official Clickpost API docs
            // https://docs.clickpost.ai/reference/pincode-serviceability
            $payload = json_encode([
                [
                    'drop_pincode' => $drop_pincode,
                    'pickup_pincode' => $pickup_pincode,
                    'service_type' => 'FORWARD'
                ]
            ]);
            
            // Call the API
            $result = $this->callClickpostAPI($this->serviceability_url, $payload);
            
            $this->log->write("Serviceability API response: " . json_encode($result));
            
            if (!$result['success']) {
                return [
                    'success' => false, 
                    'error' => 'Unable to check serviceability at this time. Please try again later.'
                ];
            }
            
            $response_data = $result['data'];
            
            if (isset($response_data['meta']) && isset($response_data['meta']['success']) && $response_data['meta']['success'] === true) {
                if (isset($response_data['result']) && isset($response_data['result']['serviceable']) && $response_data['result']['serviceable'] === true) {
                    $result = [
                        'success' => true,
                        'serviceable' => true
                    ];
                    
                    return $result;
                } else {
                    return [
                        'success' => false,
                        'serviceable' => false,
                        'error' => 'This pincode is not serviceable.'
                    ];
                }
            }
            // Fallback for other response formats if they change their API
            else if (isset($response_data['success']) && $response_data['success'] === true) {
                if (isset($response_data['is_serviceable']) && $response_data['is_serviceable'] === true) {
                    $result = [
                        'success' => true,
                        'serviceable' => true
                    ];
                    
                    return $result;
                } else {
                    return [
                        'success' => false,
                        'serviceable' => false,
                        'error' => 'This pincode is not serviceable.'
                    ];
                }
            } 
            else {
                $error_msg = isset($response_data['meta']['message']) ? $response_data['meta']['message'] : 'Invalid response from serviceability API';
                return [
                    'success' => false,
                    'serviceable' => false,
                    'error' => $error_msg
                ];
            }
        } catch (Exception $e) {
            $this->log->write("Error in checkServiceability: " . $e->getMessage());
            return [
                'success' => false,
                'serviceable' => false,
                'error' => 'Error checking serviceability: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get delivery estimate with Clickpost SLA API
     *
     * @param string $pickup_pincode Pickup pincode
     * @param string $drop_pincode Delivery pincode
     * @return array Result with success flag and SLA if available
     */
    public function getDeliveryEstimate($pickup_pincode, $drop_pincode) {
        // Validate pincodes
        if (!preg_match('/^\d{6}$/', $pickup_pincode) || !preg_match('/^\d{6}$/', $drop_pincode)) {
            return ['success' => false, 'error' => 'Invalid pincode format.'];
        }
        
        try {
            // Prepare payload according to official Clickpost EDD API docs
            // https://docs.clickpost.ai/reference/expected-date-of-delivery-v2
            $payload = json_encode([
                [
                    'pickup_pincode' => $pickup_pincode,
                    'drop_pincode' => $drop_pincode
                ]
            ]);
            
            $result = $this->callClickpostAPI($this->sla_url, $payload);
            
            $this->log->write("SLA API response: " . json_encode($result));
            
            // If API call failed, return a failure response
            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => 'Unable to retrieve delivery estimate from carrier API'
                ];
            }
            
            $response_data = $result['data'];
            
            // Parse according to official Clickpost API response format
            if (isset($response_data['meta']) && isset($response_data['meta']['success']) && $response_data['meta']['success'] === true) {
                if (isset($response_data['result'])) {
                    // Handle result as an array (API returns array of results)
                    if (is_array($response_data['result']) && !empty($response_data['result'])) {
                        $result_data = $response_data['result'][0]; // Get first result
                        
                        // Check if predicted_sla_min and predicted_sla_max are available
                        if (isset($result_data['predicted_sla_min']) && isset($result_data['predicted_sla_max'])) {
                            $min_days = $result_data['predicted_sla_min'];
                            $max_days = $result_data['predicted_sla_max'];
                            
                            if ($min_days == $max_days) {
                                return [
                                    'success' => true,
                                    'sla' => "{$min_days} business day" . ($min_days > 1 ? "s" : ""),
                                    'min' => $min_days,
                                    'max' => $max_days,
                                    'raw_data' => $result_data
                                ];
                            } else {
                                return [
                                    'success' => true,
                                    'sla' => "{$min_days}-{$max_days} business days",
                                    'min' => $min_days,
                                    'max' => $max_days,
                                    'raw_data' => $result_data
                                ];
                            }
                        }
                    } 
                    else if (is_array($response_data['result']) === false) {
                        $result_data = $response_data['result'];
                        
                        if (isset($result_data['predicted_sla_min']) && isset($result_data['predicted_sla_max'])) {
                            $min_days = $result_data['predicted_sla_min'];
                            $max_days = $result_data['predicted_sla_max'];
                            
                            if ($min_days == $max_days) {
                                return [
                                    'success' => true,
                                    'sla' => "{$min_days} business day" . ($min_days > 1 ? "s" : ""),
                                    'min' => $min_days,
                                    'max' => $max_days,
                                    'raw_data' => $result_data
                                ];
                            } else {
                                return [
                                    'success' => true,
                                    'sla' => "{$min_days}-{$max_days} business days",
                                    'min' => $min_days,
                                    'max' => $max_days,
                                    'raw_data' => $result_data
                                ];
                            }
                        }
                    }
                    
                    return [
                        'success' => false, 
                        'error' => 'Delivery estimate data not available from carrier',
                        'raw_data' => $response_data['result']
                    ];
                } else {
                    return [
                        'success' => false, 
                        'error' => 'Delivery estimate result not found in API response'
                    ];
                }
            } 
            else {
                return [
                    'success' => false, 
                    'error' => 'Invalid response format from delivery estimate API'
                ];
            }
        } catch (Exception $e) {
            $this->log->write("Error in getDeliveryEstimate: " . $e->getMessage());
            return [
                'success' => false, 
                'error' => 'Error retrieving delivery estimate: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get customer addresses for pincode dropdown
     *
     * @return void
     */
    public function getAddresses() {
        $json = [];
        
        if ($this->customer->isLogged()) {
            // Get customer addresses
            $addresses = [];
            $this->load->model('account/address');
            $customer_addresses = $this->model_account_address->getAddresses();
            
            foreach ($customer_addresses as $address) {
                if (!empty($address['postcode'])) {
                    $formatted = $address['address_1'];
                    if (!empty($address['city'])) {
                        $formatted .= ', ' . $address['city'];
                    }
                    $formatted .= ' - ' . $address['postcode'];
                    
                    $addresses[] = [
                        'postcode' => $address['postcode'],
                        'formatted' => $formatted
                    ];
                }
            }
            
            $json['addresses'] = $addresses;
            
            // Get pincode history from session
            $json['pincode_history'] = isset($this->session->data['pincode_history']) ? $this->session->data['pincode_history'] : [];
        } else {
            $json['addresses'] = [];
            $json['pincode_history'] = [];
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Generic function to call Clickpost API
     *
     * @param string $url API endpoint
     * @param string $payload JSON payload
     * @return array Result with success flag and data/error
     */
    private function callClickpostAPI($url, $payload) {
        // Initialize cURL
        $ch = curl_init();
        
        $auth_url = $url;
        if (strpos($auth_url, '?') === false) {
            $auth_url .= '?username=' . urlencode($this->api_username) . '&key=' . urlencode($this->api_key);
        } else {
            $auth_url .= '&username=' . urlencode($this->api_username) . '&key=' . urlencode($this->api_key);
        }
        
        curl_setopt($ch, CURLOPT_URL, $auth_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); 
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Security settings for production
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        // Execute cURL request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        // Close cURL
        curl_close($ch);
        
        // Check for cURL errors
        if ($response === false) {
            $this->log->write("cURL error for $url: " . $curl_error);
            return [
                'success' => false, 
                'error' => 'Connection error: ' . $curl_error
            ];
        }
        
        // Check for non-200 HTTP status code
        if ($http_code < 200 || $http_code >= 300) {
            $this->log->write("HTTP error for $url: " . $http_code . " - Response: " . $response);
            return [
                'success' => false, 
                'error' => "HTTP error: {$http_code}"
            ];
        }
        
        // Try to decode JSON response
        $data = @json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log->write("JSON decode error for $url: " . json_last_error_msg() . " - Response: " . $response);
            return [
                'success' => false, 
                'error' => 'Invalid JSON response: ' . json_last_error_msg()
            ];
        }
        
        return ['success' => true, 'data' => $data];
    }
}
