<?php
class ControllerVendorNotification extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('vendor/notification');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/notification');

		$this->getList();
	}

	public function add() {
		$this->load->language('vendor/notification');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/notification');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_notification->addNotification($this->request->post);

			
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

			$this->response->redirect($this->url->link('vendor/notification', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}


		$this->getForm();
	}

	public function edit() {
		$this->load->language('vendor/notification');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/notification');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_notification->editNotification($this->request->get['notification_id'], $this->request->post);
			
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

			$this->response->redirect($this->url->link('vendor/notification', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('vendor/notification');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/notification');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $notification_id) {
				$this->model_vendor_notification->deleteNotification($notification_id);
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

			$this->response->redirect($this->url->link('vendor/notification', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
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
			'href' => $this->url->link('vendor/notification', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('vendor/notification/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('vendor/notification/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['notifications'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);


		$notification_total = $this->model_vendor_notification->getTotalNotifications($filter_data);
		$results = $this->model_vendor_notification->getNotifications($filter_data);
		
		foreach ($results as $result) {
			
			$data['notifications'][] = array(
				'notification_id' => $result['notification_id'],
				'message'    	  => html_entity_decode($result['message']),
				'date'			  => $result['date'],
				'edit'            => $this->url->link('vendor/notification/edit', 'user_token=' . $this->session->data['user_token'] . '&notification_id=' . $result['notification_id'] . $url, true)
			);
			//print_r($data['notifications']);die();
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_message'] = $this->language->get('column_message');
		$data['column_date'] = $this->language->get('column_date');
		$data['column_sort_order'] = $this->language->get('column_sort_order');
		$data['column_action'] = $this->language->get('column_action');

		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');

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

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_message'] = $this->url->link('vendor/notification', 'user_token=' . $this->session->data['user_token'] . '&sort=message' . $url, true);
		$data['sort_date'] = $this->url->link('vendor/notification', 'user_token=' . $this->session->data['user_token'] . '&sort=date' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $notification_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('vendor/notification', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($notification_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($notification_total - $this->config->get('config_limit_admin'))) ? $notification_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $notification_total, ceil($notification_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('vendor/notification_list', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['notification_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_percent'] = $this->language->get('text_percent');
		$data['text_amount'] = $this->language->get('text_amount');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_select'] = $this->language->get('text_select');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_customer'] = $this->language->get('entry_customer');
		$data['entry_seller'] = $this->language->get('entry_seller');
		$data['entry_message'] = $this->language->get('entry_message');
		$data['entry_date'] = $this->language->get('entry_date');
		
		$data['help_keyword'] = $this->language->get('help_keyword');
		$data['help_category'] = $this->language->get('help_category');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
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
			'href' => $this->url->link('vendor/notification', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['notifications']=array();
		$data['notifications'][] = array(
			'type'  		=> $this->language->get('text_all'),
			'value' 		=> 'all'
		);
		$data['notifications'][] = array(
			'type'  		=> $this->language->get('text_customer'),
			'value' 		=> 'customer'
		);
		$data['notifications'][] = array(
			'type'  		=> $this->language->get('text_seller'),
			'value' 		=> 'seller'
		);
		$data['notifications'][] = array(
			'type'  		=> $this->language->get('text_select_customer'),
			'value' 		=> 'select_customer'
		);
		$data['notifications'][] = array(
			'type'  		=> $this->language->get('text_select_seller'),
			'value' 		=> 'select_seller'
		);

		if (!isset($this->request->get['notification_id'])) {
			$data['action'] = $this->url->link('vendor/notification/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('vendor/notification/edit', 'user_token=' . $this->session->data['user_token'] . '&notification_id=' . $this->request->get['notification_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('vendor/notification', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['notification_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$notification_info = $this->model_vendor_notification->getNotification($this->request->get['notification_id']);
			//print_r($notification_info);die();
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['notification_message'])) {
			$data['notification_message'] = $this->request->post['notification_message'];
		} elseif (isset($notification_info)) {
			$data['notification_message'] = $this->model_vendor_notification->getNotificationMessage($this->request->get['notification_id']);
		} else {
			$data['notification_message'] = array();
		}

		if (isset($this->request->post['type'])){
			$data['type'] = $this->request->post['type'];
		} elseif (!empty($notification_info['type'])){
			$data['type'] = $notification_info['type'];
		} else {
			$data['type'] = '';
		}

		if (isset($this->request->post['date'])){
			$data['date'] = $this->request->post['date'];
		} elseif (!empty($notification_info)){
			$data['date'] = $notification_info['date'];
		} else {
			$data['date'] = '';
		}
		
		$this->load->model('customer/customer');
		if (isset($this->request->post['notification_customer'])) {
			$customers = $this->request->post['notification_customer'];
		} elseif (isset($this->request->get['notification_id'])) {
			$customers = $this->model_vendor_notification->getNotificationCustomer($this->request->get['notification_id']);
		} else {
			$customers = array();
		}
		//print_r($customers);die();

		$data['notification_customers'] = array();

		foreach ($customers as $customer_id) {
			$customer_info = $this->model_customer_customer->getCustomer($customer_id);

			if ($customer_info) {
				$data['notification_customers'][] = array(
					'customer_id' => $customer_info['customer_id'],
					'firstname'   => $customer_info['firstname'],
				);
				//print_r($data['notification_customers']);die();
			}
		}
		
		$this->load->model('vendor/vendor');
		if (isset($this->request->post['notification_seller'])) {
			$sellers = $this->request->post['notification_seller'];
		} elseif (isset($this->request->get['notification_id'])) {
			$sellers = $this->model_vendor_notification->getNotificationSeller($this->request->get['notification_id']);
		} else {
			$sellers = array();
		}
		
		$data['notification_sellers'] = array();

		foreach ($sellers as $vendor_id) {
			$seller_info = $this->model_vendor_vendor->getVendor($vendor_id);

			if ($seller_info) {
				$data['notification_sellers'][] = array(
					'vendor_id' => $seller_info['vendor_id'],
					'firstname'   => $seller_info['firstname'],
				);
				//print_r($data['notification_sellers']);die();
			}
		}

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		
		$this->response->setOutput($this->load->view('vendor/notification_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'vendor/notification')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'vendor/notification')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	public function autocomplete(){
		
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
		$this->load->model('customer/customer');
			
		$filter_data = array(
		'sort'  => $sort,
		'order' => $order,
		//'filter_name' => $filter_name,
		'start' => ($page - 1) * $this->config->get('config_limit_admin'),
		'limit' => $this->config->get('config_limit_admin')
		);
		$accounts = $this->model_customer_customer->getCustomers($filter_data);
		foreach ($accounts as $account) {

		$json[] = array(
		'customer_id'  => $account['customer_id'],
		'firstname'   => strip_tags(html_entity_decode($account['firstname'], ENT_QUOTES, 'UTF-8'))
		);
		}
		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['firstname'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	
}