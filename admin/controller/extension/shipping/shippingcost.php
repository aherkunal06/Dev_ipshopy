<?php
class ControllerExtensionShippingShippingCost extends Controller {
	private $error = array();

	public function index() {
		if(isset($this->session->data['token'])){
			$tokenchage = 'token=' . $this->session->data['token'];
		} else {
			$tokenchage = 'user_token=' . $this->session->data['user_token'];
		}
		$this->load->language('extension/shipping/shippingcost');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_shippingcost', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', $tokenchage . '&type=shipping', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_none'] = $this->language->get('text_none');

		
		$data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_title'] = $this->language->get('entry_title');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();


		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $tokenchage, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', $tokenchage . '&type=shipping', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/shippingcost', $tokenchage, true)
		);

		$data['action'] = $this->url->link('extension/shipping/shippingcost', $tokenchage, true);

		$data['cancel'] = $this->url->link('marketplace/extension', $tokenchage . '&type=shipping', true);

		if (isset($this->request->post['shipping_shippingcost_status'])) {
			$data['shipping_shippingcost_status'] = $this->request->post['shipping_shippingcost_status'];
		} else {
			$data['shipping_shippingcost_status'] = $this->config->get('shipping_shippingcost_status');
		}

		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
		
		if (isset($this->request->post['shipping_shippingcost_sort_order'])) {
			$data['shipping_shippingcost_sort_order'] = $this->request->post['shipping_shippingcost_sort_order'];
		} else {
			$data['shipping_shippingcost_sort_order'] = $this->config->get('shipping_shippingcost_sort_order');
		}

		if (isset($this->request->post['shipping_shippingcost_tax_class_id'])) {
			$data['shipping_shippingcost_tax_class_id'] = $this->request->post['shipping_shippingcost_tax_class_id'];
		} else {
			$data['shipping_shippingcost_tax_class_id'] = $this->config->get('shipping_shippingcost_tax_class_id');
		}

		if (isset($this->request->post['shipping_shippingcost'])) {
			$data['shipping_shippingcost'] = $this->request->post['shipping_shippingcost'];
		} else {
			$data['shipping_shippingcost'] = $this->config->get('shipping_shippingcost');
		}
		

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/shippingcost', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/shippingcost')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}