<?php
class ControllervendorAttribute extends Controller {
	private $error = array();

	public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', 'SSL'));
		}
		$this->load->language('vendor/attribute');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/attribute');

		$this->getList();
	}

	public function add() {
		$this->load->language('vendor/attribute');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/attribute');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_attribute->addAttribute($this->request->post);

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

			$this->response->redirect($this->url->link('vendor/attribute'));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('vendor/attribute');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/attribute');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_attribute->editAttribute($this->request->get['attribute_id'], $this->request->post);

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

			$this->response->redirect($this->url->link('vendor/attribute'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('vendor/attribute');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/attribute');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $attribute_id) {
				$this->model_vendor_attribute->deleteAttribute($attribute_id);
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

			$this->response->redirect($this->url->link('vendor/attribute'));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'ad.name';
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
		
		$data['cancel'] = $this->url->link('vendor/dashboard');

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
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/attribute')
		);

		$data['add'] = $this->url->link('vendor/attribute/add');
		$data['delete'] = $this->url->link('vendor/attribute/delete');

		$data['attributes'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'vendor_id'  => $this->vendor->getId(),
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$attribute_total = $this->model_vendor_attribute->getTotalAttributes($filter_data);

		$results = $this->model_vendor_attribute->getAttributes($filter_data);

		foreach ($results as $result) {
			$data['attributes'][] = array(
				'attribute_id'    => $result['attribute_id'],
				'name'            => $result['name'],
				'attribute_group' => $result['attribute_group'],
				'sort_order'      => $result['sort_order'],
				'edit'            => $this->url->link('vendor/attribute/edit','attribute_id=' . $result['attribute_id'] . $url, 'SSL')
			);
		}
		
		$data['heading_title']          = $this->language->get('heading_title');
		$data['text_list']           	= $this->language->get('text_list');
		$data['text_no_results'] 		= $this->language->get('text_no_results');
		$data['text_confirm']			= $this->language->get('text_confirm');
		$data['text_male']				= $this->language->get('Male');
		$data['text_female'] 			= $this->language->get('Female');
		$data['text_none'] 				= $this->language->get('text_none');
	 	$data['text_enable']            = $this->language->get('text_enable');
		$data['text_disable']           = $this->language->get('text_disable');
		$data['text_select']            = $this->language->get('text_select');
		$data['column_name']			= $this->language->get('column_name');
		$data['column_attribute_group']	= $this->language->get('column_attribute_group');
		$data['column_sort_order']		= $this->language->get('column_sort_order');
		$data['column_action']			= $this->language->get('column_action');
		$data['button_remove']          = $this->language->get('button_remove');
		$data['button_edit']            = $this->language->get('button_edit');
		$data['button_add']             = $this->language->get('button_add');
		$data['button_filter']          = $this->language->get('button_filter');
		$data['button_delete']          = $this->language->get('button_delete');
		$data['button_approve']         = $this->language->get('button_approve');
		$data['button_desapprove']      = $this->language->get('button_desapprove');
		$data['text_confirm']           = $this->language->get('text_confirm');
		
		
		
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

		$data['sort_name'] = $this->url->link('vendor/attribute','sort=ad.name' . $url, 'SSL');
		$data['sort_attribute_group'] = $this->url->link('vendor/attribute','sort=attribute_group' . $url, 'SSL');
		$data['sort_sort_order'] = $this->url->link('vendor/attribute','sort=a.sort_order' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $attribute_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('vendor/attribute',$url . 'page={page}', 'SSL');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($attribute_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($attribute_total - $this->config->get('config_limit_admin'))) ? $attribute_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $attribute_total, ceil($attribute_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
		
		$data['header'] = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer'] = $this->load->controller('vendor/footer');

		
		$this->response->setOutput($this->load->view('vendor/attribute_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['attribute_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['heading_title']           = $this->language->get('heading_title');
		$data['text_form']               = !isset($this->request->get['information_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_default']            = $this->language->get('text_default');
		$data['text_enable']             = $this->language->get('text_enable');
		$data['text_disable']            = $this->language->get('text_disable');
		$data['text_select']             = $this->language->get('text_select');
		$data['entry_name']        		 = $this->language->get('entry_name');
		$data['entry_attribute_group']   = $this->language->get('entry_attribute_group');
		$data['entry_sort_order']        = $this->language->get('entry_sort_order');
		$data['entry_customer_group']    = $this->language->get('entry_customer_group');
		$data['button_save']             = $this->language->get('button_save');
		$data['button_add']              = $this->language->get('button_add');
		$data['button_remove']           = $this->language->get('button_remove');
		$data['button_cancel']           = $this->language->get('button_cancel');
		$data['text_none'] 				 = $this->language->get('text_none');
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['attribute_group'])) {
			$data['error_attribute_group'] = $this->error['attribute_group'];
		} else {
			$data['error_attribute_group'] = '';
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
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/attribute')
		);

		if (!isset($this->request->get['attribute_id'])) {
			$data['action'] = $this->url->link('vendor/attribute/add');
		} else {
			$data['action'] = $this->url->link('vendor/attribute/edit','attribute_id=' . $this->request->get['attribute_id'] . $url, 'SSL');
		}

		$data['cancel'] = $this->url->link('vendor/attribute');

		if (isset($this->request->get['attribute_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$attribute_info = $this->model_vendor_attribute->getAttribute($this->request->get['attribute_id']);
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['attribute_description'])) {
			$data['attribute_description'] = $this->request->post['attribute_description'];
		} elseif (isset($this->request->get['attribute_id'])) {
			$data['attribute_description'] = $this->model_vendor_attribute->getAttributeDescriptions($this->request->get['attribute_id']);
		} else {
			$data['attribute_description'] = array();
		}

		if (isset($this->request->post['attribute_group_id'])) {
			$data['attribute_group_id'] = $this->request->post['attribute_group_id'];
		} elseif (!empty($attribute_info)) {
			$data['attribute_group_id'] = $attribute_info['attribute_group_id'];
		} else {
			$data['attribute_group_id'] = '';
		}

		$this->load->model('vendor/attribute_group');

		$filter1=array(
			'vendor_id'  => $this->vendor->getId()
		);

		$data['attribute_groups'] = $this->model_vendor_attribute_group->getAttributeGroups($filter1);
		//print_r($data['attribute_groups']);die();

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($attribute_info)) {
			$data['sort_order'] = $attribute_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

		$data['header'] = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer'] = $this->load->controller('vendor/footer');

		
		$this->response->setOutput($this->load->view('vendor/attribute_form', $data));
	}

	protected function validateForm() {
		if (!$this->request->post['attribute_group_id']) {
			$this->error['attribute_group'] = $this->language->get('error_attribute_group');
		}

		foreach ($this->request->post['attribute_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 64)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		
		$this->load->model('vendor/product');

		foreach ($this->request->post['selected'] as $attribute_id) {
			$product_total = $this->model_vendor_product->getTotalProductsByAttributeId($attribute_id);

			if ($product_total) {
				$this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);
			}
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('vendor/attribute');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'vendor_id'   => $this->vendor->getId(),
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_vendor_attribute->getAttributes($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'attribute_id'    => $result['attribute_id'],
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'attribute_group' => $result['attribute_group']
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
