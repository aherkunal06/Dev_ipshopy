<?php
class ControllerVendorReview extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('vendor/review');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/review');

		$this->getList();
	}

	public function add() {
		$this->load->language('vendor/review');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/review');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_review->addReview($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
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
		/* 11 02 2020 */
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
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

			$this->response->redirect($this->url->link('vendor/review', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('vendor/review');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/review');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_review->editReview($this->request->get['review_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
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
		/* 11 02 2020 */
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
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

			$this->response->redirect($this->url->link('vendor/review', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('vendor/review');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/review');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $review_id) {
				$this->model_vendor_review->deleteReview($review_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
		
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . $this->request->get['filter_name'];
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
			/* 11 02 2020 */
			
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			
			if (isset($this->request->get['filter_date'])) {
				$url .= '&filter_date=' . $this->request->get['filter_date'];
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

			$this->response->redirect($this->url->link('vendor/review', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}
		
	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}
		/* 11 02 2020 */
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
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}
		
		if (isset($this->request->get['filter_date'])) {
			$filter_date = $this->request->get['filter_date'];
		} else {
			$filter_date = '';
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'review_id';
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
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
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
		/* 11 02 2020 */
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
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
			'href' => $this->url->link('vendor/review', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('vendor/review/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('vendor/review/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['reviews'] = array();

		$filter_data = array(
			/* 11 02 2020 */
			'filter_vendor'		   => $filter_vendor,
			'filter_customer_name' => $filter_customer_name,
			/* 11 02 2020 */
			'filter_customer' => $filter_customer,
			'filter_status'=> $filter_status,
			'filter_date' => $filter_date,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
				
		$review_total = $this->model_vendor_review->getTotalReview($filter_data);
		$results = $this->model_vendor_review->getReviews($filter_data);

		foreach ($results as $result) {
			$sellers = $this->model_vendor_review->getVendor($result['vendor_id']);
			if(isset($sellers['sname'])){
				$sname = $sellers['sname'];
			} else {
				$sname ='';
			}
			$customers = $this->model_vendor_review->getCustomer($result['customer_id']);
			if(isset($customers['firstname'])){
				$cname = $customers['firstname'].' '.$customers['lastname'];
			} else {
				$cname ='';
			}
			
			  /* 18-02-2020 start */
		    $this->load->model('vendor/vendor');
		   
			$textss = $result['text'];			
			if ((utf8_strlen($textss) > 200)) {
				$text = utf8_substr(trim(strip_tags(html_entity_decode($result['text'], ENT_QUOTES, 'UTF-8'))), 0, 199) .'<a class="readmore " data-toggle="modal" data-target="#viewfullreview'.$result['review_id'].'">'.$this->language->get('text_readmore').' <i class="fa fa-info-circle" aria-hidden="true"></i></a>';	
			} else {
				$text = $result['text'];				
			}
			
			$view = '<a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#viewfullreview'.$result['review_id'].'"><i class="fa fa-eye"></i></a>';
			
				$reviewvalue=array();
				$rating_infos = $this->model_vendor_review->getField($result['review_id'],$result['vendor_id']);
				
				foreach($rating_infos as $rating_info){
					$reviewvalue[]=array(
						'field_name'=> $rating_info['field_name'],
						'value' 	=> $rating_info['value']						
					);
				}				
			
			/* 18-02-2020 end */
			
			$data['reviews'][] = array(
				'review_id'   => $result['review_id'],
				'date_added'  => $result['date_added'],
				'sname'       => $sname,
				'cname'       => $cname,
				/* 18 02 2020 */
				'reviewvalue' => $reviewvalue,
				'text'        => $text,
				'fulltext'    => strip_tags(html_entity_decode($result['text'], ENT_QUOTES, 'UTF-8')),
				/* 18 02 2020 */
				'status'      => ($result['status'] ? $this->language->get('text_enable') : $this->language->get('text_disable')),
				'edit'        => $this->url->link('vendor/review/edit', 'user_token=' . $this->session->data['user_token'] . '&review_id=' . $result['review_id'] . $url, true)
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

		$data['column_customer']= $this->language->get('column_customer');
		$data['column_seller'] 	= $this->language->get('column_seller');
		$data['column_status'] 	= $this->language->get('column_status');
		$data['column_date'] 	= $this->language->get('column_date');
		$data['column_action'] 	= $this->language->get('column_action');	
		$data['entry_status'] 	= $this->language->get('entry_status');	

		
		$data['button_add'] 	= $this->language->get('button_add');
		$data['button_edit'] 	= $this->language->get('button_edit');
		$data['button_delete'] 	= $this->language->get('button_delete');
		$data['button_filter'] 	= $this->language->get('button_filter');

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

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_seller']   = $this->url->link('vendor/review', 'user_token=' . $this->session->data['user_token'] . '&sort=seller' . $url, true);
		$data['sort_status']   = $this->url->link('vendor/review', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		$data['sort_date']     = $this->url->link('vendor/review', 'user_token=' . $this->session->data['user_token'] . '&sort=date' . $url, true);

		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		/* 11 02 2020 */
		if (isset($this->request->get['filter_customer_name'])) {
			$url .= '&filter_customer_name=' . $this->request->get['filter_customer_name'];
		}
		
		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}
		/* 11 02 2020 */
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . $this->request->get['filter_customer'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
		}
		
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
		$pagination->url = $this->url->link('vendor/review', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($review_total - $this->config->get('config_limit_admin'))) ? $review_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $review_total, ceil($review_total / $this->config->get('config_limit_admin')));
		
	
		$data['filter_name']      =   $filter_name;
		$data['filter_customer']  = $filter_customer;
		/* 11 02 2020 */
		$data['filter_vendor']    = $filter_vendor;
		$data['filter_customer_name']  = $filter_customer_name;
		/* 11 02 2020 */
		$data['filter_status']    = $filter_status;
		$data['filter_date'] 	  = $filter_date;		
		$data['sort'] 			  = $sort;
		$data['order'] 			  = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


		$this->response->setOutput($this->load->view('vendor/review_list', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_form'] 	= !isset($this->request->get['review_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] 	= $this->language->get('text_enabled');
		$data['text_disabled'] 	= $this->language->get('text_disabled');
		$data['text_default'] 	= $this->language->get('text_default');
		$data['text_percent'] 	= $this->language->get('text_percent');
		$data['text_amount'] 	= $this->language->get('text_amount');
		$data['text_select'] 	= $this->language->get('text_select');
		$data['text_none'] 		= $this->language->get('text_none');
		$data['text_enable'] 	= $this->language->get('text_enable');
		$data['text_disable'] 	= $this->language->get('text_disable');
		
		$data['entry_customer'] = $this->language->get('entry_customer');
		$data['entry_seller'] 	= $this->language->get('entry_seller');
		$data['entry_text'] 	= $this->language->get('entry_text');
		$data['entry_value'] 	= $this->language->get('entry_value');
		$data['entry_price'] 	= $this->language->get('entry_price');
		$data['entry_quantity'] = $this->language->get('entry_quantity');
		$data['entry_status'] 	= $this->language->get('entry_status');
		$data['help_customer'] 	= $this->language->get('help_customer');
		$data['help_seller'] 	= $this->language->get('help_seller');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['text'])) {
			$data['error_text'] = $this->error['text'];
		} else {
			$data['error_text'] = '';
		}
		
		if (isset($this->error['value'])) {
			$data['error_value'] = $this->error['value'];
		} else {
			$data['error_value'] = '';
		}
		
		if (isset($this->error['price'])) {
			$data['error_price'] = $this->error['price'];
		} else {
			$data['error_price'] = '';
		}
		
		if (isset($this->error['quantity'])) {
			$data['error_quantity'] = $this->error['quantity'];
		} else {
			$data['error_quantity'] = '';
		}
		
		if (isset($this->error['vendor'])) {
			$data['error_vendor'] = $this->error['vendor'];
		} else {
			$data['error_vendor'] = '';
		}
		
		if (isset($this->error['customer'])) {
			$data['error_customer'] = $this->error['customer'];
		} else {
			$data['error_customer'] = '';
		}
		
		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
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
		/* 11 02 2020 */
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
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
			'href' => $this->url->link('vendor/review', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['review_id'])) {
			$data['action'] = $this->url->link('vendor/review/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('vendor/review/edit', 'user_token=' . $this->session->data['user_token'] . '&review_id=' . $this->request->get['review_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('vendor/review', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['review_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$review_info=$this->model_vendor_review->getReview($this->request->get['review_id']);
			
		}
		
		//print_r($review_info);die();
		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->request->post['text'])) {
			$data['text'] = $this->request->post['text'];
		} elseif (isset($review_info)) {
			$data['text'] = $review_info['text'];
		} else {
			$data['text'] = '';
		}
		
		if (isset($this->request->post['customer_id'])) {
			$data['customer_id'] = $this->request->post['customer_id'];
		} elseif (!empty($review_info)) {
			$data['customer_id'] = $review_info['customer_id'];
		} else {
			$data['customer_id'] = '';
		}
				
		$this->load->model('customer/customer');
		if (isset($this->request->post['customer'])) {
			$data['customer'] = $this->request->post['customer'];
		} elseif (!empty($review_info)) {
			$customer_info = $this->model_customer_customer->getCustomer($review_info['customer_id']);

			if ($customer_info) {
				$data['customer'] = $customer_info['firstname'].' '.$customer_info['lastname'];
			} else {
				$data['customer'] = '';
			}
		} else {
			$data['customer'] = '';
		}
		
		if (isset($this->request->post['vendor_id'])) {
			$data['vendor_id'] = $this->request->post['vendor_id'];
		} elseif (!empty($review_info)) {
			$data['vendor_id'] = $review_info['vendor_id'];
		} else {
			$data['vendor_id'] = '';
		}

		$this->load->model('vendor/vendor');
		if (isset($this->request->post['vendor'])) {
			$data['vendor'] = $this->request->post['vendor'];
		} elseif (!empty($review_info)) {
			$vendor_info = $this->model_vendor_vendor->getVendor($review_info['vendor_id']);

			if ($vendor_info) {
				$data['vendor'] = $vendor_info['firstname'].' '.$vendor_info['lastname'];
			} else {
				$data['vendor'] = '';
			}
		} else {
			$data['vendor'] = '';
		}
				
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (isset($review_info)) {
			$data['status'] = $review_info['status'];
		} else {
			$data['status'] = '';
		}
		
		if (isset($this->request->post['reviewfield'])) {
			$data['reviewfield'] = $this->request->post['reviewfield'];
		} elseif (isset($review_info)) {
			$data['reviewfield'] = $this->model_vendor_review->getFieldSubmits($this->request->get['review_id']);
		} else {
			$data['reviewfield'] = array();
		}
	

		$data['review_fieldselect'] = array();
		foreach ($data['reviewfield'] as $reviewid) {
			$review_fields = $this->model_vendor_review->getReviewFielddescription($reviewid['rf_id']);
			if($review_fields['field_name']){
				$fieldname=$review_fields['field_name'];
			}else{
				$fieldname='';
			}

			$data['review_fieldselect'][]=array(
		     	 'review_id'    => $reviewid['review_id'],
		      	 'value'     	=> $reviewid['value'],
		     	 'field_name'   => $fieldname,
		     	 'rf_id'        => $reviewid['rf_id'] 
		    );

		}
		$this->load->model('vendor/review_field');
		$data['review_fields'] = $this->model_vendor_review_field->getReviewFields($data);
				
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


		$this->response->setOutput($this->load->view('vendor/review_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'vendor/review')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		/* 06-04-2019 */
		if (!$this->request->post['customer_id']) {
			$this->error['customer'] = $this->language->get('error_customer');
		}
		
		if (!$this->request->post['vendor_id']) {
			$this->error['vendor'] = $this->language->get('error_vendor');
		}
		/* 06-04-2019 */
		if (utf8_strlen($this->request->post['text']) < 1) {
			$this->error['text'] = $this->language->get('error_text');
		}
		
		return !$this->error;
	}
	
	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'vendor/review')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		return !$this->error;
	}
	/* update function 11 02 2020 */
	public function autocomplete(){
		
		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}
	
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'firstname';
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
		$this->load->model('vendor/vendor');
			
		$filter_data = array(
		'sort'  => $sort,
		'order' => $order,
		'filter_customer' => $filter_customer,
		'start'            => 0,
		'limit'            => 5
		);
		$accounts = $this->model_vendor_vendor->getCustomers($filter_data);
		foreach ($accounts as $account) {

		$json[] = array(
		'customer_id'  => $account['customer_id'],
		'name'              => strip_tags(html_entity_decode($account['name'], ENT_QUOTES, 'UTF-8')),
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