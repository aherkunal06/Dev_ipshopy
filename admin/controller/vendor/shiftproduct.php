<?php
class ControllerVendorShiftProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('vendor/shiftproduct');
		$this->load->model('vendor/shiftproduct');
		$this->load->model('vendor/vendor');
		$this->load->model('catalog/manufacturer');

		$this->document->setTitle($this->language->get('heading_title'));
		/* 20 08 2020 */
		$url='';
		/* 20 08 2020 */
		$data['vendors']= $this->model_vendor_vendor->getVendors(array());
		$data['user_token'] = $this->session->data['user_token'];
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_shiftproduct->shiftproduct($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('vendor/shiftproduct', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		// Manufacture
		$this->load->model('vendor/shiftproduct');	
		
		if (isset($this->request->post['product_manufacture'])) {
			$product_manufacturies = $this->request->post['product_manufacture'];
		}  elseif (isset($this->request->get['manufacturer_id'])) {
			$product_manufacturies = $this->model_vendor_shiftproduct->getManufacturer($this->request->get['manufacturer_id']);
		} else {
			$product_manufacturies = array();
		}
 
		$data['product_manufacturies'] = array();

		foreach ($product_manufacturies as $manufacturer_id) {
			$manufacture_info = $this->model_vendor_shiftproduct->getManufacturer($manufacturer_id);
			
			if ($manufacture_info) {
				$data['product_manufacturies'][] = array(
					'manufacturer_id' => $manufacture_info['manufacturer_id'],
					'name'           => $manufacture_info['name'],
				);
			}
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		// Vendors
		$this->load->model('vendor/vendor');	
		
		if (isset($this->request->post['product_vendor'])) {
			$product_vendories = $this->request->post['product_vendor'];
		}  elseif (isset($this->request->get['vendor_id'])) {
			$product_vendories = $this->model_vendor_vendor->getVendor($this->request->get['vendor_id']);
		} else {
			$product_vendories = array();
		}
 
		$data['product_vendories'] = array();

		foreach ($product_vendories as $vendor_id) {
			$vendor_info = $this->model_vendor_vendor->getVendor($vendor_id);
			
			if ($vendor_info) {
				$data['product_vendories'][] = array(
					'vendor_id' => $vendor_info['vendor_id'],
					'name' => $vendor_info['firstname'],
				);
			}
		}

	
		
		$data['action'] = $this->url->link('vendor/shiftproduct', 'user_token=' . $this->session->data['user_token'] . $url, true);		
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['manufacture'])) {
			$data['error_manufacture'] = $this->error['manufacture'];
		} else {
			$data['error_manufacture'] = '';
		}

		if (isset($this->error['vendor'])) {
			$data['error_vendor'] = $this->error['vendor'];
		} else {
			$data['error_vendor'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('vendor/shiftproduct', $data));
	}
	
	public function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'vendor/shiftproduct')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		$error_status = false;;
		if (isset($this->request->post['product_manufacture'])) {
			foreach ($this->request->post['product_manufacture'] as $key => $value) {
				if (!empty($value)){
					$error_status = true;
				}
			}
		}
		
		if (!$error_status) {
			$this->error['manufacture'] = $this->language->get('error_manufacture');
		}

		if (empty($this->request->post['vendor'])) {
			$this->error['vendor'] = $this->language->get('error_vendor');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		
	    return !$this->error;
		
	}
	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('vendor/shiftproduct');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_vendor_shiftproduct->getManufacturers($filter_data);

			foreach ($results as $result) {				
				$json[] = array(
					'manufacturer_id' => $result['manufacturer_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
