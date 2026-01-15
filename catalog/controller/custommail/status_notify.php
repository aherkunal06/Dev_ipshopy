<?php
class ControllerCustommailStatusNotify extends Controller {
    
    public function customerMail(&$route, &$args, &$output) {
        $order_id = (int)$args[0];
    
        if (!$order_id) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'error' => 'Missing order_id'
            ]));
            return;
        }
    
        $this->load->model('custommail/status_notify');
        $this->load->language('custommail/status_notify');
    
        $data = $this->model_custommail_status_notify->getOrderInfo($order_id);
    
        if ($data) {
            $mail_subject = sprintf($this->language->get('mail_subject'), $order_id);
            $customer_name = $data[0]['firstname'] ?? 'Customer';
            $order_status = $data[0]['status'];
            $email = $data[0]['email'];
    
            $view_data = [
                'subject'            => $mail_subject,
                'customer_name'      => $customer_name,
                'order_id'           => $order_id,
                'order_status'       => $order_status,
                'shipping_address_1' => $data[0]['shipping_address_1'],
                'shipping_city'      => $data[0]['shipping_city'],
                'shipping_zone'      => $data[0]['shipping_zone'],
                'shipping_postcode'  => $data[0]['shipping_postcode'],
                'shipping_country'   => $data[0]['shipping_country'],
                'products'           => [],
                'total'              => $data[0]['total'],
                'payment_method'     => $data[0]['payment_method'],
                'shipping_method'    => $data[0]['shipping_method'],
                'order_link'         => !empty($data[0]['customer_id']) ? $data[0]['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id : '',
            ];

            foreach ($data as $product) {
                $view_data['products'][] = [
                    'productname' => $product['productname'],
                    'model'       => $product['model']
                ];
            }

            $html = $this->load->view('custommail/customer_email_template', $view_data);

            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

            $mail->setTo($data[0]['email']);
            $mail->setFrom($this->config->get('config_email'));
            $mail->setSender($this->config->get('config_name'));
            $mail->setSubject($mail_subject);
            $mail->setHtml($html);
            $mail->send();
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => 'Customer Mail sent successfully!'
        ]));
    }
    
    public function sendVendorMail(&$route, &$args, &$output) {
        $order_id = (int)$args[0];

        if (!$order_id) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'error' => 'Missing order_id'
            ]));
            return;
        }

        $this->load->model('custommail/status_notify');
        $this->load->language('custommail/status_notify');

        $vendor_ids = $this->model_custommail_status_notify->getVendorsFromOrder($order_id);

        foreach ($vendor_ids as $vendor) {
            $vendor_id = (int)$vendor['vendor_id'];
            $data = $this->model_custommail_status_notify->getVendorsByOrder($order_id, $vendor_id);

            if ($data) {
                $vendor_subject = sprintf($this->language->get('vendor_mail_subject'), $order_id);
                $vendor_name = $data[0]['seller_name'] ?? 'Vendor';
                $order_status = $data[0]['status'];
                $email = $data[0]['email'];
                
                $view_data = [
                    'subject'            => $vendor_subject,
                    'vendor_name'        => $vendor_name,
                    'order_id'           => $order_id,
                    'order_status'       => $order_status,
                    'shipping_address_1' => $data[0]['shipping_address_1'],
                    'shipping_city'      => $data[0]['shipping_city'],
                    'shipping_zone'      => $data[0]['shipping_zone'],
                    'shipping_postcode'  => $data[0]['shipping_postcode'],
                    'shipping_country'   => $data[0]['shipping_country'],
                    'products'           => [],
                    'total'              => $data[0]['total'],
                    'shipping_method'    => $data[0]['shipping_method'],
                    'order_link'         => !empty($data[0]['vendor_id']) ? $data[0]['store_url'] . 'index.php?route=vendor/latestorder/letestview&order_id=' . $order_id : '',
                ];

                foreach ($data as $product) {
                    $view_data['products'][] = [
                        'productname' => $product['productname'],
                        'model'       => $product['model']
                    ];
                }
                
                $html = $this->load->view('custommail/vendor_email_template', $view_data);

                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                $mail->setTo($data[0]['email']);
                $mail->setFrom($this->config->get('config_email'));
                $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
                $mail->setSubject($vendor_subject);
                $mail->setHtml($html);
                $mail->send();
            }
        }

        // ✅ Send success response inside method
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => 'Vendor emails sent successfully!'
        ]));
    }
    
    
    public function sendVendorWhatsapp(&$route, &$args, &$output) {
        $order_id = (int)$args[0];

        if (!$order_id) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'error' => 'Missing order_id'
            ]));
            return;
        }

        $this->load->model('custommail/status_notify');
        $this->load->language('custommail/status_notify');

        $vendor_ids = $this->model_custommail_status_notify->getVendorsFromOrder($order_id);

        foreach ($vendor_ids as $vendor) {
            $vendor_id = (int)$vendor['vendor_id'];
            $data = $this->model_custommail_status_notify->getVendorsByOrder($order_id, $vendor_id);

            if ($data) {
                $vendor_subject = sprintf($this->language->get('vendor_mail_subject'), $order_id);
                $vendor_name = $data[0]['seller_name'] ?? 'Vendor';
                $order_status = $data[0]['status'];

                $message = "Greetings " . $vendor_name . ",\n\n";
                $message .= sprintf($this->language->get('vendor_mail_message')) . "\n";
                $message .= "Order ID #" . $order_id . " has been updated to status: " . $order_status . "\n\n";
                $message .= "Shipping Details:\n";
                $message .= $data[0]['shipping_address_1'] . "\n";
                $message .= $data[0]['shipping_city'] . ", " . $data[0]['shipping_zone'] . " - " . $data[0]['shipping_postcode'] . "\n";
                $message .= $data[0]['shipping_country'] . "\n\n";
                $message .= "Product(s):\n";

                foreach ($data as $product) {
                    $message .= "- " . $product['productname'] . " (Model: " . $product['model'] . ") - ₹" . $product['total'] . "\n\n";
                }

                // $message .= "\nTracking ID: " . $data[0]['awbno'] . "\n\n";
                $message .= "Warm Regards,\nIpshopy Team\n";

                $mobile = $data[0]['telephone']; // without country code
    			$name = $data['firstname'];
        		$notification = $message;
        		$this->sendWhatsAppMessage($mobile, $notification);
                
            }
        }
        
        // ✅ Send success response inside method
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => 'Vendor Whatsapp sent successfully!'
        ]));
    } 
    
    
    public function sendWhatsAppMessage($telephone, $message) { 
		// API credentials
		$instance_id = '686E3220EFE1C';
		$access_token = '68106bf521ccc';
	
		// Format telephone (if needed, like adding country code)
		$formatted_number = '91' . ltrim($telephone, '0');  // assuming Indian numbers and removing leading 0
	
		// Build the API URL  . ltrim($telephone, '0')
		$vendor_api_url = 'https://waclient.com/api/send?' . http_build_query([
			'number'       => $formatted_number,
			'type'         => 'text',
			'message'      => $message,
			'instance_id'  => $instance_id,
			'access_token' => $access_token,
		]);
	
		// Initialize cURL
		$ch = curl_init($vendor_api_url);
	
		// Setup cURL options
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
		// Execute cURL request
		$response = curl_exec($ch);
	
		// Check for errors
		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
			error_log('WhatsApp API error: ' . $error_msg);
		}
	
		// Close cURL
		curl_close($ch);
	
		// Optional: Log response
		// error_log('WhatsApp API response: ' . $response);
	
		return $response;
	}
    
}
