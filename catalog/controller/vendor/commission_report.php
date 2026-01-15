<?php
class ControllerVendorCommissionreport  extends Controller {
	private $error = array();
	public function index() {
		$this->load->language('vendor/commission_report');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('vendor/commission_report');	
		$this->getList();
	}
 
	public function delete() {
		$this->load->language('vendor/commission_report');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('vendor/commission_report');
			//change delete//
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $order_product_id){
				$this->model_vendor_commission_report->deleteCommissionReport($order_product_id);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';

			$this->response->redirect($this->url->link('vendor/commission_report.tpl', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
		$this->getList();
	}

	public function getList() {
		if (isset($this->request->get['filter_seller'])) {
			$filter_seller = $this->request->get['filter_seller'];
		} else {
		 	$filter_seller = '';
		}
		
		if (isset($this->request->get['filter_from'])) {
			$filter_from = $this->request->get['filter_from'];
		} else {
		 	$filter_from = '';
		}
		
		if (isset($this->request->get['filter_to'])) {
			$filter_to = $this->request->get['filter_to'];
		} else {
		 	$filter_to = '';
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
		
		if (isset($this->request->get['filter_from'])) {
			$url .= '&filter_from=' . $this->request->get['filter_from'];
		}
		
		if (isset($this->request->get['filter_to'])) {
			$url .= '&filter_to=' . $this->request->get['filter_to'];
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
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/commission_report', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);
		
		$data['delete']	=$this->url->link('vendor/commission_report/delete','&token='.$this->session->data['token'].$url,'SSL');
		
		$data['commissionreports'] = array();

		$filter_data = array(
			'filter_seller'  => $filter_seller,
			'filter_from'    => $filter_from,
			'filter_to'      => $filter_to,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		$this->load->model('vendor/vendor');
		$report_total = $this->model_vendor_commission_report->getTotalCommissionReport($filter_data);
		$reports = $this->model_vendor_commission_report->getCommissionReports($filter_data);

		foreach($reports as $report){
			//	PRINT_r($report);DIE();
			$sellers = $this->model_vendor_vendor->getVendor($report['vendor_id']);
			if(isset($sellers['firstname'])){
				$sellername = $sellers['firstname'];
			} else {
				$sellername ='';
			}
			
		 	$currency_info = $this->model_vendor_commission_report->getOrderCurrency($report['order_id']);
		
			if(isset($currency_info['currency_code'])) {
				$currency = $currency_info['currency_code'];
			} else {
				$currency=$this->config->get('config_currency');
			}
			/* 07-03-2019 remove code */
			
			$data['commissionreports'][] = array(
				'order_product_id'=>$report['order_product_id'],
				'name'			=>$report['name'],
				'model'			=>$report['model'],
				'quantity'		=>$report['quantity'],
				'price'			=>$report['price'],
				'sellername'	=>$sellername,
				/* 07-03-2019 update code */
				'totalcommission'=> $this->currency->format($report['totalcommission'],$currency),
				/* 07-03-2019 update code */
				'date_added'	=>$report['date_added'],
				'commissionper'	=>$report['commissionper'],
				'commissionfix'	=>$report['commissionfix']
			);
		}
	 	
   		
		$data['heading_title']          = $this->language->get('heading_title');
		$data['text_list']           	= $this->language->get('text_list');
		$data['text_no_results'] 		= $this->language->get('text_no_results');
		$data['text_confirm']			= $this->language->get('text_confirm');
		$data['text_none'] 				= $this->language->get('text_none');
	 	$data['text_enable']            = $this->language->get('text_enable');
		$data['text_disable']           = $this->language->get('text_disable');
		$data['text_select']            = $this->language->get('text_select');
		$data['column_seller']		    = $this->language->get('column_seller');
		$data['column_name']			= $this->language->get('column_name');
		$data['column_date']			= $this->language->get('column_date');
		$data['column_model']			= $this->language->get('column_model');
		$data['column_qty']			    = $this->language->get('column_qty');
		$data['column_price']			= $this->language->get('column_price');
		$data['column_percentage']		= $this->language->get('column_percentage');
		$data['column_fixed']		    = $this->language->get('column_fixed');
		$data['column_total']		    = $this->language->get('column_total');
		$data['entry_from']			    = $this->language->get('entry_from');
		$data['entry_to']			    = $this->language->get('entry_to');
		$data['button_remove']          = $this->language->get('button_remove');
		$data['button_delete']          = $this->language->get('button_delete');
		$data['button_filter']          = $this->language->get('button_filter');
		$data['button_view']            = $this->language->get('button_view');
		$data['text_confirm']           = $this->language->get('text_confirm');
		$data['name']                   = $this->language->get('name');
		$data['token']                  = $this->session->data['token'];
		
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
		
		if (isset($this->request->get['filter_from'])) {
			$url .= '&filter_from=' . $this->request->get['filter_from'];
		}
		
		if (isset($this->request->get['filter_to'])) {
			$url .= '&filter_to=' . $this->request->get['filter_to'];
		}
		
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
	 
		$data['sort_seller']    	= $this->url->link('vendor/commission_report', 'token=' . $this->session->data['token'] . '&sort=seller' . $url, 'SSL');
		$data['sort_name']  		= $this->url->link('vendor/commission_report', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$data['sort_model']  		= $this->url->link('vendor/commission_report', 'token=' . $this->session->data['token'] . '&sort=model' . $url, 'SSL');
		$data['sort_qty']  		    = $this->url->link('vendor/commission_report', 'token=' . $this->session->data['token'] . '&sort=qty' . $url, 'SSL');
		$data['sort_price']  		= $this->url->link('vendor/commission_report', 'token=' . $this->session->data['token'] . '&sort=price' . $url, 'SSL');
		$data['sort_date']  		= $this->url->link('vendor/commission_report', 'token=' . $this->session->data['token'] . '&sort=date' . $url, 'SSL');
		$data['sort_percentage']  	= $this->url->link('vendor/commission_report', 'token=' . $this->session->data['token'] . '&sort=percentage' . $url, 'SSL');
		$data['sort_fixed']  	    = $this->url->link('vendor/commission_report', 'token=' . $this->session->data['token'] . '&sort=fixed' . $url, 'SSL');
		
		$url = '';
		
		if (isset($this->request->get['filter_seller'])) {
			$url .= '&filter_seller=' . $this->request->get['filter_seller'];
		}
		
		if (isset($this->request->get['filter_from'])) {
			$url .= '&filter_from=' . $this->request->get['filter_from'];
		}
		
		if (isset($this->request->get['filter_to'])) {
			$url .= '&filter_to=' . $this->request->get['filter_to'];
		}
		 
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
				       
		$pagination 		= new Pagination();
		$pagination->total 	= $report_total;
		$pagination->page  	= $page;
		$pagination->limit 	= $this->config->get('config_limit_admin');
		$pagination->url   	= $this->url->link('vendor/commission_report', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($report_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($report_total - $this->config->get('config_limit_admin'))) ? $report_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $report_total, ceil($report_total / $this->config->get('config_limit_admin')));
		
		$data['filter_seller']	= $filter_seller;
		$data['filter_from']	= $filter_from;
		$data['filter_to']		= $filter_to;
		$data['sort']		= $sort;
		$data['order']		= $order;
				
		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('vendor/commission_report', $data));
	}
			    
 	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'vendor/commission_report')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
			
}
?>