<?php
class ControllerVendorEnquiry extends Controller {
	private $error = array();

	public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}
		$this->load->language('vendor/enquiry');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/enquiry');

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['filter_product'])) {
			$filter_product = $this->request->get['filter_product'];
		} else {
			$filter_product = '';
		}
		
		if (isset($this->request->get['filter_productvalue'])) {
			$filter_productvalue = $this->request->get['filter_productvalue'];
		} else {
			$filter_productvalue = '';
		}
		
		
		if (isset($this->request->get['filter_enqname'])) {
			$filter_enqname = $this->request->get['filter_enqname'];
		} else {
			$filter_enqname = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'r.date_added';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
		}
		
		/* 12 02 2020 */
		if (isset($this->request->get['filter_productvalue'])) {
			$url .= '&filter_productvalue=' . urlencode(html_entity_decode($this->request->get['filter_productvalue'], ENT_QUOTES, 'UTF-8'));
		}		
		/* 12 02 2020 */
		if (isset($this->request->get['filter_enqname'])) {
			$url .= '&filter_enqname=' . urlencode(html_entity_decode($this->request->get['filter_enqname'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/enquiry')
		);

		$data['enquires'] = array();

		$filter_data = array(
			'vendor_id' 		=> $this->vendor->getId(),
			/* 12 02 2020 */			
			'filter_productvalue'=> $filter_productvalue,
			/* 12 02 2020 */
			'filter_product'    => $filter_product,
			'filter_enqname'       => $filter_enqname,
			'filter_status'     => $filter_status,
			'filter_date_added' => $filter_date_added,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$this->load->model('vendor/product');
		$this->load->model('vendor/enquiry');
		$review_total = $this->model_vendor_enquiry->getTotalgetEnquiries($filter_data);
		$results = $this->model_vendor_enquiry->getEnquiries($filter_data);
		/* 28 01 2020 */
		$vendor_id= $this->vendor->getId();		
		/* 28 01 2020 */
		foreach ($results as $result) {
			$product_info = $this->model_vendor_product->getProduct($result['product_id'], $vendor_id);
			if(!empty($product_info)){
				$pname = $product_info['name'];
			} else {
				$pname = '';
			}

			$customer_info = $this->model_vendor_enquiry->getCustomer($result['customer_id']);
			/* 19 02 2020 */
			if(!empty($customer_info['firstname'])){
				$cname = '<span class="label label-success">'.$customer_info['firstname'].' '.$customer_info['lastname'].'</span>';
			} else {
				$cname = '<span class="label label-info">'.$this->language->get('text_guest').'</span>';
			}
			/* 19 02 2020 */
			$data['enquires'][] = array(
				'inquiry_id'   => $result['inquiry_id'],
				'name'         => $result['name'],
				'email'         => $result['email'],
				'pname'        => $pname,
				'cname'        => $cname,
				'description' => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
				'status'       => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'date_added'   => $result['date_added'],
				// <!--07-03-2019 update-->
				'producturl'   =>  $this->url->link('product/product','product_id=' . $result['product_id'] . $url, true)
				// <!--07-03-2019 update-->
			);
		}
		
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_select'] = $this->language->get('text_select');
		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_none'] = $this->language->get('text_none');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_email'] = $this->language->get('column_email');
		$data['column_product'] = $this->language->get('column_product');
		$data['column_customer'] = $this->language->get('column_customer');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_date_added'] = $this->language->get('column_date_added');
		$data['column_action'] = $this->language->get('column_action');

		$data['entry_product'] = $this->language->get('entry_product');
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_rating'] = $this->language->get('entry_rating');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_date_added'] = $this->language->get('entry_date_added');

		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_filter'] = $this->language->get('button_filter');
		
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
			$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
		}
		
		
		if (isset($this->request->get['filter_productvalue'])) {
			$url .= '&filter_productvalue=' . urlencode(html_entity_decode($this->request->get['filter_productvalue'], ENT_QUOTES, 'UTF-8'));
		}
		
		
		if (isset($this->request->get['filter_enqname'])) {
			$url .= '&filter_enqname=' . urlencode(html_entity_decode($this->request->get['filter_enqname'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('vendor/enquiry', 'sort=name' . $url, true);
		$data['sort_email'] = $this->url->link('vendor/enquiry', 'sort=email' . $url, true);
		$data['sort_product'] = $this->url->link('vendor/enquiry','sort=product' . $url, true);
		$data['sort_customer'] = $this->url->link('vendor/enquiry','sort=customer' . $url, true);
		$data['sort_status'] = $this->url->link('vendor/enquiry','sort=status' . $url, true);
		$data['sort_date_added'] = $this->url->link('vendor/enquiry','sort=date_added' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_productvalue'])) {
			$url .= '&filter_productvalue=' . urlencode(html_entity_decode($this->request->get['filter_productvalue'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_enqname'])) {
			$url .= '&filter_enqname=' . urlencode(html_entity_decode($this->request->get['filter_enqname'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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
		$pagination->url = $this->url->link('vendor/enquiry',$url . 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($review_total - $this->config->get('config_limit_admin'))) ? $review_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $review_total, ceil($review_total / $this->config->get('config_limit_admin')));
		$vendor_id= $this->vendor->getId();
		
		/* 28 01 2020 */
		
		
		$data['filter_product'] = $filter_product;
		$data['filter_productvalue'] = $filter_productvalue;

		$data['filter_enqname'] = $filter_enqname;
		$data['filter_status'] = $filter_status;
		$data['filter_date_added'] = $filter_date_added;

		$data['sort'] = $sort;
		$data['order'] = $order;
		/* tmd vendor2 customer condtion  */
		$data['vendor2customer'] = $this->config->get('vendor_vendor2customer');
		/* tmd vendor2 customer condtion  */
		$data['header'] = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer'] = $this->load->controller('vendor/footer');

		
		$this->response->setOutput($this->load->view('vendor/enquiry', $data));
	}

	/* update function 12 02 2020 */
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
		$enqnames = $this->model_vendor_enquiry->getEnquiries($filter_data);
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
}
