<?php
class ControllerSaleReturn extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('sale/return');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/return');

		$this->getList();
	}

	public function add() {
		$this->load->language('sale/return');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/return');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_sale_return->addReturn($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_return_id'])) {
				$url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
			}

			if (isset($this->request->get['filter_order_id'])) {
				$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
			}

			if (isset($this->request->get['filter_customer'])) {
				$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_product'])) {
				$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_return_status_id'])) {
				$url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['filter_date_modified'])) {
				$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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

			$this->response->redirect($this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('sale/return');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/return');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_sale_return->editReturn($this->request->get['return_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_return_id'])) {
				$url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
			}

			if (isset($this->request->get['filter_order_id'])) {
				$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
			}

			if (isset($this->request->get['filter_customer'])) {
				$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_product'])) {
				$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_return_status_id'])) {
				$url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['filter_date_modified'])) {
				$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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

			$this->response->redirect($this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('sale/return');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/return');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $return_id) {
				$this->model_sale_return->deleteReturn($return_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_return_id'])) {
				$url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
			}

			if (isset($this->request->get['filter_order_id'])) {
				$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
			}

			if (isset($this->request->get['filter_customer'])) {
				$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_product'])) {
				$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_return_status_id'])) {
				$url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['filter_date_modified'])) {
				$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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

			$this->response->redirect($this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['filter_return_id'])) {
			$filter_return_id = $this->request->get['filter_return_id'];
		} else {
			$filter_return_id = '';
		}

		if (isset($this->request->get['filter_order_id'])) {
			$filter_order_id = $this->request->get['filter_order_id'];
		} else {
			$filter_order_id = '';
		}

		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}

		if (isset($this->request->get['filter_product'])) {
			$filter_product = $this->request->get['filter_product'];
		} else {
			$filter_product = '';
		}

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = '';
		}

		if (isset($this->request->get['filter_return_status_id'])) {
			$filter_return_status_id = $this->request->get['filter_return_status_id'];
		} else {
			$filter_return_status_id = '';
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$filter_date_modified = $this->request->get['filter_date_modified'];
		} else {
			$filter_date_modified = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'r.return_id';
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

		if (isset($this->request->get['filter_return_id'])) {
			$url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
		}

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_return_status_id'])) {
			$url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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
			'href' => $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('sale/return/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('sale/return/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['returns'] = array();

		$filter_data = array(
			'filter_return_id'        => $filter_return_id,
			'filter_order_id'         => $filter_order_id,
			'filter_customer'         => $filter_customer,
			'filter_product'          => $filter_product,
			'filter_model'            => $filter_model,
			'filter_return_status_id' => $filter_return_status_id,
			'filter_date_added'       => $filter_date_added,
			'filter_date_modified'    => $filter_date_modified,
			'sort'                    => $sort,
			'order'                   => $order,
			'start'                   => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                   => $this->config->get('config_limit_admin')
		);

		$return_total = $this->model_sale_return->getTotalReturns($filter_data);

		$results = $this->model_sale_return->getReturns($filter_data);
		
        

		foreach ($results as $result) {
		    // added code product return on 20-05-2025 ----------------------------
		    if (!$result['approved']) {
				$approve = $this->url->link('sale/return/approveReturn', 'user_token=' . $this->session->data['user_token'] . '&return_id=' . $result['return_id'] . $url, true);
			} else {
				$approve = '';
			}
			if ($result['approved']) {
				$disapproved = $this->url->link('sale/return/disapproveReturn', 'user_token=' . $this->session->data['user_token'] . '&return_id=' . $result['return_id'] .  $url, true);
			} else {
				$disapproved = '';
			}
			if( $result['approved']==1){
				$status = $this->language->get('text_enable');
			} elseif($result['approved']==0) {
				$status = $this->language->get('text_waitingapproved');
			} else  {
				$status = $this->language->get('text_disable');
			}
			//-------------------------------------------------------------------------
			
			$data['returns'][] = array(
				'return_id'     => $result['return_id'],
				'order_id'      => $result['order_id'],
				'customer'      => $result['customer'],
				'product'       => $result['product'],
				'model'         => $result['model'],
				'return_status' => $result['return_status'],
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'edit'          => $this->url->link('sale/return/edit', 'user_token=' . $this->session->data['user_token'] . '&return_id=' . $result['return_id'] . $url, true),
				// added code for product return on 20-05-2025
				'image'         => $this->url->link('sale/return_images', 'user_token=' . $this->session->data['user_token'] . '&return_id=' . $result['return_id'] . $url, true),
				'approve'		  => $approve,
				'disapproved'	  => $disapproved
				//-------------------------------------
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
		} elseif (isset($this->error['warning'])) {
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

		if (isset($this->request->get['filter_return_id'])) {
			$url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
		}

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_return_status_id'])) {
			$url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_return_id'] = $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . '&sort=r.return_id' . $url, true);
		$data['sort_order_id'] = $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . '&sort=r.order_id' . $url, true);
		$data['sort_customer'] = $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url, true);
		$data['sort_product'] = $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . '&sort=r.product' . $url, true);
		$data['sort_model'] = $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . '&sort=r.model' . $url, true);
		$data['sort_status'] = $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		$data['sort_date_added'] = $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . '&sort=r.date_added' . $url, true);
		$data['sort_date_modified'] = $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . '&sort=r.date_modified' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_return_id'])) {
			$url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
		}

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_return_status_id'])) {
			$url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($return_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($return_total - $this->config->get('config_limit_admin'))) ? $return_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $return_total, ceil($return_total / $this->config->get('config_limit_admin')));

		$data['filter_return_id'] = $filter_return_id;
		$data['filter_order_id'] = $filter_order_id;
		$data['filter_customer'] = $filter_customer;
		$data['filter_product'] = $filter_product;
		$data['filter_model'] = $filter_model;
		$data['filter_return_status_id'] = $filter_return_status_id;
		$data['filter_date_added'] = $filter_date_added;
		$data['filter_date_modified'] = $filter_date_modified;

		$this->load->model('localisation/return_status');

		$data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/return_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['return_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['return_id'])) {
			$data['return_id'] = $this->request->get['return_id'];
		} else {
			$data['return_id'] = 0;
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['order_id'])) {
			$data['error_order_id'] = $this->error['order_id'];
		} else {
			$data['error_order_id'] = '';
		}

		if (isset($this->error['firstname'])) {
			$data['error_firstname'] = $this->error['firstname'];
		} else {
			$data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$data['error_lastname'] = $this->error['lastname'];
		} else {
			$data['error_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['telephone'])) {
			$data['error_telephone'] = $this->error['telephone'];
		} else {
			$data['error_telephone'] = '';
		}

		if (isset($this->error['product'])) {
			$data['error_product'] = $this->error['product'];
		} else {
			$data['error_product'] = '';
		}

		if (isset($this->error['model'])) {
			$data['error_model'] = $this->error['model'];
		} else {
			$data['error_model'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_return_id'])) {
			$url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
		}

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_return_status_id'])) {
			$url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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
			'href' => $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['return_id'])) {
			$data['action'] = $this->url->link('sale/return/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('sale/return/edit', 'user_token=' . $this->session->data['user_token'] . '&return_id=' . $this->request->get['return_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['return_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$return_info = $this->model_sale_return->getReturn($this->request->get['return_id']);
		}

		if (isset($this->request->post['order_id'])) {
			$data['order_id'] = $this->request->post['order_id'];
		} elseif (!empty($return_info)) {
			$data['order_id'] = $return_info['order_id'];
		} else {
			$data['order_id'] = '';
		}

		if (isset($this->request->post['date_ordered'])) {
			$data['date_ordered'] = $this->request->post['date_ordered'];
		} elseif (!empty($return_info)) {
			$data['date_ordered'] = ($return_info['date_ordered'] != '0000-00-00' ? $return_info['date_ordered'] : '');
		} else {
			$data['date_ordered'] = '';
		}

		if (isset($this->request->post['customer'])) {
			$data['customer'] = $this->request->post['customer'];
		} elseif (!empty($return_info)) {
			$data['customer'] = $return_info['customer'];
		} else {
			$data['customer'] = '';
		}

		if (isset($this->request->post['customer_id'])) {
			$data['customer_id'] = $this->request->post['customer_id'];
		} elseif (!empty($return_info)) {
			$data['customer_id'] = $return_info['customer_id'];
		} else {
			$data['customer_id'] = '';
		}

		if (isset($this->request->post['firstname'])) {
			$data['firstname'] = $this->request->post['firstname'];
		} elseif (!empty($return_info)) {
			$data['firstname'] = $return_info['firstname'];
		} else {
			$data['firstname'] = '';
		}

		if (isset($this->request->post['lastname'])) {
			$data['lastname'] = $this->request->post['lastname'];
		} elseif (!empty($return_info)) {
			$data['lastname'] = $return_info['lastname'];
		} else {
			$data['lastname'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (!empty($return_info)) {
			$data['email'] = $return_info['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} elseif (!empty($return_info)) {
			$data['telephone'] = $return_info['telephone'];
		} else {
			$data['telephone'] = '';
		}

		if (isset($this->request->post['product'])) {
			$data['product'] = $this->request->post['product'];
		} elseif (!empty($return_info)) {
			$data['product'] = $return_info['product'];
		} else {
			$data['product'] = '';
		}

		if (isset($this->request->post['product_id'])) {
			$data['product_id'] = $this->request->post['product_id'];
		} elseif (!empty($return_info)) {
			$data['product_id'] = $return_info['product_id'];
		} else {
			$data['product_id'] = '';
		}

		if (isset($this->request->post['model'])) {
			$data['model'] = $this->request->post['model'];
		} elseif (!empty($return_info)) {
			$data['model'] = $return_info['model'];
		} else {
			$data['model'] = '';
		}

		if (isset($this->request->post['quantity'])) {
			$data['quantity'] = $this->request->post['quantity'];
		} elseif (!empty($return_info)) {
			$data['quantity'] = $return_info['quantity'];
		} else {
			$data['quantity'] = '';
		}

		if (isset($this->request->post['opened'])) {
			$data['opened'] = $this->request->post['opened'];
		} elseif (!empty($return_info)) {
			$data['opened'] = $return_info['opened'];
		} else {
			$data['opened'] = '';
		}

		if (isset($this->request->post['return_reason_id'])) {
			$data['return_reason_id'] = $this->request->post['return_reason_id'];
		} elseif (!empty($return_info)) {
			$data['return_reason_id'] = $return_info['return_reason_id'];
		} else {
			$data['return_reason_id'] = '';
		}

		$this->load->model('localisation/return_reason');

		$data['return_reasons'] = $this->model_localisation_return_reason->getReturnReasons();

		if (isset($this->request->post['return_action_id'])) {
			$data['return_action_id'] = $this->request->post['return_action_id'];
		} elseif (!empty($return_info)) {
			$data['return_action_id'] = $return_info['return_action_id'];
		} else {
			$data['return_action_id'] = '';
		}

		$this->load->model('localisation/return_action');

		$data['return_actions'] = $this->model_localisation_return_action->getReturnActions();

		if (isset($this->request->post['comment'])) {
			$data['comment'] = $this->request->post['comment'];
		} elseif (!empty($return_info)) {
			$data['comment'] = $return_info['comment'];
		} else {
			$data['comment'] = '';
		}

		if (isset($this->request->post['return_status_id'])) {
			$data['return_status_id'] = $this->request->post['return_status_id'];
		} elseif (!empty($return_info)) {
			$data['return_status_id'] = $return_info['return_status_id'];
		} else {
			$data['return_status_id'] = '';
		}

		$this->load->model('localisation/return_status');

		$data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/return_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'sale/return')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (empty($this->request->post['order_id'])) {
			$this->error['order_id'] = $this->language->get('error_order_id');
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if ((utf8_strlen($this->request->post['product']) < 1) || (utf8_strlen($this->request->post['product']) > 255)) {
			$this->error['product'] = $this->language->get('error_product');
		}

		if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
			$this->error['model'] = $this->language->get('error_model');
		}

		if (empty($this->request->post['return_reason_id'])) {
			$this->error['reason'] = $this->language->get('error_reason');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'sale/return')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function history() {
		$this->load->language('sale/return');

		$this->load->model('sale/return');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['histories'] = array();

		$results = $this->model_sale_return->getReturnHistories($this->request->get['return_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$data['histories'][] = array(
				'notify'     => $result['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
				'status'     => $result['status'],
				'comment'    => nl2br($result['comment']),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$history_total = $this->model_sale_return->getTotalReturnHistories($this->request->get['return_id']);

		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('sale/return/history', 'user_token=' . $this->session->data['user_token'] . '&return_id=' . $this->request->get['return_id'] . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

		$this->response->setOutput($this->load->view('sale/return_history', $data));
	}
	
	public function addHistory() {
		$this->load->language('sale/return');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/return')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('sale/return');

			$this->model_sale_return->addReturnHistory($this->request->get['return_id'], $this->request->post['return_status_id'], $this->request->post['comment'], $this->request->post['notify']);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}	
	
	// added code for product return 20-05-2025 ------------- 
	
	public function approveReturn() {
		$json = [];
	
		if (!$this->user->hasPermission('modify', 'sale/return')) {
			$json['error'] = "Permission denied!";
		} else {
			$return_id = $this->request->post['return_id'];
	        
			// Load necessary models
			$this->load->model('sale/return');
			$this->load->model('sale/order');
			$this->load->model('catalog/product');
			
	
			// Fetch return order details
			$return_info = $this->model_sale_return->getReturn($return_id);
			var_dump("return info ========>",$return_info);
			if (!$return_info) {
				$json['error'] = "Invalid Return ID!";
				$this->response->setOutput(json_encode($json));
				return;
			} 
	
			$order_id = $return_info['order_id'];
			$order_info = $this->model_sale_order->getOrder($order_id);
// 			var_dump($order_info);
			if (!$order_info || empty($order_info)){
				$json['error'] = "Invalid Order ID!";
				$this->response->setOutput(json_encode($json));
				return;
			}
	
			// Fetch product details
			$product_info = $this->model_catalog_product->getProduct($return_info['product_id']);
			var_dump($order_info);
			if (!$product_info) {
				$json['error'] = "Invalid Product!";
				$this->response->setOutput(json_encode($json));
				return;
			}
	
			// Construct the payload dynamically
			$shipway_payload = [
				"order_id" => $order_info['order_id'],
				"return_order_status" => "R",
			    // "carrier_id" => "80165",
				"return_warehouse_id" => "58067",
				"refund_payment_id" => "1",
				"transfer_details" => [
					"account_number" => "12345678904444",
					"phone" => "xxxxxxxx78",
					"ifsc_code" => "HDFC0004393",
					"account_type" => "saving",
					"beneficiary_name" => "Pooja",
					"bank_name" => "HDFC Bank"
				],
				"products" => [
					[
						"product" => $product_info['name'],
						"price" => $product_info['total'],
				// 		"price" => "30",
						"product_code" => $product_info['model'],
						// "hsn_code" => "213456",
						// "amount" => "1",
						// "discount" => "0",
						// "tax_rate" => "5",
						// "tax_title" => "IGST",
						 "return_reason_id" => "79356",
						// "return_products_images" => [
						// 	"https://example.com/sample1.jpg",
						// 	"https://example.com/sample2.jpg"
						// ],
						"customer_notes" => $return_info['comment'],
						// "variants" => "red"
					]
				],
				"discount" => "0",
				// "shipping" => $order_info['shipping_cost'],
				"order_total" => $order_info['total'],
				"gift_card_amt" => "100",
				"taxes" => "40",
				// "payment_type" => $order_info['payment_code'],
				"payment_type" => ($order_info['payment_code'] == 'cod') ? 'C' : 'P',
				"email" => $order_info['email'],
				"billing_address" => $order_info['payment_address_1'],
				"billing_address2" => $order_info['payment_address_2'],
				"billing_city" => $order_info['payment_city'],
				"billing_state" => $order_info['payment_zone'],
				"billing_country" => $order_info['payment_country'],
				"billing_firstname" => $order_info['payment_firstname'],
				"billing_lastname" => $order_info['payment_lastname'],
				"billing_phone" => $order_info['telephone'],
				"billing_zipcode" => $order_info['payment_postcode'],
				"shipping_address" => $order_info['shipping_address_1'],
				"shipping_address2" => $order_info['shipping_address_2'],
				"shipping_city" => $order_info['shipping_city'],
				"shipping_state" => $order_info['shipping_zone'],
				"shipping_country" => $order_info['shipping_country'],
				"shipping_firstname" => $order_info['shipping_firstname'],
				"shipping_lastname" => $order_info['shipping_lastname'],
				"shipping_phone" => $order_info['telephone'],
				"shipping_zipcode" => $order_info['shipping_postcode'],
				"order_weight" => "110",
				"box_length" => "20",
				"box_breadth" => "15",
				"box_height" => "10",
				"order_date" => $order_info['date_added'],
				"quality_check" => 1,
				"qc_checkurl" => [
					"https://example.com/sample1.jpg",
					"https://example.com/sample2.jpg"
				],
				"qc_text_capture" => [
					[
						"qc_text_capture_label" => "IMEI",
						"qc_text_capture_value" => "TEXT",
						"value_to_check" => "3421"
					]
				]
			];
	       //  var_dump($shipway_payload);  
			// Call Shipway API
			$shipway_api_url = "https://app.shipway.com/api/Createreturns";
			$shipway_username = "ipshopy1@gmail.com";
			// $api_key = "96V1f01z291K02U1jg35s5Sb93gB4QmY"; // Replace with actual API Key
			$shipway_key = "96V1f01z291K02U1jg35s5Sb93gB4QmY";

			$credentials = base64_encode("$shipway_username:$shipway_key");

			$ch = curl_init($shipway_api_url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				// "Authorization: Basic " . base64_encode($api_key),
				"Authorization: Basic " . $credentials,
				"Content-Type: application/json"
			]);
			// curl_setopt($ch, CURLOPT_POST, true);
			// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($shipway_payload));
			// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($shipway_payload));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Try disabling SSL verification (not recommended for production)
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
				
			$response = curl_exec($ch);
		
// 			var_dump($response);
			$json['res'] =$response;
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$error = curl_error($ch);
			curl_close($ch);
// 			var_dump($http_code);
			$shipwayResponse = json_decode($response, true);

			if ($http_code == 200 || (
				(isset($shipwayResponse["success"]) && $shipwayResponse["success"] == 1)
			)) {


				// $awb_no = isset($shipwayResponse["awb_response"]["AWB"]) ? $shipwayResponse["awb_response"]["AWB"] : null;
				// $label_url = isset($shipwayResponse["awb_response"]["shipping_url"]) ? $shipwayResponse["awb_response"]["shipping_url"] : null;
				$rma_no = isset($shipwayResponse["create_return_response"]["rma_no"]) ? $shipwayResponse["create_return_response"]["rma_no"] : null;

				$json['success'] = "Return shipment created successfully!";
		
				// $this->model_sale_return->approve($return_id,$rma_no); 
				$product_id = $return_info['product_id']; // already fetched above
			$this->model_sale_return->approve($return_id, $product_id, $rma_no);
			} else {
				$json['error'] = "Failed to create return shipment.";
			}
		}
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
      
		
		
	}
	
	public function disapproveReturn() {
		$this->load->language('sale/return'); // Load the correct language file
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('sale/return'); // Load the correct model
	
		$disapproves = array();
	
		if (isset($this->request->post['selected'])) {
			$disapproves = $this->request->post['selected'];
		} elseif (isset($this->request->get['return_id'])) {
			$disapproves[] = $this->request->get['return_id'];
		}
	
		if ($disapproves ) {
			foreach ($disapproves as $return_id) {
				$this->model_sale_return->disapprove($return_id); // Call the disapprove function for return
			}
	
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
	
			/* 18 02 2020 - Preserve Filters */
			if (isset($this->request->get['filter_customer'])) {
				$url .= '&filter_customer=' . $this->request->get['filter_customer'];
			}
	
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
	
			// if (isset($this->request->get['filter_approved'])) {
			// 	$url .= '&filter_approved=' . $this->request->get['filter_approved'];
			// }
	
			if (isset($this->request->get['filter_date'])) {
				$url .= '&filter_date=' . $this->request->get['filter_date'];
			}
	
			/* Sorting and Pagination */
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
	
			// Redirect back to the return list after disapproval
			$this->response->redirect($this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
	
		$this->getList(); // Load the return list view
	}
	//--------------------------------------------------------------
	
}