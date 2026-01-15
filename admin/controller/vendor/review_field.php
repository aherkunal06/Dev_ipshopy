<?php
class ControllerVendorReviewField extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('vendor/review_field');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/review_field');

		$this->getList();
	}

	public function add() {
		$this->load->language('vendor/review_field');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/review_field');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_review_field->addReviewField($this->request->post);

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

			$this->response->redirect($this->url->link('vendor/review_field', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('vendor/review_field');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/review_field');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_review_field->editReviewField($this->request->get['rf_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('vendor/review_field', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('vendor/review_field');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/review_field');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $rf_id) {
				$this->model_vendor_review_field->deleteReviewField($rf_id);
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

			$this->response->redirect($this->url->link('vendor/review_field', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}
		
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
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
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
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
			'href' => $this->url->link('vendor/review_field', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('vendor/review_field/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('vendor/review_field/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['review_fields'] = array();

		$filter_data = array(
			'filter_status' => $filter_status,
			'filter_name'   => $filter_name,
			'sort'  	 	=> $sort,
			'order'		 	=> $order,
			'start'			=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'		 	=> $this->config->get('config_limit_admin')
		);

		$review_field_total = $this->model_vendor_review_field->getTotalReviewFields($filter_data);

		$results = $this->model_vendor_review_field->getReviewFields($filter_data);
		
		foreach($results as $result) {
			$data['review_fields'][] = array(
				'rf_id' => $result['rf_id'],
				'field_name'   => $result['field_name'],
				'sort_order'   => $result['sort_order'],
				'status'	   => ($result['status'] ? $this->language->get('text_enable'): $this->language->get('text_disable')),
 				'edit'         => $this->url->link('vendor/review_field/edit', 'user_token=' . $this->session->data['user_token'] . '&rf_id=' . $result['rf_id'] . $url, true)
			);
		} 	

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['text_enable'] = $this->language->get('text_enable');
		$data['text_disable'] = $this->language->get('text_disable');
		$data['text_select'] = $this->language->get('text_select');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_sort_order'] = $this->language->get('column_sort_order');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');
		
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_name'] = $this->language->get('entry_name');
		
		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_filter'] = $this->language->get('button_filter');
		$data['user_token'] = $this->session->data['user_token'];

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

		$data['sort_field_name'] = $this->url->link('vendor/review_field', 'user_token=' . $this->session->data['user_token'] . '&sort=fiels_name' . $url, true);
		$data['sort_sort_order'] = $this->url->link('vendor/review_field', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);
		$data['sort_status'] = $this->url->link('vendor/review_field', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);

		$url = '';
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		if (isset($this->request->get['field_name'])) {
			$url .= '&field_name=' . $this->request->get['field_name'];
		}
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $review_field_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('vendor/review_field', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_field_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($review_field_total - $this->config->get('config_limit_admin'))) ? $review_field_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $review_field_total, ceil($review_field_total / $this->config->get('config_limit_admin')));

		$data['filter_name']    = $filter_name;
		$data['filter_status']  = $filter_status;
		$data['sort'] 			= $sort;
		$data['order']			= $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('vendor/review_field_list', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['rf_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_enable'] = $this->language->get('text_enable');
		$data['text_disable'] = $this->language->get('text_disable');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_store'] = $this->language->get('entry_store');
		$data['help_keyword'] = $this->language->get('help_keyword');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['field_name'])) {
			$data['error_field_name'] = $this->error['field_name'];
		} else {
			$data['error_field_name'] = '';
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
			'href' => $this->url->link('vendor/review_field', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['rf_id'])) {
			$data['action'] = $this->url->link('vendor/review_field/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('vendor/review_field/edit', 'user_token=' . $this->session->data['user_token'] . '&rf_id=' . $this->request->get['rf_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('vendor/review_field', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['rf_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$review_field_info = $this->model_vendor_review_field->getReviewField($this->request->get['rf_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (isset($review_field_info)) {
			$data['sort_order'] = $review_field_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (isset($review_field_info)) {
			$data['status'] = $review_field_info['status'];
		} else {
			$data['status'] = '';
		}
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->post['vendor_review_field_description'])) {
			$data['vendor_review_field_description'] = $this->request->post['vendor_review_field_description'];
		} elseif (isset($review_field_info)) {
			$data['vendor_review_field_description'] = $this->model_vendor_review_field->getVendorReviewFieldDescriptions($this->request->get['rf_id']);
		} else {
			$data['vendor_review_field_description'] = array();
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		
		$this->response->setOutput($this->load->view('vendor/review_field_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'vendor/review_field')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['vendor_review_field_description'] as $language_id => $value) {
			if ((utf8_strlen($value['field_name']) < 3) || (utf8_strlen($value['field_name']) > 64)) {
				$this->error['field_name'][$language_id] = $this->language->get('error_field_name');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'vendor/review_field')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	/* new code vendor filter */

	public function autocomplete(){
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'field_name';
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
		$this->load->model('vendor/review_field');
			
		$filter_data = array(
		'sort'  => $sort,
		'order' => $order,
		'start' => ($page - 1) * $this->config->get('config_limit_admin'),
		'limit' => $this->config->get('config_limit_admin')
		);
		$results = $this->model_vendor_review_field->getReviewFields($filter_data);

		foreach ($results as $result) {

		$json[] = array(
		'rf_id'  => $result['rf_id'],
		'field_name'   => strip_tags(html_entity_decode($result['field_name'], ENT_QUOTES, 'UTF-8'))
		);
		}
		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['field_name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	/* new code vendor filter */

	
}