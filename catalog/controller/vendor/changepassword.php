<?php
class ControllerVendorChangepassword extends Controller {
	private $error = array();

	public function index() {
		if (!$this->vendor->isLogged()) {
			/* 23-07-2019 */
			$this->session->data['redirect'] = $this->url->link('vendor/changepassword', '', true);

			$this->response->redirect($this->url->link('vendor/login', '', true));
			/* 23-07-2019 */
			
		}
		
		$data['cancel'] = $this->url->link('vendor/dashboard');
		
		$this->load->language('vendor/changepassword');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		
		$this->load->model('vendor/vendor');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->model_vendor_vendor->editPassword($this->vendor->getid(),$this->request->post);
			
// 			Krishna Salve Changes - 23/05/2025	
			$this->response->redirect($this->url->link('vendor/changesuccess', 'logout=1', true));
		}
		
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('vendor/changepassword', '', 'SSL')
		);
		
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('vendor/login', '', 'SSL'));
		$data['text_form'] = !isset($this->request->get['product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_yes'] 			= $this->language->get('text_yes');
		$data['text_no'] 			= $this->language->get('text_no');
		$data['text_select'] 		= $this->language->get('text_select');
		$data['text_none'] 			= $this->language->get('text_none');
		$data['text_loading'] 		= $this->language->get('text_loading');
		
		$data['entry_oldpassword'] 	= $this->language->get('entry_oldpassword');
		$data['entry_password'] 	= $this->language->get('entry_password');
		$data['entry_confirm'] 		= $this->language->get('entry_confirm');
				
		$data['button_continue'] 	= $this->language->get('button_continue');
	
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['password'])) {
			$data['error_password'] = $this->error['password'];
		} else {
			$data['error_password'] = '';
		}

		if (isset($this->error['confirm'])) {
			$data['error_confirm'] = $this->error['confirm'];
		} else {
			$data['error_confirm'] = '';
		}
		
		$data['action'] = $this->url->link('vendor/changepassword', '', 'SSL');
		
		
		if (isset($this->request->post['oldpassword'])) {
			$data['oldpassword'] = $this->request->post['oldpassword'];
		} else {
			$data['oldpassword'] = '';
		}
		
		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}
		
		if (isset($this->request->post['confirm'])) {
			$data['confirm'] = $this->request->post['confirm'];
		} else {
			$data['confirm'] = '';
		}
		
				
		$data['column_left'] 	= $this->load->controller('vendor/column_left');
		$data['column_right'] 	= $this->load->controller('common/column_right');
		$data['content_top'] 	= $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] 		= $this->load->controller('vendor/footer');
		$data['header'] 		= $this->load->controller('vendor/header');
		
		
		$this->response->setOutput($this->load->view('vendor/changepassword', $data));
	}
	
	private function validate() {
		if (!$this->model_vendor_vendor->verifyPassword($this->vendor->getId(),$this->request->post)) {
			$this->error['warning'] = $this->language->get('error_not_match');
		}
		
		if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
			$this->error['password'] = $this->language->get('error_password');
		}

		if ($this->request->post['confirm'] != $this->request->post['password']) {
			$this->error['confirm'] = $this->language->get('error_confirm');
		}
		
		return !$this->error;
	}	
}
