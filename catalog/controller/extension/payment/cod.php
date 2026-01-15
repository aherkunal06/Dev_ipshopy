<?php
class ControllerExtensionPaymentCod extends Controller {
	public function index() {
	         return $this->load->view('extension/payment/cod');
	}

	public function confirm() {
		$json = array();
		
		if ($this->session->data['payment_method']['code'] == 'cod') {
			$this->load->model('checkout/order');

        // added on 18-03-2025 
        // $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_cod_order_status_id'));
			    // Check if the 'order_id' is an array (i.e., multiple orders)
				if (is_array($this->session->data['order_id'])) {
					// Iterate over each order_id in the array
					foreach ($this->session->data['order_id'] as $order_id) {
						// Add the order history for each order
						$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_cod_order_status_id'));
					}
				} else {
					// If 'order_id' is not an array, process it as a single order
					$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_cod_order_status_id'));
				}
		
			$json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}
}
