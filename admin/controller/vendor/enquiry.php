<?php
class ControllerVendorEnquiry extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('vendor/enquiry');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/enquiry');

		$this->getList();
	}

	public function delete() {
		$this->load->language('vendor/enquiry');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/enquiry');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $inquiry_id) {
				$this->model_vendor_enquiry->deleteEnquiry($inquiry_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('vendor/enquiry', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}
		
	protected function getList() {
		if (isset($this->request->get['filter_enqname'])) {
			$filter_enqname = $this->request->get['filter_enqname'];
		} else {
			$filter_enqname = '';
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}
	
		if (isset($this->request->get['filter_product'])) {
			$filter_product = $this->request->get['filter_product'];
		} else {
			$filter_product = null;
		}
		
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}
		
		/* 11 02 2020 */
		if (isset($this->request->get['filter_productname'])) {
			$filter_productname = $this->request->get['filter_productname'];
		} else {
			$filter_productname = '';
		}
		if (isset($this->request->get['filter_customer_name'])) {
			$filter_customer_name = $this->request->get['filter_customer_name'];
		} else {
			$filter_customer_name = '';
		}
		
		if (isset($this->request->get['filter_vendor'])) {
			$filter_vendor = $this->request->get['filter_vendor'];
		} else {
			$filter_vendor = '';
		}
		/* 11 02 2020 */
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date_added';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';
		
		if (isset($this->request->get['filter_enqname'])) {
			$url .= '&filter_enqname=' . $this->request->get['filter_enqname'];
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . $this->request->get['filter_customer'];
		}
		
		/* 11 02 2020 */
		
		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}
		if (isset($this->request->get['filter_customer_name'])) {
			$url .= '&filter_customer_name=' . $this->request->get['filter_customer_name'];
		}
		
		if (isset($this->request->get['filter_productname'])) {
			$url .= '&filter_productname=' . $this->request->get['filter_productname'];
		}
		/* 11 02 2020 */
		
		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . $this->request->get['filter_product'];
		}
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/enquiry', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('vendor/enquiry/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('vendor/enquiry/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['inquires'] = array();

		$filter_data = array(
			'filter_enqname' => $filter_enqname,
			'filter_customer' => $filter_customer,
			'filter_product' => $filter_product,
			'filter_name' => $filter_name,
			/* 11 02 2020 */
			'filter_vendor' => $filter_vendor,
			'filter_customer_name' => $filter_customer_name,
			'filter_productname' => $filter_productname,
			/* 11 02 2020 */
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		$this->load->model('vendor/product');	
		$review_total = $this->model_vendor_enquiry->getTotalEnquiry($filter_data);
		$results = $this->model_vendor_enquiry->getEnquires($filter_data);

		foreach ($results as $result) {
			
			$sellers = $this->model_vendor_enquiry->getVendor($result['vendor_id']);
			/* 11 02 2020 sname */
			if(isset($sellers['sname'])){
				$sname = $sellers['sname'];
			} else {
				$sname ='';
			}
			$customers = $this->model_vendor_enquiry->getCustomer($result['customer_id']);
			if(isset($customers['customername'])){
				$cname = $customers['customername'];
			} else {
				$cname ='Guest';
			}

			$products = $this->model_vendor_product->getProduct($result['product_id']);
			
			if(isset($products['name'])){
				$pname = $products['name'];
			} else {
				$pname ='';
			}

			$data['inquires'][] = array(
				'inquiry_id'  => $result['inquiry_id'],
				'name'  	  => $result['name'],
				'email'   	  => $result['email'],
				'pname'       => $pname,
				'sname'       => $sname,
				'cname'       => $cname,
				'description' => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
				'status'      => ($result['status'] ? $this->language->get('text_enable') : $this->language->get('text_disable')),
				'date_added'  => $result['date_added'],
				'edit'        => $this->url->link('vendor/enquiry/edit', 'user_token=' . $this->session->data['user_token'] . '&inquiry_id=' . $result['inquiry_id'] . $url, true),
				'view'        => $this->url->link('vendor/enquiry/view', 'user_token=' . $this->session->data['user_token'] . '&inquiry_id=' . $result['inquiry_id'] . $url, true),
				// <!--07-03-2019 update-->
				'producturl'        => $this->url->link('vendor/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . $url, true)
				// <!--07-03-2019 update-->
			);
		}

		$data['heading_title'] 	= $this->language->get('heading_title');
		$data['user_token'] 			= $this->session->data['user_token'];
		$data['text_list'] 		= $this->language->get('text_list');
		$data['text_no_results']= $this->language->get('text_no_results');
		$data['text_confirm'] 	= $this->language->get('text_confirm');
		$data['text_enable'] 	= $this->language->get('text_enable');
		$data['text_disable'] 	= $this->language->get('text_disable');
		$data['text_select'] 	= $this->language->get('text_select');
		$data['text_none'] 		= $this->language->get('text_none');

		$data['column_name']	= $this->language->get('column_name');
		$data['column_email']	= $this->language->get('column_email');
		$data['column_product'] = $this->language->get('column_product');
		$data['column_description'] = $this->language->get('column_description');
		$data['column_customer']= $this->language->get('column_customer');
		$data['column_seller'] 	= $this->language->get('column_seller');
		$data['column_status'] 	= $this->language->get('column_status');
		$data['column_date'] 	= $this->language->get('column_date');
		$data['column_action'] 	= $this->language->get('column_action');	
		
		$data['button_add'] 	= $this->language->get('button_add');
		$data['button_edit'] 	= $this->language->get('button_edit');
		$data['button_delete'] 	= $this->language->get('button_delete');
		$data['button_filter'] 	= $this->language->get('button_filter');
		$data['button_view'] 	= $this->language->get('button_view');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . $this->request->get['filter_product'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name']     = $this->url->link('vendor/enquiry', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_email']    = $this->url->link('vendor/enquiry', 'user_token=' . $this->session->data['user_token'] . '&sort=email' . $url, true);
		$data['sort_product']  = $this->url->link('vendor/enquiry', 'user_token=' . $this->session->data['user_token'] . '&sort=product' . $url, true);
		$data['sort_seller']   = $this->url->link('vendor/enquiry', 'user_token=' . $this->session->data['user_token'] . '&sort=seller' . $url, true);
		$data['sort_customer'] = $this->url->link('vendor/enquiry', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url, true);
		$data['sort_status']   = $this->url->link('vendor/enquiry', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		$data['sort_date']     = $this->url->link('vendor/enquiry', 'user_token=' . $this->session->data['user_token'] . '&sort=date' . $url, true);

		$url = '';
		
		if (isset($this->request->get['filter_enqname'])) {
			$url .= '&filter_enqname=' . $this->request->get['filter_enqname'];
		}
		
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . $this->request->get['filter_customer'];
		}
		
		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . $this->request->get['filter_product'];
		}
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		/* 11 02 2020 */
		if (isset($this->request->get['filter_customer_name'])) {
			$url .= '&filter_customer_name=' . $this->request->get['filter_customer_name'];
		}
		
		if (isset($this->request->get['filter_productname'])) {
			$url .= '&filter_productname=' . $this->request->get['filter_productname'];
		}
		
		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}
		/* 11 02 2020 */
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('vendor/enquiry', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($review_total - $this->config->get('config_limit_admin'))) ? $review_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $review_total, ceil($review_total / $this->config->get('config_limit_admin')));
		
		$data['filter_enqname']    = $filter_enqname;
		$data['filter_customer']   = $filter_customer;
		$data['filter_product']    = $filter_product;
		$data['filter_name'] 	   = $filter_name;
		/* 11 02 2020 */
		$data['filter_vendor']     = $filter_vendor;
		$data['filter_customer_name'] = $filter_customer_name;
		$data['filter_productname'] = $filter_productname;
		/* 11 02 2020 */
		$data['sort'] 			  = $sort;
		$data['order'] 			  = $order;

		if(isset($data['filter_name'])){
			$sellers = $this->model_vendor_enquiry->getVendor($data['filter_name']);
		}

		if(isset($sellers['sname'])){
			$data['sellername'] = $sellers['sname'];
		} else {
			$data['sellername'] ='';
		}

		if(isset($data['filter_customer'])){
			$customers = $this->model_vendor_enquiry->getCustomer($data['filter_customer']);
		}
		if(isset($customers['firstname'])){
			$data['customername'] = $customers['firstname'];
		} else {
			$data['customername'] ='';
		}
		
		$this->load->model('vendor/product');
		if(isset($data['filter_product'])){
			$products_info = $this->model_vendor_product->getProduct($data['filter_product']);
		}
		if(isset($products_info['name'])){
			$names = $products_info['name'];
		} else {
			$names = '';
		}
		$data['productname'] = $names;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


		$this->response->setOutput($this->load->view('vendor/enquiry_list', $data));
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'vendor/enquiry')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		return !$this->error;
	}
	
	public function view() {

		$this->load->language('vendor/enquiry');
		$this->load->model('vendor/enquiry');	

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'inquiry_id';
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
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/enquiry', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['messages'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$message_total = $this->model_vendor_enquiry->getTotalMessages($this->request->get['inquiry_id']);
		$results = $this->model_vendor_enquiry->getMessages($this->request->get['inquiry_id']);

		foreach ($results as $result) {

			$data['messages'][] = array(
				'msg_id'  => $result['msg_id'],
				'message' => html_entity_decode($result['message']),
				'date_added'    => $result['date_added'],
			);
			//print_r($data['messages']);die();
		}

		$data['heading_title'] 	= $this->language->get('heading_title');
		$data['user_token'] 			= $this->session->data['user_token'];
		$data['text_list'] 		= $this->language->get('text_list');
		$data['text_no_results']= $this->language->get('text_no_results');
		$data['text_confirm'] 	= $this->language->get('text_confirm');
		$data['text_enable'] 	= $this->language->get('text_enable');
		$data['text_disable'] 	= $this->language->get('text_disable');
		$data['text_select'] 	= $this->language->get('text_select');
		$data['text_none'] 		= $this->language->get('text_none');
		$data['text_view'] 		= $this->language->get('text_view');
		$data['text_message'] 	= $this->language->get('text_message');

		$data['column_name']	= $this->language->get('column_name');
		$data['column_email']	= $this->language->get('column_email');
		$data['column_product'] = $this->language->get('column_product');
		$data['column_customer']= $this->language->get('column_customer');
		$data['column_seller'] 	= $this->language->get('column_seller');
		$data['column_status'] 	= $this->language->get('column_status');
		$data['column_date'] 	= $this->language->get('column_date');
		$data['column_action'] 	= $this->language->get('column_action');	
		$data['column_message']= $this->language->get('column_message');	
		$data['column_addview']	= $this->language->get('column_addview');	
		$data['column_telephone']	= $this->language->get('column_telephone');	
		$data['column_details']	= $this->language->get('column_details');	
		$data['column_enquirydetails']	= $this->language->get('column_enquirydetails');	
		
		$data['button_add'] 	= $this->language->get('button_add');
		$data['button_edit'] 	= $this->language->get('button_edit');
		$data['button_delete'] 	= $this->language->get('button_delete');
		$data['button_filter'] 	= $this->language->get('button_filter');
		$data['button_view'] 	= $this->language->get('button_view');
		$data['button_send'] 	= $this->language->get('button_send');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['message'])) {
			$data['error_message'] = $this->error['message'];
		} else {
			$data['error_message'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		if (isset($this->request->get['inquiry_id'])) {
			$inquiry_info=$this->model_vendor_enquiry->getEnquiry($this->request->get['inquiry_id']);
		}

		if(isset($inquiry_info['name'])){
			$data['name'] = $inquiry_info['name'];
		} else {
			$data['name'] = '';
		}

		if(isset($inquiry_info['email'])){
			$data['email'] = $inquiry_info['email'];
	    } else {
	    	$data['email'] = '';
	    }

	    if(isset($inquiry_info['telephone'])) {
			$data['telephone'] = $inquiry_info['telephone'];
		} else {
			$data['telephone'] = '';
		}

		if(isset($inquiry_info['customer_id'])){
			$data['customer_id'] = $inquiry_info['customer_id'];
		} else {
			$data['customer_id'] = '';
		}
				
		$this->load->model('sale/customer');
		if(isset($inquiry_info['customer_id'])){
			$customer_info = $this->model_sale_customer->getCustomer($inquiry_info['customer_id']);
			
			if ($customer_info) {
				$data['customer'] = $customer_info['firstname'];
			} else {
				$data['customer'] = '';
			}
		} else {
			$data['customer'] = '';
		}
		
		if(isset($inquiry_info['vendor_id'])){
			$data['vendor_id'] = $inquiry_info['vendor_id'];
		} else {
			$data['vendor_id'] = '';
		}

		$this->load->model('vendor/vendor');
		if(isset($inquiry_info['vendor_id'])){
			$vendor_info = $this->model_vendor_vendor->getVendor($inquiry_info['vendor_id']);
			if ($vendor_info) {
				$data['vendor'] = $vendor_info['firstname'];
			} else {
				$data['vendor'] = '';
			}
		} else {
			$data['vendor']='';
		}

		if(isset($inquiry_info['product_id'])){
			$data['product_id'] = $inquiry_info['product_id'];
		} else {
			$data['product_id'] = '';
		}

		$this->load->model('vendor/product');
		if(isset($inquiry_info['product_id'])){
			$product_info = $this->model_vendor_product->getProduct($inquiry_info['product_id']);
			if ($product_info) {
				$data['product'] = $product_info['name'];
			} else {
				$data['product'] = '';
			}
		} else {
			$data['product'] = '';
		}

		if(isset($inquiry_info['inquiry_id'])){
			$data['inquiry_id'] = $inquiry_info['inquiry_id'];
		} else {
			$data['inquiry_id'] = '';
		}

		if(isset($inquiry_info['description'])){
			$data['description'] = $inquiry_info['description'];
		} else {
			$data['description'] = '';
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$pagination = new Pagination();
		$pagination->total = $message_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('customer/tmd_customerimage', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($message_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($message_total - $this->config->get('config_limit_admin'))) ? $message_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $message_total, ceil($message_total / $this->config->get('config_limit_admin')));

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


		$this->response->setOutput($this->load->view('vendor/enquiry_view', $data));
	}

	public function sendmessage(){
		$this->load->language('vendor/enquiry');
		$this->load->model('vendor/enquiry');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_vendor_enquiry->addMessage($this->request->post);
			$json['success'] = $this->language->get('text_success');					
		}					
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
	}
	/* 28 02 2020 */
		public function autocomplete(){
		
		if (isset($this->request->get['filter_enqname'])) {
			$filter_enqname = $this->request->get['filter_enqname'];
		} else {
			$filter_enqname = '';
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
		$this->load->model('vendor/enquiry');
			
		$filter_data = array(
		'sort'  => $sort,
		'order' => $order,
		'filter_enqname' => $filter_enqname,
		'start'            => 0,
		'limit'            => 5
		);
		$enqnames = $this->model_vendor_enquiry->getEnquires($filter_data);
		foreach ($enqnames as $enqname) {

		$json[] = array(
		'inquiry_id'  => $enqname['inquiry_id'],
		'name'              => strip_tags(html_entity_decode($enqname['name'], ENT_QUOTES, 'UTF-8'))
		);
		}
		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	/* 28 02 2020 */
}