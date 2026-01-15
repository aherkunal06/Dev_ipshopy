<?php
class ControllerVendorLogout extends Controller {
	public function index() {
		if ($this->vendor->isLogged()) {
			$this->vendor->logout();
						
			$this->response->redirect($this->url->link('vendor/logout', '', 'SSL'));
		}

		$this->load->language('vendor/logout');
		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/vendor', '', true)
		);

		$data['heading_title']   = $this->language->get('heading_title');
		$data['text_message']    = $this->language->get('text_message');
		$data['button_continue'] = $this->language->get('button_continue');
		$data['continue']        = $this->url->link('vendor/login');
		
		$data['column_left'] 	= $this->load->controller('common/column_left');
		$data['column_right'] 	= $this->load->controller('common/column_right');
		$data['content_top'] 	= $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] 		= $this->load->controller('common/footer');
		$data['header'] 		= $this->load->controller('common/header');
				
		$this->response->setOutput($this->load->view('vendor/success', $data));
	}
}
