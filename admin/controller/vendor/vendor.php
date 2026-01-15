<?php
class ControllerVendorVendor extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('vendor/vendor');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/vendor');
		$this->load->model('vendor/store');
 
		$this->getList();
	}

	public function add() {
		$this->load->language('vendor/vendor');
		$this->load->model('vendor/store');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/vendor');
		
		// added code changes for the show username on 21-05-2025
        // ✅ Add registered_by from the current admin user
    	$this->load->model('user/user');
        // 	----------------------------------------------------------
        
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
		    // added code changes for the show username on 21-05-2025
		    $user_info = $this->model_user_user->getUser($this->user->getId());
        	$this->request->post['registered_by'] = $user_info['username'];
            // 	----------------------------------------------------------
			$this->model_vendor_vendor->addVendor($this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			/* 18 02 2020 */
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . $this->request->get['filter_name'];
			}
			
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			
			if (isset($this->request->get['filter_approved'])) {
				$url .= '&filter_approved=' . $this->request->get['filter_approved'];
			}
			
			if (isset($this->request->get['filter_date'])) {
				$url .= '&filter_date=' . $this->request->get['filter_date'];
			}
			/* 18 02 2020 */
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('vendor/vendor');
		$this->load->model('vendor/store');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/vendor');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			
			$this->model_vendor_vendor->editVendor($this->request->get['vendor_id'], $this->request->post);
		
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			/* 18 02 2020 */
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . $this->request->get['filter_name'];
			}
			
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			
			
			if (isset($this->request->get['filter_approved'])) {
				$url .= '&filter_approved=' . $this->request->get['filter_approved'];
			}
			
			
			if (isset($this->request->get['filter_date'])) {
				$url .= '&filter_date=' . $this->request->get['filter_date'];
			}
			
			/* 18 02 2020 */
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->response->redirect($this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true));
			
			}
		$this->getForm();
	}

	public function delete() {
		$this->load->language('vendor/vendor');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/vendor');
		$this->load->model('vendor/store');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $vendor_id) {
				$this->model_vendor_vendor->deleteVendor($vendor_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			/* 18 02 2020 */
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . $this->request->get['filter_name'];
			}
			
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			
			
			if (isset($this->request->get['filter_approved'])) {
				$url .= '&filter_approved=' . $this->request->get['filter_approved'];
			}
			
			if (isset($this->request->get['filter_date'])) {
				$url .= '&filter_date=' . $this->request->get['filter_date'];
			}
			/* 18 02 2020 */
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}
	
	public function approve(){
		$this->load->language('vendor/vendor');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('vendor/vendor');
		$approves = array();
		if (isset($this->request->post['selected'])){
			$approve = $this->request->post['selected'];
		} 
		elseif (isset($this->request->get['vendor_id'])){
			$approves[] = $this->request->get['vendor_id'];
		}
		if ($approves && $this->validateApprove()){
			foreach($approves as $vendor_id){
				$this->model_vendor_vendor->approve($vendor_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			/* 18 02 2020 */
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . $this->request->get['filter_name'];
			}
			
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			
			
			if (isset($this->request->get['filter_approved'])) {
				$url .= '&filter_approved=' . $this->request->get['filter_approved'];
			}
		
			
			if (isset($this->request->get['filter_date'])) {
				$url .= '&filter_date=' . $this->request->get['filter_date'];
			}
				/* 18 02 2020 */
			if (isset($this->request->get['sort'])){
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])){
				$url .= '&page=' . $this->request->get['page'];
			}
			$this->response->redirect($this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList(); 
	}

	public function disapprove(){
		$this->load->language('vendor/vendor');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('vendor/vendor');
		$approves = array();
		if (isset($this->request->post['selected'])){
			$approve = $this->request->post['selected'];
		} 
		elseif (isset($this->request->get['vendor_id'])){
			$approves[] = $this->request->get['vendor_id'];
		}
		if ($approves && $this->validateDesapprove()){
			foreach($approves as $vendor_id){
				$this->model_vendor_vendor->Disapprove($vendor_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			
			/* 18 02 2020 */
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . $this->request->get['filter_name'];
			}
			
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}
			
			
			if (isset($this->request->get['filter_approved'])) {
				$url .= '&filter_approved=' . $this->request->get['filter_approved'];
			}
			
			
			if (isset($this->request->get['filter_date'])) {
				$url .= '&filter_date=' . $this->request->get['filter_date'];
			}
			/* 18 02 2020 */
			if (isset($this->request->get['sort'])){
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])){
				$url .= '&page=' . $this->request->get['page'];
			}
			$this->response->redirect($this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList(); 

	 }	

	protected function getList() {
		
		if (isset($this->request->post['config_autoseller'])) {
			$data['config_autoseller'] = $this->request->post['config_autoseller'];
		} else {
			$data['config_autoseller'] = $this->config->get('config_autoseller');
		}
		
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}
		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}
		/* 18 02 2020 */
		if (isset($this->request->get['filter_approved'])) {
			$filter_approved = $this->request->get['filter_approved'];
		} else {
			$filter_approved = '';
		}
		/* 18 02 2020 */
		if (isset($this->request->get['filter_date'])) {
			$filter_date = $this->request->get['filter_date'];
		} else {
			$filter_date = '';
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
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		/* 18 02 2020 */
		if (isset($this->request->get['filter_approved'])) {
			$url .= '&filter_approved=' . $this->request->get['filter_approved'];
		}
		/* 18 02 2020 */
		
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
			'href' => $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('vendor/vendor/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('vendor/vendor/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		
		$data['setting'] = $this->url->link('vendor/vendor/setting', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['vendors'] = array();

		$filter_data = array(
			'filter_name' => $filter_name,
			'filter_status' => $filter_status,
			'filter_approved' => $filter_approved,
			'filter_date' => $filter_date,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin'),
			'user_id'        => $this->user->getId() // Add user_id for filtering changes on the 09-06-2025
		);

		$vendor_total = $this->model_vendor_vendor->getTotalVendor($filter_data);

		$results = $this->model_vendor_vendor->getVendors($filter_data);
		$this->load->model('tool/image');
		
		foreach ($results as $result) {
			
			/* 17 02 2020 */
			$totalproduct = $this->model_vendor_vendor->getVendorProducts($result['vendor_id']);
		
			/* 17 02 2020 */
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}
			
			if (!$result['approved']) {
				$approve = $this->url->link('vendor/vendor/approve', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $result['vendor_id'] . $url, true);
			} else {
				$approve = '';
			}
			
			if ($result['approved']) {
				$disapproved = $this->url->link('vendor/vendor/disapprove', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $result['vendor_id'] . $url, true);
			} else {
				$disapproved = '';
			}
			/* 19 02 2020  */
			if(!empty($result['firstname'])){
				$firstname = $result['firstname'];
			} else {
				$firstname ='';
			}
			if(!empty($result['lastname'])){
				$lastname = $result['lastname'];
			} else {
				$lastname ='';
			}
			
			

			$sellername = $firstname.' '.$lastname;
		
			$vendorstorename = $this->model_vendor_vendor->getVendorsStorename($result['vendor_id']);
			
			if(!empty($vendorstorename['name'])){
				$storename = $vendorstorename['name'];
			} else {
				$storename ='';
			}
			
			if($result['status']==1 && $result['approved']==1){
				$status = $this->language->get('text_enable');
			} elseif($result['approved']==0) {
				$status = $this->language->get('text_waitingapproved');
			} else  {
				$status = $this->language->get('text_disable');
			}
			
		
			$data['vendors'][] = array(
				'vendor_id'       => $result['vendor_id'],
				'display_name'    => $result['display_name'],
				
				'sellername' 	  => $sellername,					
				'vendorstorename' => $storename,				
				'totalproduct' 	  => $totalproduct['total'],
				'date_added' 	  => $result['date_added'],
				//added code changes for show username on 21-05-2025
				'registered_by'   => $result['registered_by'],
				'email'           => $result['email'],
				'status'          => $status,
				'approve'		  => $approve,
				'disapproved'	  => $disapproved,
				'image'		      => $image,
				'edit'            => $this->url->link('vendor/vendor/edit', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $result['vendor_id'] . $url, true)
			);
			
			//added changes for show the username on 21-05-2025
    		// Get the email of the user who registered this vendor
			$registered_by_username = '';
			if (!empty($result['registered_by'])) {
				// You may need to adjust this if 'registered_by' is already an email
				// If it's a user_id, fetch the email from the user table
			$this->load->model('user/user');
			$user_info = $this->model_user_user->getUser($result['registered_by']);
			if ($user_info) {
				$registered_by_username = $user_info['username'];
			}
		}
		    
		}

		$data['heading_title'] 		= $this->language->get('heading_title');
		$data['user_token'] 		= $this->session->data['user_token'];
		$data['text_list'] 			= $this->language->get('text_list');
		$data['text_no_results'] 	= $this->language->get('text_no_results');
		$data['text_confirm'] 		= $this->language->get('text_confirm');
		$data['text_enable'] 		= $this->language->get('text_enable');
		$data['text_disable'] 		= $this->language->get('text_disable');
		$data['text_select'] 		= $this->language->get('text_select');
		$data['text_none'] 			= $this->language->get('text_none');		
		$data['column_display_name']= $this->language->get('column_display_name');
		$data['column_name'] 		= $this->language->get('column_name');
		$data['column_firstname'] 	= $this->language->get('column_firstname');
		$data['column_lastname'] 	= $this->language->get('column_lastname');
		$data['column_email'] 		= $this->language->get('column_email');
		$data['column_status'] 		= $this->language->get('column_status');
		$data['column_image'] 		= $this->language->get('column_image');
		$data['column_approve'] 	= $this->language->get('column_approve');		
		$data['column_sort_order']  = $this->language->get('column_sort_order');
		$data['column_action'] 		= $this->language->get('column_action');		
		$data['entry_firstname'] 	= $this->language->get('entry_firstname');	
		$data['entry_status'] 		= $this->language->get('entry_status');	
		$data['entry_date'] 		= $this->language->get('entry_date');			
		$data['button_add'] 		= $this->language->get('button_add');
		$data['button_edit'] 		= $this->language->get('button_edit');
		$data['button_delete'] 		= $this->language->get('button_delete');
		$data['button_filter'] 		= $this->language->get('button_filter');
		$data['button_approve'] 	= $this->language->get('button_approve');
		$data['button_desapprove']  = $this->language->get('button_desapprove');
		$data['button_setting']		= $this->language->get('button_setting');
		
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
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
	
		/* 18 02 2020 */
		if (isset($this->request->get['filter_approved'])) {
			$url .= '&filter_approved=' . $this->request->get['filter_approved'];
		}
		/* 18 02 2020 */
		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
		}
		/* 17 02 2020 remove code */
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
			
		$data['sort_image'] 	= $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=image' . $url, true);
		$data['sort_display_name'] = $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=display_name' . $url, true);
		$data['sort_sellername'] = $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=vendorname' . $url, true);
		$data['sort_lastname'] 	= $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=lastname' . $url, true);
		$data['sort_email'] 	= $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=email' . $url, true);
		$data['sort_status'] 	= $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		$data['sort_sort_order']= $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);
		/* 17 02 2020 */
		$data['sort_date_added'] = $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);
		$data['sort_totalproduct'] = $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=totalproduct' . $url, true);
		/* 17 02 2020 */
		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		/* 18 02 2020 */
		if (isset($this->request->get['filter_approved'])) {
			$url .= '&filter_approved=' . $this->request->get['filter_approved'];
		}
		/* 18 02 2020 */
		
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
		$pagination->total = $vendor_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($vendor_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($vendor_total - $this->config->get('config_limit_admin'))) ? $vendor_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $vendor_total, ceil($vendor_total / $this->config->get('config_limit_admin')));
		
		$data['filter_name']  = $filter_name;
		$data['filter_status']= $filter_status;
		/* 18 20 2020 */
		$data['filter_approved']= $filter_approved;
			/* 18 20 2020 */
		$data['filter_date']  = $filter_date;		
		$data['sort'] 		  = $sort;
		$data['order'] 		  = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


		$this->response->setOutput($this->load->view('vendor/vendor_list', $data));
	}

	protected function getForm() {
		$data['heading_title'] 			= $this->language->get('heading_title');

		$data['text_form'] 				= !isset($this->request->get['vendor_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] 			= $this->language->get('text_enabled');
		$data['text_disabled'] 			= $this->language->get('text_disabled');
		$data['text_default'] 			= $this->language->get('text_default');
		$data['text_percent'] 			= $this->language->get('text_percent');
		$data['text_amount'] 			= $this->language->get('text_amount');
		$data['text_select'] 			= $this->language->get('text_select');
		$data['text_none'] 				= $this->language->get('text_none');
		$data['text_enable'] 			= $this->language->get('text_enable');
		$data['text_disable'] 			= $this->language->get('text_disable');		
		$data['column_name'] 			= $this->language->get('column_name');
		$data['column_lastname'] 		= $this->language->get('column_lastname');		
		$data['entry_display_name'] 	= $this->language->get('entry_display_name');
		$data['entry_firstname'] 		= $this->language->get('entry_firstname');
		$data['entry_lastname'] 		= $this->language->get('entry_lastname');
		$data['entry_email'] 			= $this->language->get('entry_email');
		$data['entry_telephone'] 		= $this->language->get('entry_telephone');
		$data['entry_gstin'] 			= $this->language->get('entry_gstin');
		$data['entry_company'] 			= $this->language->get('entry_company');
		$data['entry_address_1'] 		= $this->language->get('entry_address_1');
		$data['entry_address_2'] 		= $this->language->get('entry_address_2');
		$data['entry_status'] 			= $this->language->get('entry_status');
		$data['entry_city'] 			= $this->language->get('entry_city');
		$data['entry_postcode'] 		= $this->language->get('entry_postcode');
		$data['entry_country'] 			= $this->language->get('entry_country');
		$data['entry_zone'] 			= $this->language->get('entry_zone');
		$data['entry_password'] 		= $this->language->get('entry_password');
		$data['entry_confirmpassword'] 	= $this->language->get('entry_confirmpassword');
		$data['entry_image'] 			= $this->language->get('entry_image');
		$data['entry_about'] 			= $this->language->get('entry_about');		
		// Store Data ///
		
		$data['entry_name'] 			= $this->language->get('entry_name');
		$data['entry_description'] 		= $this->language->get('entry_description');
		$data['entry_shipping_policy'] 	= $this->language->get('entry_shipping_policy');
		$data['entry_return_policy'] 	= $this->language->get('entry_return_policy');
		$data['entry_meta_keyword'] 	= $this->language->get('entry_meta_keyword');
		$data['entry_meta_description'] = $this->language->get('entry_meta_description');
		$data['entry_vendorname'] 		= $this->language->get('entry_vendorname');
		$data['entry_email'] 			= $this->language->get('entry_email');
		$data['entry_phone'] 			= $this->language->get('entry_phone');
		$data['entry_address'] 			= $this->language->get('entry_address');
		$data['entry_city'] 			= $this->language->get('entry_city');
		$data['entry_country'] 			= $this->language->get('entry_country');
		$data['entry_zone'] 			= $this->language->get('entry_zone');
		$data['entry_postcode'] 		= $this->language->get('entry_postcode');
		$data['entry_bank_detail'] 		= $this->language->get('entry_bank_detail');
		$data['entry_tax_number'] 		= $this->language->get('entry_tax_number');
		$data['entry_shipping_charge'] 	= $this->language->get('entry_shipping_charge');
		$data['entry_url'] 				= $this->language->get('entry_url');
		$data['entry_banner'] 			= $this->language->get('entry_banner');
		$data['entry_logo'] 			= $this->language->get('entry_logo');
		$data['entry_about'] 			= $this->language->get('entry_about');
		$data['entry_commission']       = $this->language->get('entry_commission');
		$data['entry_productstatus']    = $this->language->get('entry_productstatus');
		$data['entry_bankname']  		= $this->language->get('entry_bankname');
		$data['entry_bnumber']  		= $this->language->get('entry_bnumber');
		$data['entry_swiftcode']  		= $this->language->get('entry_swiftcode');
		$data['entry_aname']  			= $this->language->get('entry_aname');
		$data['entry_anumber']  		= $this->language->get('entry_anumber');
		$data['entry_Emailid']  		= $this->language->get('entry_Emailid');
		$data['entry_method']  			= $this->language->get('entry_method');
		$data['text_bank']  			= $this->language->get('text_bank');
		$data['text_paypal']  			= $this->language->get('text_paypal');
		$data['text_autoapprove']    	= $this->language->get('text_autoapprove');
		$data['text_pending']    		= $this->language->get('text_pending');
		$data['entry_facebook_url'] 	= $this->language->get('entry_facebook_url');
		$data['entry_google_url'] 		= $this->language->get('entry_google_url');
		$data['entry_mapurl'] 			= $this->language->get('entry_mapurl');

		$data['tab_chat'] 				= $this->language->get('tab_chat');
		$data['entry_status'] 				= $this->language->get('entry_status');
		$data['text_yes'] 			= $this->language->get('text_yes');
		$data['text_no'] 			= $this->language->get('text_no');
		// Store Data ///
		
		$data['tab_seller'] 		    = $this->language->get('tab_seller');
		$data['tab_generalstore'] 		= $this->language->get('tab_generalstore');
		$data['tab_datastore'] 			= $this->language->get('tab_datastore');
		$data['tab_commission'] 		= $this->language->get('tab_commission');
		

		$data['help_keyword'] = $this->language->get('help_keyword');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['display_name'])) {
			$data['error_display_name'] = $this->error['display_name'];
		} else {
			$data['error_display_name'] = '';
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
		
		if (isset($this->error['gstin'])) {
			$data['error_gstin'] = $this->error['gstin'];
		} else {
			$data['error_gstin'] = '';
		}
		
		if (isset($this->error['company'])) {
			$data['error_company'] = $this->error['company'];
		} else {
			$data['error_company'] = '';
		}
		
		if (isset($this->error['address_1'])) {
			$data['error_address_1'] = $this->error['address_1'];
		} else {
			$data['error_address_1'] = '';
		}
		
		if (isset($this->error['address_2'])) {
			$data['error_address_2'] = $this->error['address_2'];
		} else {
			$data['error_address_2'] = '';
		}
		
		if (isset($this->error['city'])) {
			$data['error_city'] = $this->error['city'];
		} else {
			$data['error_city'] = '';
		}		
		
		if (isset($this->error['country'])) {
			$data['error_country'] = $this->error['country'];
		} else {
			$data['error_country'] = '';
		}
		
		if (isset($this->error['zone'])) {
			$data['error_zone'] = $this->error['zone'];
		} else {
			$data['error_zone'] = '';
		}

	
		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['paypal'])) {
			$data['error_paypal'] = $this->error['paypal'];
		} else {
			$data['error_paypal'] = '';
		}

		if (isset($this->error['bank_account_name'])) {
			$data['error_bank_account_name'] = $this->error['bank_account_name'];
		} else {
			$data['error_bank_account_name'] = '';
		}

		if (isset($this->error['bank_account_number'])) {
			$data['error_bank_account_number'] = $this->error['bank_account_number'];
		} else {
			$data['error_bank_account_number'] = '';
		}
		
		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} elseif (!empty($vendor_info)) {
			$data['password'] = $vendor_info['password'];
		} else {
			$data['password'] = '';
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}
		
		if (isset($this->request->post['country_id'])) {
			$data['country_id'] = (int)$this->request->post['country_id'];
		} elseif (isset($this->session->data['shipping_address']['country_id'])) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}
		
		if (isset($this->request->post['zone_id'])) {
			$data['zone_id'] = (int)$this->request->post['zone_id'];
		} elseif (isset($this->session->data['shipping_address']['zone_id'])) {
			$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$data['zone_id'] = '';
		}
				
		$this->load->model('localisation/country');
		$data['countries'] = $this->model_localisation_country->getCountries();	
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();	
		
		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		/* 18 02 2020 */
			if (isset($this->request->get['filter_approved'])) {
				$url .= '&filter_approved=' . $this->request->get['filter_approved'];
			}
		/* 18 02 2020 */	
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
			'href' => $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['vendor_id'])) {
			$data['action'] = $this->url->link('vendor/vendor/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('vendor/vendor/edit', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $this->request->get['vendor_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['vendor_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$vendor_info=$this->model_vendor_vendor->getVendor($this->request->get['vendor_id']);
			
		}
	
		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['display_name'])) {
			$data['display_name'] = $this->request->post['display_name'];
		} elseif (isset($vendor_info['display_name'])){
			$data['display_name'] = $vendor_info['display_name'];
		} else {
			$data['display_name'] = '';
		}

		if (isset($this->request->post['firstname'])) {
			$data['firstname'] = $this->request->post['firstname'];
		} elseif (isset($vendor_info['firstname'])){
			$data['firstname'] = $vendor_info['firstname'];
		} else {
			$data['firstname'] = '';
		}
		

		if (isset($this->request->post['lastname'])) {
			$data['lastname'] = $this->request->post['lastname'];
		} elseif (isset($vendor_info['lastname'])){
			$data['lastname'] = $vendor_info['lastname'];
		} else {
			$data['lastname'] = '';
		}
		
		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (isset($vendor_info['email'])){
			$data['email'] = $vendor_info['email'];
		} else {
			$data['email'] = '';
		}
		
		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} elseif (isset($vendor_info['telephone'])){
			$data['telephone'] = $vendor_info['telephone'];
		} else {
			$data['telephone'] = '';
		}

		if (isset($this->request->post['gstin'])) {
			$data['gstin'] = $this->request->post['gstin'];
		} elseif (isset($vendor_info['gstin'])){
			$data['gstin'] = $vendor_info['gstin'];
		} else {
			$data['gstin'] = '';
		}
		
		if (isset($this->request->post['about'])) {
			$data['about'] = $this->request->post['about'];
		} elseif (isset($vendor_info['about'])){
			$data['about'] = $vendor_info['about'];
		} else {
			$data['about'] = '';
		}
		
		if (isset($this->request->post['company'])) {
			$data['company'] = $this->request->post['company'];
		} elseif (isset($vendor_info['company'])){
			$data['company'] = $vendor_info['company'];
		} else {
			$data['company'] = '';
		}
		
		if (isset($this->request->post['address_1'])) {
			$data['address_1'] = $this->request->post['address_1'];
		} elseif (isset($vendor_info['address_1'])){
			$data['address_1'] = $vendor_info['address_1'];
		} else {
			$data['address_1'] = '';
		}
		
		if (isset($this->request->post['address_2'])) {
			$data['address_2'] = $this->request->post['address_2'];
		} elseif (isset($vendor_info['address_2'])){
			$data['address_2'] = $vendor_info['address_2'];
		} else {
			$data['address_2'] = '';
		}
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (isset($vendor_info['status'])){
			$data['status'] = $vendor_info['status'];
		} else {
			$data['status'] = '';
		}
		
		if (isset($this->request->post['product_status'])) {
			$data['product_status'] = $this->request->post['product_status'];
		} elseif (isset($vendor_info['product_status'])){
			$data['product_status'] = $vendor_info['product_status'];
		} else {
			$data['product_status'] = '';
		}
		
		if (isset($this->request->post['city'])) {
			$data['city'] = $this->request->post['city'];
		} elseif (isset($vendor_info['city'])){
			$data['city'] = $vendor_info['city'];
		} else {
			$data['city'] = '';
		}
		
		if (isset($this->request->post['postcode'])) {
			$data['postcode'] = $this->request->post['postcode'];
		} elseif (isset($vendor_info['postcode'])){
			$data['postcode'] = $vendor_info['postcode'];
		} else {
			$data['postcode'] = '';
		}
		
		if (isset($this->request->post['country_id'])) {
			$data['country_id'] = $this->request->post['country_id'];
		} elseif (isset($vendor_info['country_id'])){
			$data['country_id'] = $vendor_info['country_id'];
		} else {
			$data['country_id'] = '';
		}
		
		if (isset($this->request->post['zone_id'])) {
			$data['zone_id'] = $this->request->post['zone_id'];
		} elseif (isset($vendor_info['zone_id'])){
			$data['zone_id'] = $vendor_info['zone_id'];
		} else {
			$data['zone_id'] = '';
		}

		if (isset($this->request->post['vendor_id'])) {
			$data['vendor_id'] = $this->request->post['vendor_id'];
		} elseif (isset($vendor_info['vendor_id'])){
			$data['vendor_id'] = $vendor_info['vendor_id'];
		} else {
			$data['vendor_id'] = '';
		}
		
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (isset($vendor_info['image'])){
			$data['image'] = $vendor_info['image'];
		} else {
			$data['image'] = '';
		}
		
		$this->load->model('tool/image');
			
		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($vendor_info) && is_file(DIR_IMAGE . $vendor_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($vendor_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			
//// Seller Store Start ///
				
		if (isset($this->request->post['store_description'])) {
			$data['store_description'] = $this->request->post['store_description'];
		} elseif (isset($vendor_info)) {
			$data['store_description'] = $this->model_vendor_vendor->getVendorStoreDescriptions($this->request->get['vendor_id']);
		} else {
			$data['store_description'] = array();
		}
		
		if (isset($this->request->post['store_about'])) {
			$data['store_about'] = $this->request->post['store_about'];
		} elseif (isset($vendor_info['store_about'])){
			$data['store_about'] = $vendor_info['store_about'];
		} else {
			$data['store_about'] = '';
		}
		
				
		if (isset($this->request->post['banner'])) {
			$data['banner'] = $this->request->post['banner'];
		} elseif (isset($vendor_info['banner'])){
			$data['banner'] = $vendor_info['banner'];
		} else {
			$data['banner'] = '';
		}
		
		
		if (isset($this->request->post['logo'])) {
			$data['logo'] = $this->request->post['logo'];
		} elseif (isset($vendor_info['logo'])){
			$data['logo'] = $vendor_info['logo'];
		} else {
			$data['logo'] = '';
		}
		
		if (isset($this->request->post['tax_number'])) {
			$data['tax_number'] = $this->request->post['tax_number'];
		} elseif (isset($vendor_info['tax_number'])){
			$data['tax_number'] = $vendor_info['tax_number'];
		} else {
			$data['tax_number'] = '';
		}

		if (isset($this->request->post['facebook_url'])) {
			$data['facebook_url'] = $this->request->post['facebook_url'];
		} elseif (isset($vendor_info['facebook_url'])){
			$data['facebook_url'] = $vendor_info['facebook_url'];
		} else {
			$data['facebook_url'] = '';
		}

		if (isset($this->request->post['google_url'])) {
			$data['google_url'] = $this->request->post['google_url'];
		} elseif (isset($vendor_info['google_url'])){
			$data['google_url'] = $vendor_info['google_url'];
		} else {
			$data['google_url'] = '';
		}
		
		if (isset($this->request->post['map_url'])) {
			$data['map_url'] = $this->request->post['map_url'];
		} elseif (isset($vendor_info['map_url'])){
			$data['map_url'] = $vendor_info['map_url'];
		} else {
			$data['map_url'] = '';
		}

		if (isset($this->request->post['shipping_charge'])) {
			$data['shipping_charge'] = $this->request->post['shipping_charge'];
		} elseif (isset($vendor_info['shipping_charge'])){
			$data['shipping_charge'] = $vendor_info['shipping_charge'];
		} else {
			$data['shipping_charge'] = '';
		}
// 09 06 2018 //		
		if (isset($this->request->post['vendor_seo_url'])) {
			$data['vendor_seo_url'] = $this->request->post['vendor_seo_url'];
		} elseif (isset($this->request->get['vendor_id'])) {
			$data['vendor_seo_url'] = $this->model_vendor_vendor->getVendorSeoUrls($this->request->get['vendor_id']);
		} else {
			$data['vendor_seo_url'] = array();
		}	
		
		
		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		$this->load->model('setting/store');

		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}
		
		
	// 09 06 2018 //
		
		if (isset($this->request->post['vendor_id'])) {
			$data['vendor_id'] = $this->request->post['vendor_id'];
		} elseif (isset($vendor_info['vendor_id'])){
			$data['vendor_id'] = $vendor_info['vendor_id'];
		} else {
			$data['vendor_id'] = '';
		}
		
		if (isset($this->request->post['commission'])) {
			$data['commission'] = $this->request->post['commission'];
		} elseif (isset($vendor_info['commission'])){
			$data['commission'] = $vendor_info['commission'];
		} else {
			$data['commission'] = '';
		}

		if (isset($this->request->post['payment_method'])) {
			$data['payment_method'] = $this->request->post['payment_method'];
		} elseif (!empty($vendor_info)) {
			$data['payment_method'] = $vendor_info['payment_method'];
		} else {
			$data['payment_method'] = 'paypal';
		}

		if (isset($this->request->post['paypal'])) {
			$data['paypal'] = $this->request->post['paypal'];
		} elseif (!empty($vendor_info)) {
			$data['paypal'] = $vendor_info['paypal'];
		} else {
			$data['paypal'] = '';
		}

		if (isset($this->request->post['bank_name'])) {
			$data['bank_name'] = $this->request->post['bank_name'];
		} elseif (!empty($vendor_info)) {
			$data['bank_name'] = $vendor_info['bank_name'];
		} else {
			$data['bank_name'] = '';
		}

		if (isset($this->request->post['bank_branch_number'])) {
			$data['bank_branch_number'] = $this->request->post['bank_branch_number'];
		} elseif (!empty($vendor_info)) {
			$data['bank_branch_number'] = $vendor_info['bank_branch_number'];
		} else {
			$data['bank_branch_number'] = '';
		}

		if (isset($this->request->post['bank_swift_code'])) {
			$data['bank_swift_code'] = $this->request->post['bank_swift_code'];
		} elseif (!empty($vendor_info)) {
			$data['bank_swift_code'] = $vendor_info['bank_swift_code'];
		} else {
			$data['bank_swift_code'] = '';
		}

		if (isset($this->request->post['bank_account_name'])) {
			$data['bank_account_name'] = $this->request->post['bank_account_name'];
		} elseif (!empty($vendor_info)) {
			$data['bank_account_name'] = $vendor_info['bank_account_name'];
		} else {
			$data['bank_account_name'] = '';
		}

		if (isset($this->request->post['bank_account_number'])) {
			$data['bank_account_number'] = $this->request->post['bank_account_number'];
		} elseif (!empty($vendor_info)) {
			$data['bank_account_number'] = $vendor_info['bank_account_number'];
		} else {
			$data['bank_account_number'] = '';
		}
		
		$this->load->model('tool/image');
		
		if (isset($this->request->post['banner']) && is_file(DIR_IMAGE . $this->request->post['banner'])) {
			$data['thumb_banner'] = $this->model_tool_image->resize($this->request->post['banner'], 100, 100);
		} elseif (!empty($vendor_info) && is_file(DIR_IMAGE . $vendor_info['banner'])) {
			$data['thumb_banner'] = $this->model_tool_image->resize($vendor_info['banner'], 100, 100);
		} else {
			$data['thumb_banner'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if (isset($this->request->post['logo']) && is_file(DIR_IMAGE . $this->request->post['logo'])) {
			$data['thumb_logo'] = $this->model_tool_image->resize($this->request->post['logo'], 100, 100);
		} elseif (!empty($vendor_info) && is_file(DIR_IMAGE . $vendor_info['logo'])) {
			$data['thumb_logo'] = $this->model_tool_image->resize($vendor_info['logo'], 100, 100);
		} else {
			$data['thumb_logo'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		$data['banner_placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['logo_placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
//// Seller Store End ///
			
		
		if(isset($vendor_info['vendor_id'])){
			$status_info = $this->model_vendor_vendor->getmsg($vendor_info['vendor_id']);
		}
		
		if (isset($this->request->post['chatstatus'])) {
			$data['chatstatus'] = $this->request->post['chatstatus'];
		} elseif (isset($status_info['chatstatus'])){
			$data['chatstatus'] = $status_info['chatstatus'];
		} else {
			$data['chatstatus'] = '';
		}

		if (isset($this->request->post['message'])) {
			$data['message'] = $this->request->post['message'];
		} elseif (isset($status_info['message'])){
			$data['message'] = $status_info['message'];
		} else {
			$data['message'] = '';
		}

	
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('vendor/vendor_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'vendor/vendor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['display_name']) < 2) || (utf8_strlen($this->request->post['display_name']) > 64)) {
			$this->error['display_name'] = $this->language->get('error_display_name');
		}

		if ((utf8_strlen($this->request->post['firstname']) < 2) || (utf8_strlen($this->request->post['firstname']) > 64)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen($this->request->post['lastname']) < 2) || (utf8_strlen($this->request->post['lastname']) > 64)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}
						
		$email_info = $this->model_vendor_vendor->getVendorByEmail($this->request->post['email']);

		if (!isset($this->request->get['vendor_id'])) {
			if ($email_info) {
				$this->error['warning'] = $this->language->get('error_email_match');
			}
		} else {
			if ($email_info && ($this->request->get['vendor_id'] != $email_info['vendor_id'])) {
				$this->error['warning'] = $this->language->get('error_email_match');
			}
		}
		
		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}
		
		if ((utf8_strlen($this->request->post['telephone']) < 2) || (utf8_strlen($this->request->post['telephone']) > 64)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}
		
		if ((utf8_strlen($this->request->post['address_1']) < 2) || (utf8_strlen($this->request->post['address_1']) > 255)) {
			$this->error['address_1'] = $this->language->get('error_address_1');
		}
		
		if ((utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 255)) {
			$this->error['city'] = $this->language->get('error_city');
		}
		
		/*if ((utf8_strlen($this->request->post['postcode']) < 2) || (utf8_strlen($this->request->post['postcode']) > 64)) {
			$this->error['postcode'] = $this->language->get('error_postcode');
		}
		
		if ((utf8_strlen($this->request->post['password']) < 2) || (utf8_strlen($this->request->post['password']) > 64)) {
			$this->error['password'] = $this->language->get('error_password');
		}*/
		
		foreach ($this->request->post['store_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if ($this->request->post['payment_method'] == 'paypal') {
			if ((utf8_strlen($this->request->post['paypal']) > 96) || !filter_var($this->request->post['paypal'], FILTER_VALIDATE_EMAIL)) {
				$this->error['paypal'] = $this->language->get('error_paypal');
			}
		} elseif ($this->request->post['payment_method'] == 'banktransfer') {
			if ($this->request->post['bank_account_name'] == '') {
				$this->error['bank_account_name'] = $this->language->get('error_bank_account_name');
			}

			if ($this->request->post['bank_account_number'] == '') {
				$this->error['bank_account_number'] = $this->language->get('error_bank_account_number');
			}
		}
		if ($this->request->post['vendor_seo_url']) {
			
			$this->load->model('vendor/seo_url');
			
			foreach ($this->request->post['vendor_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}							
						
						$seo_urls = $this->model_vendor_seo_url->getSeoUrlsByKeyword($keyword);
						
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['vendor_id']) || (($seo_url['query'] != 'vendor_id=' . $this->request->get['vendor_id'])))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
							}
						}
					}
				}
			}
		}
		return !$this->error;
	}
	
	protected function validateApprove(){
		if (!$this->user->hasPermission('modify', 'vendor/vendor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	    return !$this->error;
	}
	
	protected function validateDesapprove(){
		if (!$this->user->hasPermission('modify', 'vendor/vendor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	    return !$this->error;

	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'vendor/vendor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	return !$this->error;
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function autocomplete(){	
		/* 20 08 2020 */
		$json = array();
		/* 20 08 2020 */
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

			
		if (isset($this->request->get['filter_seller'])) {
				$filter_seller = $this->request->get['filter_seller'];
			} else {
				$filter_seller = '';
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
		$this->load->model('vendor/vendor');
			
		$filter_data = array(
		'sort'  => $sort,
		'order' => $order,
		'filter_seller' => $filter_seller,
		'filter_name' => $filter_name,
		'user_id'        => $this->user->getId(), // Add user_id for filtering changes on the 09-06-2025
		'start' => 0,
		'limit' => 5
		);
		$accounts = $this->model_vendor_vendor->getVendors($filter_data);
		foreach ($accounts as $account) {
		
		$json[] = array(
		'vendor_id'  => $account['vendor_id'],
		'vendorname'   => strip_tags(html_entity_decode($account['vendorname'], ENT_QUOTES, 'UTF-8'))
		);
		}
		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['vendorname'];
		}
		/* 20 08 2020 */
	//	array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function setting() {
		$this->load->language('vendor/vendor');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate() ) {
				
			$this->model_setting_setting->editSetting('vendor', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('vendor/vendor/setting', 'user_token=' . $this->session->data['user_token'], true));
		}
		
		
		$data['user_token'] =$this->session->data['user_token'] ;
		
		
		
		
		$data['heading_title'] = $this->language->get('text_settingvendor');
		$data['text_add'] = $this->language->get('text_vendorsetingadd');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_data'] = $this->language->get('tab_data');
		$data['entry_showorder_status'] = $this->language->get('entry_showorder_status');
		$data['entry_earnpayment_status'] = $this->language->get('entry_earnpayment_status');
		$data['help_earnpayment_status'] = $this->language->get('help_earnpayment_status');
		$data['help_showorder_status'] = $this->language->get('help_showorder_status');
		$data['tab_language'] = $this->language->get('tab_language');
		$data['text_sellerlist'] = $this->language->get('text_sellerlist');
		$data['text_loginseller'] = $this->language->get('text_loginseller');
		$data['text_sellerprofile'] = $this->language->get('text_sellerprofile');
		$data['text_sellerdash'] = $this->language->get('text_sellerdash');
		$data['text_sellerproduct'] = $this->language->get('text_sellerproduct');
		$data['text_sellerreview'] = $this->language->get('text_sellerreview');
		$data['text_managestore'] = $this->language->get('text_managestore');
		$data['text_manageprofile'] = $this->language->get('text_manageprofile');
		$data['text_download'] = $this->language->get('text_download');
		$data['text_manufacture'] = $this->language->get('text_manufacture');
		$data['text_logout'] = $this->language->get('text_logout');
		$data['text_afterloginseller'] = $this->language->get('text_afterloginseller');
		
		/* 23 04 2020 */
		$data['text_selernameinpro'] = $this->language->get('text_selernameinpro');
		$data['text_productpage'] = $this->language->get('text_productpage');
		$data['help_selernameinpro'] = $this->language->get('help_selernameinpro');
		/* 23 04 2020 */
		
        /* 08 04 2020 */
		$data['entry_hidevname'] = $this->language->get('entry_hidevname');
		$data['entry_hidevemail'] = $this->language->get('entry_hidevemail');
		$data['entry_hidevphoneno'] = $this->language->get('entry_hidevphoneno');
		$data['entry_hidevsocialicon'] = $this->language->get('entry_hidevsocialicon');
		$data['help_hidevname'] = $this->language->get('help_hidevname');
		$data['help_hidevemail'] = $this->language->get('help_hidevemail');
		$data['help_hidevponeno'] = $this->language->get('help_hidevponeno');
		$data['help_hidevsocialicon'] = $this->language->get('help_hidevsocialicon');
		$data['text_forallseller'] = $this->language->get('text_forallseller');
        /* 08 04 2020 */
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
			if (isset($this->error['error_keynotfound'])) {
				$data['error_keynotfound'] = $this->error['error_keynotfound'];
			} else {
				$data['error_keynotfound'] = '';
			}
			
		/* <!--04 05 2020-->  */
		if (isset($this->error['error_showorderwarning'])) {
			$data['error_showorderwarning'] = $this->error['error_showorderwarning'];
		} else {
			$data['error_showorderwarning'] = '';
		}
		/* <!--04 05 2020-->  */	

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_vendorlist'),
			'href' => $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'], true)
		);

		

		$data['action'] = $this->url->link('vendor/vendor/setting', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'], true);
		
		
		if (isset($this->request->post['vendor_customer2vendor'])) {
			$data['vendor_customer2vendor'] = $this->request->post['vendor_customer2vendor'];
		} else {
			$data['vendor_customer2vendor'] = $this->config->get('vendor_customer2vendor');
		}		
		if (isset($this->request->post['vendor_vendor2customer'])) {
			$data['vendor_vendor2customer'] = $this->request->post['vendor_vendor2customer'];
		} else {
			$data['vendor_vendor2customer'] = $this->config->get('vendor_vendor2customer');
		}	
        
        if (isset($this->request->post['vendor_vendorautoapprove'])) {
			$data['vendor_vendorautoapprove'] = $this->request->post['vendor_vendorautoapprove'];
		} else {
			$data['vendor_vendorautoapprove'] = $this->config->get('vendor_vendorautoapprove');
		}	
        
        if (isset($this->request->post['vendor_proautoapprove'])) {
			$data['vendor_proautoapprove'] = $this->request->post['vendor_proautoapprove'];
		} else {
			$data['vendor_proautoapprove'] = $this->config->get('vendor_proautoapprove');
		}	
		
        if (isset($this->request->post['vendor_hidevendorcontact'])) {
			$data['vendor_hidevendorcontact'] = $this->request->post['vendor_hidevendorcontact'];
		} else {
			$data['vendor_hidevendorcontact'] = $this->config->get('vendor_hidevendorcontact');
		}	
		
		/* 08 04 2020 */
		
         if (isset($this->request->post['vendor_hidevendorname'])) {
			$data['vendor_hidevendorname'] = $this->request->post['vendor_hidevendorname'];
		} else {
			 $data['vendor_hidevendorname'] = $this->config->get('vendor_hidevendorname');
		}
		
		
		if (isset($this->request->post['vendor_vhidevname'])) {
			$data['vendor_vhidevname'] = $this->request->post['vendor_vhidevname'];
		} else {
			 $data['vendor_vhidevname'] = $this->config->get('vendor_vhidevname');
		}
		
        if (isset($this->request->post['vendor_hidevemail'])) {
			$data['vendor_hidevemail'] = $this->request->post['vendor_hidevemail'];
		} else {
			 $data['vendor_hidevemail'] = $this->config->get('vendor_hidevemail');
		}
		
		
        if (isset($this->request->post['vendor_hidevponeno'])) {
			$data['vendor_hidevponeno'] = $this->request->post['vendor_hidevponeno'];
		} else {
			 $data['vendor_hidevponeno'] = $this->config->get('vendor_hidevponeno');
		}
		
        if (isset($this->request->post['vendor_hidevsocialicon'])) {
			$data['vendor_hidevsocialicon'] = $this->request->post['vendor_hidevsocialicon'];
		} else {
			$data['vendor_hidevsocialicon'] = $this->config->get('vendor_hidevsocialicon');
		}
				
      
        if (isset($this->request->post['vendor_vprivacy_id'])) {
			$data['vendor_vprivacy_id'] = $this->request->post['vendor_vprivacy_id'];
		} else {
			 $data['vendor_vprivacy_id'] = $this->config->get('vendor_vprivacy_id');
		}
		
		$this->load->model('catalog/information');

		$data['informations'] = $this->model_catalog_information->getInformations();

		/* 08 04 2020 */
		
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['vendor_languages'])) {
			$data['vendor_languages'] = $this->request->post['vendor_languages'];
		} else {
			$data['vendor_languages'] = $this->config->get('vendor_languages');;
		}
		
		if (isset($this->error['showorder_status'])) {
			$data['error_showorder_status'] = $this->error['showorder_status'];
		} else {
			$data['error_showorder_status'] = '';
		}
		if (isset($this->error['earnpayment_status'])) {
			$data['error_earnpayment_status'] = $this->error['earnpayment_status'];
		} else {
			$data['error_earnpayment_status'] = '';
		}
				
		if (isset($this->request->post['vendor_showorder_status'])) {
			$data['vendor_showorder_status'] = $this->request->post['vendor_showorder_status'];
		} elseif ($this->config->get('vendor_showorder_status')) {
			$data['vendor_showorder_status'] = $this->config->get('vendor_showorder_status');
		} else {
			$data['vendor_showorder_status'] = array();
		}
		
		if (isset($this->request->post['vendor_earnpayment_status'])) {
			$data['vendor_earnpayment_status'] = $this->request->post['vendor_earnpayment_status'];
		} elseif ($this->config->get('vendor_earnpayment_status')) {
			$data['vendor_earnpayment_status'] = $this->config->get('vendor_earnpayment_status');
		} else {
			$data['vendor_earnpayment_status'] = array();
		}
		
		/*############ 13 02 2021 ############*/
		
		if (isset($this->request->post['vendor_aboutstore'])) {
			$data['vendor_aboutstore'] = $this->request->post['vendor_aboutstore'];
		} elseif ($this->config->get('vendor_aboutstore')) {
			$data['vendor_aboutstore'] = $this->config->get('vendor_aboutstore');
		} else {
			$data['vendor_aboutstore'] = '';
		}
		
		if (isset($this->request->post['vendor_tabproduct'])) {
			$data['vendor_tabproduct'] = $this->request->post['vendor_tabproduct'];
		} elseif ($this->config->get('vendor_tabproduct')) {
			$data['vendor_tabproduct'] = $this->config->get('vendor_tabproduct');
		} else {
			$data['vendor_tabproduct'] = '';
		}		
		
		if (isset($this->request->post['vendor_review'])) {
			$data['vendor_review'] = $this->request->post['vendor_review'];
		} elseif ($this->config->get('vendor_review')) {
			$data['vendor_review'] = $this->config->get('vendor_review');
		} else {
			$data['vendor_review'] = '';
		}
		
		if (isset($this->request->post['vendor_productreview'])) {
			$data['vendor_productreview'] = $this->request->post['vendor_productreview'];
		} elseif ($this->config->get('vendor_productreview')) {
			$data['vendor_productreview'] = $this->config->get('vendor_productreview');
		} else {
			$data['vendor_productreview'] = '';
		}
		
		
		if (isset($this->request->post['vendor_profile'])) {
			$data['vendor_profile'] = $this->request->post['vendor_profile'];
		} elseif ($this->config->get('vendor_profile')) {
			$data['vendor_profile'] = $this->config->get('vendor_profile');
		} else {
			$data['vendor_profile'] = '';
		}
		
		if (isset($this->request->post['vendor_aboutstoresort'])) {
			$data['vendor_aboutstoresort'] = $this->request->post['vendor_aboutstoresort'];
		} elseif ($this->config->get('vendor_aboutstoresort')) {
			$data['vendor_aboutstoresort'] = $this->config->get('vendor_aboutstoresort');
		} else {
			$data['vendor_aboutstoresort'] = '';
		}
		
		if (isset($this->request->post['vendor_tabproductsort'])) {
			$data['vendor_tabproductsort'] = $this->request->post['vendor_tabproductsort'];
		} elseif ($this->config->get('vendor_tabproductsort')) {
			$data['vendor_tabproductsort'] = $this->config->get('vendor_tabproductsort');
		} else {
			$data['vendor_tabproductsort'] = '';
		}
		
		if (isset($this->request->post['vendor_reviewsort'])) {
			$data['vendor_reviewsort'] = $this->request->post['vendor_reviewsort'];
		} elseif ($this->config->get('vendor_reviewsort')) {
			$data['vendor_reviewsort'] = $this->config->get('vendor_reviewsort');
		} else {
			$data['vendor_reviewsort'] = '';
		}
		
		if (isset($this->request->post['vendor_productreviewsort'])) {
			$data['vendor_productreviewsort'] = $this->request->post['vendor_productreviewsort'];
		} elseif ($this->config->get('vendor_productreviewsort')) {
			$data['vendor_productreviewsort'] = $this->config->get('vendor_productreviewsort');
		} else {
			$data['vendor_productreviewsort'] = '';
		}	
		
		if (isset($this->request->post['vendor_profilestoresort'])) {
			$data['vendor_profilestoresort'] = $this->request->post['vendor_profilestoresort'];
		} elseif ($this->config->get('vendor_profilestoresort')) {
			$data['vendor_profilestoresort'] = $this->config->get('vendor_profilestoresort');
		} else {
			$data['vendor_profilestoresort'] = '';
		}	
		
		
		
		/*############ 13 02 2021 ############*/
		
		/*advance settings */
		if (isset($this->request->post['vendor_required_displayname'])) {
			$data['vendor_required_displayname'] = $this->request->post['vendor_required_displayname'];
		} elseif ($this->config->get('vendor_required_displayname')) {
			$data['vendor_required_displayname'] = $this->config->get('vendor_required_displayname');
		} else {
			$data['vendor_required_displayname'] = '';
		}
		
		if (isset($this->request->post['vendor_status_displayname'])) {
			$data['vendor_status_displayname'] = $this->request->post['vendor_status_displayname'];
		} elseif ($this->config->get('vendor_status_displayname')) {
			$data['vendor_status_displayname'] = $this->config->get('vendor_status_displayname');
		} else {
			$data['vendor_status_displayname'] = '';
		}
		
		if (isset($this->request->post['vendor_required_lastname'])) {
			$data['vendor_required_lastname'] = $this->request->post['vendor_required_lastname'];
		} elseif ($this->config->get('vendor_required_lastname')) {
			$data['vendor_required_lastname'] = $this->config->get('vendor_required_lastname');
		} else {
			$data['vendor_required_lastname'] = '';
		}
		
		if (isset($this->request->post['vendor_status_lastname'])) {
			$data['vendor_status_lastname'] = $this->request->post['vendor_status_lastname'];
		} elseif ($this->config->get('vendor_status_lastname')) {
			$data['vendor_status_lastname'] = $this->config->get('vendor_status_lastname');
		} else {
			$data['vendor_status_lastname'] = '';
		}
		
		if (isset($this->request->post['vendor_required_telephone'])) {
			$data['vendor_required_telephone'] = $this->request->post['vendor_required_telephone'];
		} elseif ($this->config->get('vendor_required_telephone')) {
			$data['vendor_required_telephone'] = $this->config->get('vendor_required_telephone');
		} else {
			$data['vendor_required_telephone'] = '';
		}
		
		if (isset($this->request->post['vendor_status_telephone'])) {
			$data['vendor_status_telephone'] = $this->request->post['vendor_status_telephone'];
		} elseif ($this->config->get('vendor_status_telephone')) {
			$data['vendor_status_telephone'] = $this->config->get('vendor_status_telephone');
		} else {
			$data['vendor_status_telephone'] = '';
		}
		
		if (isset($this->request->post['vendor_required_gstin'])) {
			$data['vendor_required_gstin'] = $this->request->post['vendor_required_gstin'];
		} elseif ($this->config->get('vendor_required_gstin')) {
			$data['vendor_required_gstin'] = $this->config->get('vendor_required_gstin');
		} else {
			$data['vendor_required_gstin'] = '';
		}
		
		if (isset($this->request->post['vendor_status_gstin'])) {
			$data['vendor_status_gstin'] = $this->request->post['vendor_status_gstin'];
		} elseif ($this->config->get('vendor_status_gstin')) {
			$data['vendor_status_gstin'] = $this->config->get('vendor_status_gstin');
		} else {
			$data['vendor_status_gstin'] = '';
		}
		
		if (isset($this->request->post['vendor_required_company'])) {
			$data['vendor_required_company'] = $this->request->post['vendor_required_company'];
		} elseif ($this->config->get('vendor_required_company')) {
			$data['vendor_required_company'] = $this->config->get('vendor_required_company');
		} else {
			$data['vendor_required_company'] = '';
		}
		
		if (isset($this->request->post['vendor_status_company'])) {
			$data['vendor_status_company'] = $this->request->post['vendor_status_company'];
		} elseif ($this->config->get('vendor_status_company')) {
			$data['vendor_status_company'] = $this->config->get('vendor_status_company');
		} else {
			$data['vendor_status_company'] = '';
		}
		
		if (isset($this->request->post['vendor_required_address_1'])) {
			$data['vendor_required_address_1'] = $this->request->post['vendor_required_address_1'];
		} elseif ($this->config->get('vendor_required_address_1')) {
			$data['vendor_required_address_1'] = $this->config->get('vendor_required_address_1');
		} else {
			$data['vendor_required_address_1'] = '';
		}
		
		if (isset($this->request->post['vendor_status_address_1'])) {
			$data['vendor_status_address_1'] = $this->request->post['vendor_status_address_1'];
		} elseif ($this->config->get('vendor_status_address_1')) {
			$data['vendor_status_address_1'] = $this->config->get('vendor_status_address_1');
		} else {
			$data['vendor_status_address_1'] = '';
		}
		
		if (isset($this->request->post['vendor_required_address_2'])) {
			$data['vendor_required_address_2'] = $this->request->post['vendor_required_address_2'];
		} elseif ($this->config->get('vendor_required_address_2')) {
			$data['vendor_required_address_2'] = $this->config->get('vendor_required_address_2');
		} else {
			$data['vendor_required_address_2'] = '';
		}
		
		if (isset($this->request->post['vendor_status_address_2'])) {
			$data['vendor_status_address_2'] = $this->request->post['vendor_status_address_2'];
		} elseif ($this->config->get('vendor_status_address_2')) {
			$data['vendor_status_address_2'] = $this->config->get('vendor_status_address_2');
		} else {
			$data['vendor_status_address_2'] = '';
		}
		
		if (isset($this->request->post['vendor_required_city'])) {
			$data['vendor_required_city'] = $this->request->post['vendor_required_city'];
		} elseif ($this->config->get('vendor_required_city')) {
			$data['vendor_required_city'] = $this->config->get('vendor_required_city');
		} else {
			$data['vendor_required_city'] = '';
		}
		
		if (isset($this->request->post['vendor_status_city'])) {
			$data['vendor_status_city'] = $this->request->post['vendor_status_city'];
		} elseif ($this->config->get('vendor_status_city')) {
			$data['vendor_status_city'] = $this->config->get('vendor_status_city');
		} else {
			$data['vendor_status_city'] = '';
		}
		
		if (isset($this->request->post['vendor_required_country'])) {
			$data['vendor_required_country'] = $this->request->post['vendor_required_country'];
		} elseif ($this->config->get('vendor_required_country')) {
			$data['vendor_required_country'] = $this->config->get('vendor_required_country');
		} else {
			$data['vendor_required_country'] = '';
		}
		
		if (isset($this->request->post['vendor_status_country'])) {
			$data['vendor_status_country'] = $this->request->post['vendor_status_country'];
		} elseif ($this->config->get('vendor_status_country')) {
			$data['vendor_status_country'] = $this->config->get('vendor_status_country');
		} else {
			$data['vendor_status_country'] = '';
		}
		
		if (isset($this->request->post['vendor_required_zone'])) {
			$data['vendor_required_zone'] = $this->request->post['vendor_required_zone'];
		} elseif ($this->config->get('vendor_required_zone')) {
			$data['vendor_required_zone'] = $this->config->get('vendor_required_zone');
		} else {
			$data['vendor_required_zone'] = '';
		}
		
		if (isset($this->request->post['vendor_status_zone'])) {
			$data['vendor_status_zone'] = $this->request->post['vendor_status_zone'];
		} elseif ($this->config->get('vendor_status_zone')) {
			$data['vendor_status_zone'] = $this->config->get('vendor_status_zone');
		} else {
			$data['vendor_status_zone'] = '';
		}
		
		
		if (isset($this->request->post['vendor_vpostcode'])) {
			$data['vendor_vpostcode'] = $this->request->post['vendor_vpostcode'];
		} elseif ($this->config->get('vendor_vpostcode')) {
			$data['vendor_vpostcode'] = $this->config->get('vendor_vpostcode');
		} else {
			$data['vendor_vpostcode'] = '';
		}
		
		if (isset($this->request->post['vendor_status_postcode'])) {
			$data['vendor_status_postcode'] = $this->request->post['vendor_status_postcode'];
		} elseif ($this->config->get('vendor_status_postcode')) {
			$data['vendor_status_postcode'] = $this->config->get('vendor_status_postcode');
		} else {
			$data['vendor_status_postcode'] = '';
		}
		
		if (isset($this->request->post['vendor_required_about'])) {
			$data['vendor_required_about'] = $this->request->post['vendor_required_about'];
		} elseif ($this->config->get('vendor_required_about')) {
			$data['vendor_required_about'] = $this->config->get('vendor_required_about');
		} else {
			$data['vendor_required_about'] = '';
		}
		
		if (isset($this->request->post['vendor_status_about'])) {
			$data['vendor_status_about'] = $this->request->post['vendor_status_about'];
		} elseif ($this->config->get('vendor_status_about')) {
			$data['vendor_status_about'] = $this->config->get('vendor_status_about');
		} else {
			$data['vendor_status_about'] = '';
		}
		
		if (isset($this->request->post['vendor_status_image'])) {
			$data['vendor_status_image'] = $this->request->post['vendor_status_image'];
		} elseif ($this->config->get('vendor_status_image')) {
			$data['vendor_status_image'] = $this->config->get('vendor_status_image');
		} else {
			$data['vendor_status_image'] = '';
		}
		
		//general tab
		
		if (isset($this->request->post['vendor_required_meta_description'])) {
			$data['vendor_required_meta_description'] = $this->request->post['vendor_required_meta_description'];
		} elseif ($this->config->get('vendor_required_meta_description')) {
			$data['vendor_required_meta_description'] = $this->config->get('vendor_required_meta_description');
		} else {
			$data['vendor_required_meta_description'] = '';
		}
		
		if (isset($this->request->post['vendor_status_meta_description'])) {
			$data['vendor_status_meta_description'] = $this->request->post['vendor_status_meta_description'];
		} elseif ($this->config->get('vendor_status_meta_description')) {
			$data['vendor_status_meta_description'] = $this->config->get('vendor_status_meta_description');
		} else {
			$data['vendor_status_meta_description'] = '';
		}
		
		if (isset($this->request->post['vendor_required_description'])) {
			$data['vendor_required_description'] = $this->request->post['vendor_required_description'];
		} elseif ($this->config->get('vendor_required_description')) {
			$data['vendor_required_description'] = $this->config->get('vendor_required_description');
		} else {
			$data['vendor_required_description'] = '';
		}
		
		if (isset($this->request->post['vendor_status_description'])) {
			$data['vendor_status_description'] = $this->request->post['vendor_status_description'];
		} elseif ($this->config->get('vendor_status_description')) {
			$data['vendor_status_description'] = $this->config->get('vendor_status_description');
		} else {
			$data['vendor_status_description'] = '';
		}
		
		if (isset($this->request->post['vendor_required_shipping_policy'])) {
			$data['vendor_required_shipping_policy'] = $this->request->post['vendor_required_shipping_policy'];
		} elseif ($this->config->get('vendor_required_shipping_policy')) {
			$data['vendor_required_shipping_policy'] = $this->config->get('vendor_required_shipping_policy');
		} else {
			$data['vendor_required_shipping_policy'] = '';
		}
		
		if (isset($this->request->post['vendor_status_shipping_policy'])) {
			$data['vendor_status_shipping_policy'] = $this->request->post['vendor_status_shipping_policy'];
		} elseif ($this->config->get('vendor_status_shipping_policy')) {
			$data['vendor_status_shipping_policy'] = $this->config->get('vendor_status_shipping_policy');
		} else {
			$data['vendor_status_shipping_policy'] = '';
		}
		
		
		if (isset($this->request->post['vendor_required_return_policy'])) {
			$data['vendor_required_return_policy'] = $this->request->post['vendor_required_return_policy'];
		} elseif ($this->config->get('vendor_required_return_policy')) {
			$data['vendor_required_return_policy'] = $this->config->get('vendor_required_return_policy');
		} else {
			$data['vendor_required_return_policy'] = '';
		}
		
		if (isset($this->request->post['vendor_status_return_policy'])) {
			$data['vendor_status_return_policy'] = $this->request->post['vendor_status_return_policy'];
		} elseif ($this->config->get('vendor_status_return_policy')) {
			$data['vendor_status_return_policy'] = $this->config->get('vendor_status_return_policy');
		} else {
			$data['vendor_status_return_policy'] = '';
		}
		
		
		if (isset($this->request->post['vendor_required_meta_keyword'])) {
			$data['vendor_required_meta_keyword'] = $this->request->post['vendor_required_meta_keyword'];
		} elseif ($this->config->get('vendor_required_meta_keyword')) {
			$data['vendor_required_meta_keyword'] = $this->config->get('vendor_required_meta_keyword');
		} else {
			$data['vendor_required_meta_keyword'] = '';
		}
		
		if (isset($this->request->post['vendor_status_meta_keyword'])) {
			$data['vendor_status_meta_keyword'] = $this->request->post['vendor_status_meta_keyword'];
		} elseif ($this->config->get('vendor_status_meta_keyword')) {
			$data['vendor_status_meta_keyword'] = $this->config->get('vendor_status_meta_keyword');
		} else {
			$data['vendor_status_meta_keyword'] = '';
		}
		
		//datastore
		
		if (isset($this->request->post['vendor_required_bank_detail'])) {
			$data['vendor_required_bank_detail'] = $this->request->post['vendor_required_bank_detail'];
		} elseif ($this->config->get('vendor_required_bank_detail')) {
			$data['vendor_required_bank_detail'] = $this->config->get('vendor_required_bank_detail');
		} else {
			$data['vendor_required_bank_detail'] = '';
		}
		
		if (isset($this->request->post['vendor_status_bank_detail'])) {
			$data['vendor_status_bank_detail'] = $this->request->post['vendor_status_bank_detail'];
		} elseif ($this->config->get('vendor_status_bank_detail')) {
			$data['vendor_status_bank_detail'] = $this->config->get('vendor_status_bank_detail');
		} else {
			$data['vendor_status_bank_detail'] = '';
		}
		
		if (isset($this->request->post['vendor_required_storeabout'])) {
			$data['vendor_required_storeabout'] = $this->request->post['vendor_required_storeabout'];
		} elseif ($this->config->get('vendor_required_storeabout')) {
			$data['vendor_required_storeabout'] = $this->config->get('vendor_required_storeabout');
		} else {
			$data['vendor_required_storeabout'] = '';
		}
		
		if (isset($this->request->post['vendor_status_storeabout'])) {
			$data['vendor_status_storeabout'] = $this->request->post['vendor_status_storeabout'];
		} elseif ($this->config->get('vendor_status_storeabout')) {
			$data['vendor_status_storeabout'] = $this->config->get('vendor_status_storeabout');
		} else {
			$data['vendor_status_storeabout'] = '';
		}
		
		if (isset($this->request->post['vendor_required_mapurl'])) {
			$data['vendor_required_mapurl'] = $this->request->post['vendor_required_mapurl'];
		} elseif ($this->config->get('vendor_required_mapurl')) {
			$data['vendor_required_mapurl'] = $this->config->get('vendor_required_mapurl');
		} else {
			$data['vendor_required_mapurl'] = '';
		}
		
		if (isset($this->request->post['vendor_status_mapurl'])) {
			$data['vendor_status_mapurl'] = $this->request->post['vendor_status_mapurl'];
		} elseif ($this->config->get('vendor_status_mapurl')) {
			$data['vendor_status_mapurl'] = $this->config->get('vendor_status_mapurl');
		} else {
			$data['vendor_status_mapurl'] = '';
		}
		
		
		if (isset($this->request->post['vendor_required_tax_number'])) {
			$data['vendor_required_tax_number'] = $this->request->post['vendor_required_tax_number'];
		} elseif ($this->config->get('vendor_required_tax_number')) {
			$data['vendor_required_tax_number'] = $this->config->get('vendor_required_tax_number');
		} else {
			$data['vendor_required_tax_number'] = '';
		}
		
		if (isset($this->request->post['vendor_status_tax_number'])) {
			$data['vendor_status_tax_number'] = $this->request->post['vendor_status_tax_number'];
		} elseif ($this->config->get('vendor_status_tax_number')) {
			$data['vendor_status_tax_number'] = $this->config->get('vendor_status_tax_number');
		} else {
			$data['vendor_status_tax_number'] = '';
		}
		
		
		if (isset($this->request->post['vendor_required_shipping_charge'])) {
			$data['vendor_required_shipping_charge'] = $this->request->post['vendor_required_shipping_charge'];
		} elseif ($this->config->get('vendor_required_shipping_charge')) {
			$data['vendor_required_shipping_charge'] = $this->config->get('vendor_required_shipping_charge');
		} else {
			$data['vendor_required_shipping_charge'] = '';
		}
		
		if (isset($this->request->post['vendor_status_shipping_charge'])) {
			$data['vendor_status_shipping_charge'] = $this->request->post['vendor_status_shipping_charge'];
		} elseif ($this->config->get('vendor_status_shipping_charge')) {
			$data['vendor_status_shipping_charge'] = $this->config->get('vendor_status_shipping_charge');
		} else {
			$data['vendor_status_shipping_charge'] = '';
		}
		
		
		if (isset($this->request->post['vendor_status_url'])) {
			$data['vendor_status_url'] = $this->request->post['vendor_status_url'];
		} elseif ($this->config->get('vendor_status_url')) {
			$data['vendor_status_url'] = $this->config->get('vendor_status_url');
		} else {
			$data['vendor_status_url'] = '';
		}
		
		if (isset($this->request->post['vendor_required_url'])) {
			$data['vendor_required_url'] = $this->request->post['vendor_required_url'];
		} elseif ($this->config->get('vendor_required_url')) {
			$data['vendor_required_url'] = $this->config->get('vendor_required_url');
		} else {
			$data['vendor_required_url'] = '';
		}
		
		if (isset($this->request->post['vendor_status_logo'])) {
			$data['vendor_status_logo'] = $this->request->post['vendor_status_logo'];
		} elseif ($this->config->get('vendor_status_logo')) {
			$data['vendor_status_logo'] = $this->config->get('vendor_status_logo');
		} else {
			$data['vendor_status_logo'] = '';
		}
		
		if (isset($this->request->post['vendor_status_banner'])) {
			$data['vendor_status_banner'] = $this->request->post['vendor_status_banner'];
		} elseif ($this->config->get('vendor_status_banner')) {
			$data['vendor_status_banner'] = $this->config->get('vendor_status_banner');
		} else {
			$data['vendor_status_banner'] = '';
		}
		
		//payment detail
			
		if (isset($this->request->post['vendor_status_paypal'])) {
			$data['vendor_status_paypal'] = $this->request->post['vendor_status_paypal'];
		} elseif ($this->config->get('vendor_status_paypal')) {
			$data['vendor_status_paypal'] = $this->config->get('vendor_status_paypal');
		} else {
			$data['vendor_status_paypal'] = '';
		}
		
		if (isset($this->request->post['vendor_status_bank'])) {
			$data['vendor_status_bank'] = $this->request->post['vendor_status_bank'];
		} elseif ($this->config->get('vendor_status_bank')) {
			$data['vendor_status_bank'] = $this->config->get('vendor_status_bank');
		} else {
			$data['vendor_status_bank'] = '';
		}
		
		/*advance settings */
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();		
						
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('vendor/vendor_setting', $data));
	}	
		
	protected function validate() {
		
		if (!$this->user->hasPermission('modify', 'vendor/vendor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}		
		
		
			
		
		if (!isset($this->request->post['vendor_showorder_status'])) {
			$this->error['showorder_status'] = $this->language->get('error_showorder_status');
		}
		
		if (!isset($this->request->post['vendor_earnpayment_status'])) {
			$this->error['earnpayment_status'] = $this->language->get('error_earnpayment_status');
		}
		
				
		/* <!--04 05 2020-->  */
		if ($this->error) {			
		$this->error['error_showorderwarning'] =  $this->language->get('error_showorderwarning');
		}
		
		/* <!--04 05 2020-->  */
		return !$this->error;
	}		
}