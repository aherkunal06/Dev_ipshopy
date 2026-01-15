<?php
	class ControllerVendorWishlist extends Controller {
	private $error = array();	
	public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', 'SSL'));
		}
		$this->load->language('vendor/wishlist');
		$this->load->model('vendor/wishlist');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/wishlist', '', 'SSL')
		);
		
		if (isset($this->session->data['success'])) {
		 	$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';
		
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}
					
					
		if (isset($this->request->get['search'])) {
			$url .= '&search=' . $this->request->get['search'];
		}
				
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		
		$data['heading_title']		= $this->language->get('heading_title');
		$data['text_success']		= $this->language->get('text_success');
		$data['text_list']			= $this->language->get('text_list');
		$data['text_loading']		= $this->language->get('text_loading');
		$data['text_no_results']	= $this->language->get('text_no_results');
		$data['text_confirm']		= $this->language->get('text_confirm');
		$data['text_package']		 	= $this->language->get('text_package');
		$data['text_seepack'] 			= $this->language->get('text_seepack');
		$data['text_mypro'] 			= $this->language->get('text_mypro');
		$data['text_search'] 			= $this->language->get('text_search');
		$data['text_detailview'] 		= $this->language->get('text_detailview');
		
		
		$data['wstdlt'] = $this->url->link('vendor/wishlist/deletewst', '', 'SSL');
		$data['column_left'] 		= $this->load->controller('common/column_left');
		$data['column_right']	 	= $this->load->controller('common/column_right');
		$data['content_top'] 		= $this->load->controller('common/content_top');
		$data['content_bottom'] 	= $this->load->controller('common/content_bottom');
		$data['footer'] 			= $this->load->controller('common/footer');
		$data['header'] 			= $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('vendor/wishlist', $data));

	}
			
	function add(){
		$this->load->language('vendor/wishlist');
		$this->load->model('vendor/wishlist');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_vendor_wishlist->addWishlist($this->request->post['product_id']);
			
			$json['success'] = $this->language->get('text_success');
			
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
				
			
}