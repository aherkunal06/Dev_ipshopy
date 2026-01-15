<?php
class ControllerVendorReviewreport  extends Controller {
	private $error = array();
	public function index() {
		$this->load->language('vendor/review_report');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('vendor/review_report');	
		$this->getList();
	}
 
	public function delete() {
		$this->load->language('vendor/review_report');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('vendor/review_report');
			//change delete//
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $review_id){
				$this->model_vendor_review_report->deleteReviewReport($review_id);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';

			$this->response->redirect($this->url->link('vendor/review_report', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList();
	}

	public function getList() {

		if (isset($this->request->get['filter_seller'])) {
			$filter_seller = $this->request->get['filter_seller'];
		} else {
		 	$filter_seller = '';
		}

		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
		 	$filter_customer = '';
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'order_product_id';
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

		if (isset($this->request->get['filter_seller'])) {
			$url .= '&filter_seller=' . $this->request->get['filter_seller'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . $this->request->get['filter_customer'];
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
			'href' => $this->url->link('vendor/review_report', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);
		
		$data['delete']	=$this->url->link('vendor/review_report/delete','&user_token='.$this->session->data['user_token'].$url,true);
		
		$data['reviewreports'] = array();

		$filter_data = array(
			'filter_seller'  => $filter_seller,
			'filter_customer'  => $filter_customer,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		$this->load->model('vendor/vendor');
		$this->load->model('customer/customer');
		$this->load->model('vendor/review_field');
		$report_review_total = $this->model_vendor_review_report->getTotalReviewReport($filter_data);
		$reports = $this->model_vendor_review_report->getReviewReports($filter_data);
		foreach($reports as $report){
			$sellers = $this->model_vendor_vendor->getVendor($report['vendor_id']);
			if(isset($sellers['firstname'])){
				$sellername = $sellers['firstname'];
			} else {
				$sellername ='';
			}

			$customer = $this->model_customer_customer->getCustomer($report['customer_id']);
			if(isset($customer['firstname'])){
				$customername = $customer['firstname'];
			} else {
				$customername ='';
			}

			$ratings=array();
			$field_info = $this->model_vendor_review_report->getField($report['review_id'],$report['vendor_id']);
			
			foreach($field_info as $field){
				$ratings[]=array(
					'field_name' => $field['field_name'],
					'value' => $field['value']
				);
			}
			
			$data['reviewreports'][] = array(
				'review_id'=>$report['review_id'],
				'sellername'=>$sellername,
				'customername'=>$customername.'</br>',
				'ratings'=>$ratings,
				'status'   =>($report['status']) ? $this->language->get('text_enable') : $this->language->get('text_disable'),
				'date_added'	=>$report['date_added']
				
			);
		}
	 	
   		
		$data['heading_title']     = $this->language->get('heading_title');
		$data['text_list']         = $this->language->get('text_list');
		$data['text_no_results']   = $this->language->get('text_no_results');
		$data['text_confirm']	   = $this->language->get('text_confirm');
		$data['text_none'] 		   = $this->language->get('text_none');
	 	$data['text_enable']       = $this->language->get('text_enable');
		$data['text_disable']      = $this->language->get('text_disable');
		$data['text_select']       = $this->language->get('text_select');
		$data['column_seller']	   = $this->language->get('column_seller');
		$data['column_customer']   = $this->language->get('column_customer');
		$data['column_rating']	   = $this->language->get('column_rating');
		$data['column_status']	   = $this->language->get('column_status');
		$data['column_date']	   = $this->language->get('column_date');
		$data['entry_seller']	   = $this->language->get('entry_seller');
		$data['entry_customer']	   = $this->language->get('entry_customer');
		$data['button_remove']     = $this->language->get('button_remove');
		$data['button_delete']     = $this->language->get('button_delete');
		$data['button_filter']     = $this->language->get('button_filter');
		$data['button_view']       = $this->language->get('button_view');
		$data['text_confirm']      = $this->language->get('text_confirm');
		$data['name']              = $this->language->get('name');
		$data['user_token']             = $this->session->data['user_token'];
		
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
		if (isset($this->request->get['filter_seller'])) {
			$url .= '&filter_seller=' . $this->request->get['filter_seller'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . $this->request->get['filter_customer'];
		}
		
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
	 
		$data['sort_seller']    = $this->url->link('vendor/review_report', 'user_token=' . $this->session->data['user_token'] . '&sort=seller' . $url, true);
		$data['sort_customer']  = $this->url->link('vendor/review_report', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url, true);
		$data['sort_rating']  	= $this->url->link('vendor/review_report', 'user_token=' . $this->session->data['user_token'] . '&sort=rating' . $url, true);
		$data['sort_status']  	= $this->url->link('vendor/review_report', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		$data['sort_date']  	= $this->url->link('vendor/review_report', 'user_token=' . $this->session->data['user_token'] . '&sort=date' . $url, true);
		
		$url = '';

		if (isset($this->request->get['filter_seller'])) {
			$url .= '&filter_seller=' . $this->request->get['filter_seller'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . $this->request->get['filter_customer'];
		}
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
				       
		$pagination 		= new Pagination();
		$pagination->total 	= $report_review_total;
		$pagination->page  	= $page;
		$pagination->limit 	= $this->config->get('config_limit_admin');
		$pagination->url   	= $this->url->link('vendor/review_report', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($report_review_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($report_review_total - $this->config->get('config_limit_admin'))) ? $report_review_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $report_review_total, ceil($report_review_total / $this->config->get('config_limit_admin')));
		
		$data['filter_seller']	= $filter_seller;
		$data['filter_customer']	= $filter_customer;
		$data['sort']		= $sort;
		$data['order']		= $order;
				
		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');
		

		$this->response->setOutput($this->load->view('vendor/review_report', $data));
	}
			    
 	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'vendor/review_report')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
			
}
?>