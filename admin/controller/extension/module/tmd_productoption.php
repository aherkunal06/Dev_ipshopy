<?php
class ControllerExtensionModuleTmdProductoption extends Controller {
	private $error = array();
	
	public function install() {
	$this->load->model('extension/tmd_vendorproductoption');
	$this->model_extension_tmd_vendorproductoption->install();
	}	
	
	public function uninstall() {
	$this->load->model('extension/tmd_vendorproductoption');
	$this->model_extension_tmd_vendorproductoption->uninstall();
	}
	
	public function index() {
		$this->load->language('extension/module/tmd_productoption');

		$this->document->setTitle($this->language->get('heading_title1'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			if(isset($this->request->post['tmd_productoption_status'])) {

				$status=$this->request->post['tmd_productoption_status'];
			}
			
			$postdata['module_tmd_productoption_status']=$status;

			$this->model_setting_setting->editSetting('module_tmd_productoption_status',$postdata);
			
			
			$this->model_setting_setting->editSetting('tmd_productoption', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_enablesku'] = $this->language->get('entry_enablesku');
		$data['entry_enableupc'] = $this->language->get('entry_enableupc');
		$data['entry_enableean'] = $this->language->get('entry_enableean');
		$data['entry_enablemodel'] = $this->language->get('entry_enablemodel');
		
		$data['entry_skulabel'] = $this->language->get('entry_skulabel');
		$data['entry_upclabel'] = $this->language->get('entry_upclabel');
		$data['entry_eanlabel'] = $this->language->get('entry_eanlabel');
		$data['entry_modellabel'] = $this->language->get('entry_modellabel');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title1'),
			'href' => $this->url->link('extension/module/tmd_productoption', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/tmd_productoption', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		if (isset($this->request->post['tmd_productoption_sku'])) {
			$data['tmd_productoption_sku'] = $this->request->post['tmd_productoption_sku'];
		} else {
			$data['tmd_productoption_sku'] = $this->config->get('tmd_productoption_sku');
		}
		
		if (isset($this->request->post['tmd_productoption_upc'])) {
			$data['tmd_productoption_upc'] = $this->request->post['tmd_productoption_upc'];
		} else {
			$data['tmd_productoption_upc'] = $this->config->get('tmd_productoption_upc');
		}
		
		if (isset($this->request->post['tmd_productoption_ean'])) {
			$data['tmd_productoption_ean'] = $this->request->post['tmd_productoption_ean'];
		} else {
			$data['tmd_productoption_ean'] = $this->config->get('tmd_productoption_ean');
		}
		
		if (isset($this->request->post['tmd_productoption_model'])) {
			$data['tmd_productoption_model'] = $this->request->post['tmd_productoption_model'];
		} else {
			$data['tmd_productoption_model'] = $this->config->get('tmd_productoption_model');
		}
		
		if (isset($this->request->post['tmd_productoption_skulabel'])) {
			$data['tmd_productoption_skulabel'] = $this->request->post['tmd_productoption_skulabel'];
		} else {
			$data['tmd_productoption_skulabel'] = $this->config->get('tmd_productoption_skulabel');
		}
		
		if (isset($this->request->post['tmd_productoption_upclabel'])) {
			$data['tmd_productoption_upclabel'] = $this->request->post['tmd_productoption_upclabel'];
		} else {
			$data['tmd_productoption_upclabel'] = $this->config->get('tmd_productoption_upclabel');
		}
		
		if (isset($this->request->post['tmd_productoption_eanlabel'])) {
			$data['tmd_productoption_eanlabel'] = $this->request->post['tmd_productoption_eanlabel'];
		} else {
			$data['tmd_productoption_eanlabel'] = $this->config->get('tmd_productoption_eanlabel');
		}
		
		if (isset($this->request->post['tmd_productoption_modellabel'])) {
			$data['tmd_productoption_modellabel'] = $this->request->post['tmd_productoption_modellabel'];
		} else {
			$data['tmd_productoption_modellabel'] = $this->config->get('tmd_productoption_modellabel');
		}
		
		if (isset($this->request->post['tmd_productoption_status'])) {
			$data['tmd_productoption_status'] = $this->request->post['tmd_productoption_status'];
		} else {
			$data['tmd_productoption_status'] = $this->config->get('tmd_productoption_status');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/tmd_productoption', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/tmd_productoption')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
