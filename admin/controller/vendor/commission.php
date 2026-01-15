<?php
class ControllerVendorCommission extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('vendor/commission');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/commission');

		$this->getList();
	}

	public function add() {
		$this->load->language('vendor/commission');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/commission');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_commission->addCommission($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['filter_id'])) {
			$url .= '&filter_id=' . $this->request->get['filter_id'];
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_category'];
			}
			
			if (isset($this->request->get['filter_categoryname'])) {
				$url .= '&filter_categoryname=' . $this->request->get['filter_categoryname'];
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

			$this->response->redirect($this->url->link('vendor/commission', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('vendor/commission');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/commission');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_commission->editCommission($this->request->get['commission_id'], $this->request->post);
			//print_r($this->request->post);die();
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['filter_id'])) {
			$url .= '&filter_id=' . $this->request->get['filter_id'];
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_category'];
			}
			
			if (isset($this->request->get['filter_categoryname'])) {
				$url .= '&filter_categoryname=' . $this->request->get['filter_categoryname'];
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

			$this->response->redirect($this->url->link('vendor/commission', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('vendor/commission');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/commission');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $commission_id) {
				$this->model_vendor_commission->deleteCommission($commission_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['filter_id'])) {
			$url .= '&filter_id=' . $this->request->get['filter_id'];
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . $this->request->get['filter_category'];
			}
			
			if (isset($this->request->get['filter_categoryname'])) {
				$url .= '&filter_categoryname=' . $this->request->get['filter_categoryname'];
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

			$this->response->redirect($this->url->link('vendor/commission', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['filter_id'])) {
			$filter_id = $this->request->get['filter_id'];
		} else {
			$filter_id = '';
		}

		if (isset($this->request->get['filter_category'])) {
			$filter_category = $this->request->get['filter_category'];
		} else {
			$filter_category = '';
		}
		
		/* 17 02 2020 */
		if (isset($this->request->get['filter_categoryname'])) {
			$filter_categoryname = $this->request->get['filter_categoryname'];
		} else {
			$filter_categoryname = null;
		}
		/* 17 02 2020 */
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'commission_id';
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

		if (isset($this->request->get['filter_id'])) {
			$url .= '&filter_id=' . $this->request->get['filter_id'];
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		/* 17 02 2020 */
		if (isset($this->request->get['filter_categoryname'])) {
			$url .= '&filter_categoryname=' . $this->request->get['filter_categoryname'];
		}
		/* 17 02 2020 */
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
			'href' => $this->url->link('vendor/commission', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('vendor/commission/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('vendor/commission/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['categories'] = array();

		$filter_data = array(
			'filter_id'  => $filter_id,
			/* 17 02 2020 */
			'filter_categoryname'  => $filter_categoryname,
			/* 17 02 2020 */
			'filter_category'  => $filter_category,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		$this->load->model('catalog/category');
		$vendor_total = $this->model_vendor_commission->getTotalCommission($filter_data);

		$results = $this->model_vendor_commission->getCommissions($filter_data);

		foreach ($results as $result) {
			$categories = $this->model_catalog_category->getCategory($result['category_id']);
			if(isset($categories['name'])){
				$catnames = $categories['name'];
			} else {
				$catnames ='';
			}
			$data['categories'][] = array(
				'commission_id' => $result['commission_id'],
				'fixed'         => $result['fixed'],
				'percentage'    => $result['percentage'],
				'catnames'      => $catnames,
				'edit'          => $this->url->link('vendor/commission/edit', 'user_token=' . $this->session->data['user_token'] . '&commission_id=' . $result['commission_id'] . $url, true)
			);
		}

		$data['heading_title'] 	    = $this->language->get('heading_title');
		$data['user_token'] 				= $this->session->data['user_token'];
		$data['text_list'] 			= $this->language->get('text_list');
		$data['text_no_results'] 	= $this->language->get('text_no_results');
		$data['text_confirm'] 		= $this->language->get('text_confirm');
		$data['text_enable'] 		= $this->language->get('text_enable');
		$data['text_disable'] 		= $this->language->get('text_disable');
		$data['text_select'] 		= $this->language->get('text_select');
		$data['text_none'] 		    = $this->language->get('text_none');

		$data['column_category'] 	= $this->language->get('column_category');
		$data['column_commission'] 	= $this->language->get('column_commission');
		$data['column_id'] 	        = $this->language->get('column_id');
		$data['column_action'] 		= $this->language->get('column_action');

		$data['entry_firstname'] 	= $this->language->get('entry_firstname');
		$data['entry_status'] 		= $this->language->get('entry_status');
		$data['entry_date'] 		= $this->language->get('entry_date');

		$data['button_add'] 		= $this->language->get('button_add');
		$data['button_edit'] 		= $this->language->get('button_edit');
		$data['button_delete'] 		= $this->language->get('button_delete');
		$data['button_filter'] 		= $this->language->get('button_filter');
		$data['button_approve'] 	= $this->language->get('button_approve');

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

		$data['sort_id']   		 = $this->url->link('vendor/commission', 'user_token=' . $this->session->data['user_token'] . '&sort=id' . $url, true);
		$data['sort_category']   = $this->url->link('vendor/commission', 'user_token=' . $this->session->data['user_token'] . '&sort=category' . $url, true);
		$data['sort_commission'] = $this->url->link('vendor/commission', 'user_token=' . $this->session->data['user_token'] . '&sort=commission' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_id'])) {
			$url .= '&filter_id=' . $this->request->get['filter_id'];
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		
		/* 17 02 2020 */
		if (isset($this->request->get['filter_categoryname'])) {
			$url .= '&filter_categoryname=' . $this->request->get['filter_categoryname'];
		}
		/* 17 02 2020 */

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$pagination = new Pagination();
		$pagination->total = $vendor_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('vendor/commission', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($vendor_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($vendor_total - $this->config->get('config_limit_admin'))) ? $vendor_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $vendor_total, ceil($vendor_total / $this->config->get('config_limit_admin')));
		$data['filter_id']        = $filter_id;
		$data['filter_category']  = $filter_category;
		/* 17 02 2020 */
		$data['filter_categoryname']  = $filter_categoryname;
		/* 17 02 2020 */
		$data['sort'] 			  = $sort;
		$data['order'] 			  = $order;


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('vendor/commission_list', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] 		= !isset($this->request->get['commission_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] 	= $this->language->get('text_enabled');
		$data['text_disabled'] 	= $this->language->get('text_disabled');
		$data['text_default'] 	= $this->language->get('text_default');
		$data['text_percent'] 	= $this->language->get('text_percent');
		$data['text_amount'] 	= $this->language->get('text_amount');
		$data['text_select'] 	= $this->language->get('text_select');
		$data['text_none'] 		= $this->language->get('text_none');

		$data['entry_category'] = $this->language->get('entry_category');
		$data['entry_commission']= $this->language->get('entry_commission');
		$data['tab_general']= $this->language->get('tab_general');
		$data['tab_setcommision']= $this->language->get('tab_setcommision');

		$data['help_commission']= $this->language->get('help_commission');
		$data['button_save'] 	= $this->language->get('button_save');
		$data['button_cancel'] 	= $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

        /* 21 02 2019 */
		if (isset($this->error['category_id'])) {
			$data['error_category_id'] = $this->error['category_id'];
		} else {
			$data['error_category_id'] = '';
		}
		/* 21 02 2019 */

		$url = '';
		
		if (isset($this->request->get['filter_id'])) {
			$url .= '&filter_id=' . $this->request->get['filter_id'];
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . $this->request->get['filter_category'];
		}
		
		if (isset($this->request->get['filter_categoryname'])) {
			$url .= '&filter_categoryname=' . $this->request->get['filter_categoryname'];
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
			'href' => $this->url->link('vendor/commission', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$this->load->model('catalog/category');
		$data['categories'] = $this->model_catalog_category->getCategories($data);

		if (!isset($this->request->get['commission_id'])) {
			$data['action'] = $this->url->link('vendor/commission/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('vendor/commission/edit', 'user_token=' . $this->session->data['user_token'] . '&commission_id=' . $this->request->get['commission_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('vendor/commission', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['commission_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$commission_info=$this->model_vendor_commission->getCommission($this->request->get['commission_id']);

		}
		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['category_id'])) {
			$data['category_id'] = $this->request->post['category_id'];
		} elseif (isset($commission_info['category_id'])){
			$data['category_id'] = $commission_info['category_id'];
		} else {
			$data['category_id'] = '';
		}

        /* 21 02 2019 */
		if (isset($this->request->post['fixed'])) {
			$data['fixed'] = $this->request->post['fixed'];
		} elseif (isset($commission_info['fixed'])){
			$data['fixed'] = $commission_info['fixed'];
		} else {
			$data['fixed'] = '';
		}
		if (isset($this->request->post['percentage'])) {
			$data['percentage'] = $this->request->post['percentage'];
		} elseif (isset($commission_info['percentage'])){
			$data['percentage'] = $commission_info['percentage'];
		} else {
			$data['percentage'] = '';
		}
		/* 21 02 2019 */

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('vendor/commission_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'vendor/commission')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

        /* 21 02 2019 */
		if (empty($this->request->post['category_id'])) {
			$this->error['category_id'] = $this->language->get('error_category_id');
		}
		/* 21 02 2019 */

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'vendor/commission')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

}
