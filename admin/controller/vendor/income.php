<?php
class ControllerVendorIncome  extends Controller {
	private $error = array();
	public function index() {
		$this->load->language('vendor/income');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('vendor/income');	
		$this->getList();
	}
	
	public function add() {
		$this->load->language('vendor/income');

		$this->document->setTitle($this->language->get('payment_title'));

		$this->load->model('vendor/income');
 
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
	
	        // Generate a random code (like a 10-char alphanumeric string) added on 15-05-2025
			$random_code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
			$this->request->post['payment_code'] = $random_code;
			
// 			Krishna Salve Changes - 09/07/2025
			// ✅ Append callback for email before passing to model
			$post_data = $this->request->post;
			$post_data['send_callback'] = [$this, 'sendPaymentMailToVendor'];

			$this->model_vendor_income->addAmount($post_data);
			
			// added on the 15-05-2025
			if (!empty($this->request->post['order_ids'])) {
				$this->model_vendor_income->markVendorSettlementsPaidByOrderIds($this->request->post['order_ids'],$random_code);
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

			$this->response->redirect($this->url->link('vendor/income', 'user_token=' . $this->session->data['user_token'] .$url, true));
		}

		$this->getForm();
	}
 	
	public function getList() {
				
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
		 	$filter_name = false;
		}
		
		if (isset($this->request->get['filter_vendor'])) {
			$filter_vendor = $this->request->get['filter_vendor'];
		} else {
			$filter_vendor = '';
		}
		
		if (isset($this->request->get['filter_date_added_from'])) {
			$filter_date_added_from = $this->request->get['filter_date_added_from'];
		} else {
		 	$filter_date_added_from = false;
		}

		if (isset($this->request->get['filter_date_added_to'])) {
			$filter_date_added_to = $this->request->get['filter_date_added_to'];
		} else {
		 	$filter_date_added_to = false;
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
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
			
		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}
		
		if (isset($this->request->get['filter_date_added_from'])) {
			$url .= '&filter_date_added_from=' . $this->request->get['filter_date_added_from'];
		}

		if (isset($this->request->get['filter_date_added_to	'])) {
			$url .= '&filter_date_added_to	=' . $this->request->get['filter_date_added_to	'];
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
			'href' => $this->url->link('vendor/income', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);
		
		$data['add']	=$this->url->link('vendor/income/add','&user_token='.$this->session->data['user_token'].$url,true);
		
		$data['incomes'] = array();

		$filter_data = array(
			
			'filter_vendor' => $filter_vendor,
			
			'filter_name'  => $filter_name,
			'filter_date_added_from'	=> $filter_date_added_from,
			'filter_date_added_to'	=> $filter_date_added_to,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		$this->load->model('vendor/vendor');
		$report_total = $this->model_vendor_income->getTotalIncome($filter_data);
		$reports = $this->model_vendor_income->getIncomes($filter_data);
	 	
		foreach($reports as $report){
				$taxamount ='0';
				$shipingamount =$this->model_vendor_income->getTotalShipping($filter_data,$report['vendor_id']);
			
			//added changes on 15-05-2025
			$sellers = $this->model_vendor_vendor->getVendor($report['vendor_id']);
			$carrier_data = $this->model_vendor_income->getVendorCompleteOrderData($report['vendor_id']);
			$paid_order_courier_data =$this->model_vendor_income->getVendorCompleteOrderDataWithPaidStatus($report['vendor_id']);
			
			//added the code on the 26-06-2025
			$rto_and_return_data = $this->model_vendor_income->getRTOAndReturnOrders($report['vendor_id']);
			$paid_rto_and_return_data =$this->model_vendor_income->getRTOAndReturnPaidOrders($report['vendor_id']);
			
			// Extract only 'total deduction' from the RTO & Return Delivered data
			$rto_and_reverse_courier_rate_total = 0;
			
			foreach ($rto_and_return_data as $row) {
				$rto_and_reverse_courier_rate_total += isset($row['total_deduction']) ? (float)$row['total_deduction'] : 0;
			}

			// for paid order data
			$paid_rto_and_reverse_courier_rate_total = 0;
			foreach ($paid_rto_and_return_data as $row) {
				$paid_rto_and_reverse_courier_rate_total += isset($row['total_deduction']) ? (float)$row['total_deduction'] : 0;
			}
			// ----------------------------------------------------------------------
            
            $courier_rate_total = 0;
			$product_courier_total = 0;

			foreach ($carrier_data as $row) {
				$courier_rate_total += isset($row['total_deduction']) ? (float)$row['total_deduction'] : 0;
				$product_courier_total += isset($row['product_courier_charges']) ? (float)$row['product_courier_charges'] : 0;
			}
			
			$paid_courier_rate_total = 0;
			$paid_product_courier_total = 0;

			foreach ($paid_order_courier_data as $row) {
				$paid_courier_rate_total += isset($row['total_deduction']) ? (float)$row['total_deduction'] : 0;
				$paid_product_courier_total += isset($row['product_courier_charges']) ? (float)$row['product_courier_charges'] : 0;
			}

			// combine all courier charges as total deduction updated on 30-05-2025
			$total_of_total_deduction = $rto_and_reverse_courier_rate_total+ $paid_rto_and_reverse_courier_rate_total + $courier_rate_total + $paid_courier_rate_total;
            //------------------------------------------------------------------------------------------------------------------------------------------------------------
            
    		// Total Amount 
			$total = $this->model_vendor_income->getTotal($filter_data,$report['vendor_id']);

    		// Seller Amount
			$totalcommission = $this->model_vendor_income->getTotalCommission($filter_data,$report['vendor_id']);

    		// Admin Amount
			$totalamount = $total-$totalcommission+$taxamount; //+$shipingamount;

            //added changes on 15-05-2025
			$totalamount = $totalamount - $total_of_total_deduction;
			
			// added the logic to add the product courier charges into the settlement on 15-05-2025
			$total_of_product_courier_total = $product_courier_total + $paid_product_courier_total;
			$totalamount += $total_of_product_courier_total; 
			$total += $total_of_product_courier_total;
            // ---------------------------------------------------------
        
    		// Pay Seller Amount
			$payamount = $this->model_vendor_income->getAmount($report['vendor_id']);
			
    		// Remaining Amount
			$remaining_amounts = $totalamount-$payamount;
		
			if ($remaining_amounts) {
					$remaining_amount = $this->currency->format($remaining_amounts, $this->config->get('config_currency'), $this->config->get('currency_value'));
			} else {
				$remaining_amount = '';
			}
			
			
            //$sellers = $this->model_vendor_vendor->getVendor($report['vendor_id']);
				
				
			if(isset($sellers['vname'])){
				$sellername = $sellers['vname'];
			} else {
				$sellername ='';
			}

			
			$data['incomes'][] = array(
				'vendor_id'			=> $report['vendor_id'],
				// 'tmdshippingcost'	=> $this->currency->format($shipingamount, $this->config->get('config_currency'), $this->config->get('currency_value')),	
				'sellername'		=> $sellername,				
				'date_added'		=> $report['date_added'],				
				'display_name'		=> $report['display_name'],
				'total'				=> $this->currency->format($total+$taxamount, $this->config->get('config_currency'), $this->config->get('currency_value')),
				'totalcommission'	=> $this->currency->format($totalcommission, $this->config->get('config_currency'), $this->config->get('currency_value')),
				'totalamount'		=> $this->currency->format($totalamount, $this->config->get('config_currency'), $this->config->get('currency_value')),
				'payamount'			=> $this->currency->format($payamount, $this->config->get('config_currency'), $this->config->get('currency_value')),
				
				'remaining_amount'	=> $remaining_amount ?  $remaining_amount  : 0,
				'payment'       	=> $this->url->link('vendor/income/add', 'user_token=' . $this->session->data['user_token'] .'&vendor_id=' . $report['vendor_id'] . $url, true),
				'courier_rate'    => $this->currency->format($total_of_total_deduction,  $this->config->get('config_currency'), $this->config->get('currency_value'))
			);
		}

		   		
		$data['heading_title']          = $this->language->get('heading_title');
		$data['text_list']           	= $this->language->get('text_list');
		$data['text_no_results'] 		= $this->language->get('text_no_results');
		$data['text_confirm']			= $this->language->get('text_confirm');
		$data['text_none'] 				= $this->language->get('text_none');
	 	$data['text_enable']            = $this->language->get('text_enable');
		$data['text_disable']           = $this->language->get('text_disable');
		$data['entry_from']             = $this->language->get('entry_from');
		$data['entry_to']               = $this->language->get('entry_to');
		$data['entry_t_amount']         = $this->language->get('entry_t_amount');
		$data['entry_s_amount']         = $this->language->get('entry_s_amount');
		$data['entry_a_amount']         = $this->language->get('entry_a_amount');
		$data['text_select']            = $this->language->get('text_select');
		$data['column_seller']		    = $this->language->get('column_seller');
		$data['column_tamount']			= $this->language->get('column_tamount');
		$data['column_samount']			= $this->language->get('column_samount');
		$data['column_admin_amount']	= $this->language->get('column_admin_amount');
		$data['column_paid']			= $this->language->get('column_paid');
		$data['column_remaining']		= $this->language->get('column_remaining');
		$data['column_date']			= $this->language->get('column_date');
		$data['column_action']			= $this->language->get('column_action');
		$data['button_delete']          = $this->language->get('button_delete');
		$data['button_filter']          = $this->language->get('button_filter');
		$data['button_pay']             = $this->language->get('button_pay');
		$data['text_confirm']           = $this->language->get('text_confirm');
		$data['name']                   = $this->language->get('name');
		$data['user_token']                  = $this->session->data['user_token'];
		
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
		
		
		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}
		

		if (isset($this->request->get['filter_date_added_from'])) {
			$url .= '&filter_date_added_from=' . $this->request->get['filter_date_added_from'];
		}

		if (isset($this->request->get['filter_date_added_to	'])) {
			$url .= '&filter_date_added_to=' . $this->request->get['filter_date_added_to'];
		}
		
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
	 
		$data['sort_seller']    	= $this->url->link('vendor/income', 'user_token=' . $this->session->data['user_token'] . '&sort=seller' . $url, true);
		$data['sort_tamount']  		= $this->url->link('vendor/income', 'user_token=' . $this->session->data['user_token'] . '&sort=tamount' . $url, true);
		$data['sort_samount']  		= $this->url->link('vendor/income', 'user_token=' . $this->session->data['user_token'] . '&sort=samount' . $url, true);
		$data['sort_admin_amount']  = $this->url->link('vendor/income', 'user_token=' . $this->session->data['user_token'] . '&sort=admin_amount' . $url, true);
		
		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}
		
		if (isset($this->request->get['filter_date_added_from'])) {
			$url .= '&filter_date_added_from=' . $this->request->get['filter_date_added_from'];
		}

		if (isset($this->request->get['filter_date_added_to'])) {
			$url .= '&filter_date_added_to=' . $this->request->get['filter_date_added_to'];
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
		$pagination->url   	= $this->url->link('vendor/income', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($report_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($report_total - $this->config->get('config_limit_admin'))) ? $report_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $report_total, ceil($report_total / $this->config->get('config_limit_admin')));
		
		$data['filter_name']	= $filter_name;
	
		$data['filter_vendor']  = $filter_vendor;
		
		$data['filter_date_added_from']	= $filter_date_added_from;
		$data['filter_date_added_to']   = $filter_date_added_to;

		$this->load->model('vendor/vendor');
		if(isset($data['filter_name'])) {
			$vendor_info = $this->model_vendor_vendor->getVendor($data['filter_name']);
		}
	
		if(isset($vendor_info['vname'])) {
			$data['sellernme'] = $vendor_info['vname'];
		} else {
			$data['sellernme'] ='';
		}
		
		$data['sort']						= $sort;
		$data['order']						= $order;
						
		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('vendor/income_list', $data));
	}
	
	protected function getForm() {
		$data['payment_title']  = $this->language->get('payment_title');
		$data['text_form'] 	    = $this->language->get('text_form');
		$data['text_enabled'] 	= $this->language->get('text_enabled');
		$data['text_disabled'] 	= $this->language->get('text_disabled');
		$data['text_default'] 	= $this->language->get('text_default');
		$data['text_percent'] 	= $this->language->get('text_percent');
		$data['text_amount'] 	= $this->language->get('text_amount');
		$data['text_select'] 	= $this->language->get('text_select');
		$data['text_none'] 		= $this->language->get('text_none');
		$data['text_enable'] 	= $this->language->get('text_enable');
		$data['text_disable'] 	= $this->language->get('text_disable');
		$data['text_bank']  	= $this->language->get('text_bank');
		$data['text_paypal']  	= $this->language->get('text_paypal');
		
		$data['entry_seller'] 	= $this->language->get('entry_seller');
		$data['entry_amount'] 	= $this->language->get('entry_amount');
		$data['entry_payment'] 	= $this->language->get('entry_payment');
		$data['entry_comment'] 	= $this->language->get('entry_comment');
		$data['entry_bankname']  = $this->language->get('entry_bankname');
		$data['entry_bnumber']  = $this->language->get('entry_bnumber');
		$data['entry_swiftcode'] = $this->language->get('entry_swiftcode');
		$data['entry_aname']  	= $this->language->get('entry_aname');
		$data['entry_anumber']  = $this->language->get('entry_anumber');
		$data['entry_Emailid']  = $this->language->get('entry_Emailid');
		$data['entry_method']  	= $this->language->get('entry_method');
		

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['remaining_amount'])) {
			$data['error_remaining_amount'] = $this->error['remaining_amount'];
		} else {
			$data['error_remaining_amount'] = '';
		}

		if (isset($this->error['amount'])) {
			$data['error_amount'] = $this->error['amount'];
		} else {
			$data['error_amount'] = '';
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
			'text' => $this->language->get('payment_title'),
			'href' => $this->url->link('vendor/income', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		
		$data['action'] = $this->url->link('vendor/income/add', 'user_token=' . $this->session->data['user_token'] .'&vendor_id=' . $this->request->get['vendor_id'] . $url, true);
		
		if (isset($this->request->get['vendor_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$pay_info=$this->model_vendor_income->getPay($this->request->get['vendor_id']);
			
		}
			
		if (isset($this->request->get['vendor_id'])) {
			$data['vendor_id'] = $this->request->get['vendor_id'];
		
		} elseif (isset($pay_info['vendor_id'])){
			$data['vendor_id'] = $pay_info['vendor_id'];
		} else {
			$data['vendor_id'] = '';
		}

		if(isset($this->request->post['vendor_id'])){	
			$this->load->model('vendor/vendor');
			$vendor_info=$this->model_vendor_vendor->getVendor($pay_info['vendor_id']);
			$data['vendor']=$vendor_info['firstname'];
		} else {
			$data['vendor']='';
		}

        // commented on 08/07/2025
        // 		if (isset($this->request->post['comment'])) {
        // 			$data['comment'] = $this->request->post['comment'];
        // 		} elseif (isset($pay_info['comment'])){
        // 			$data['comment'] = $pay_info['comment'];
        // 		} else {
        // 			$data['comment'] = '';
        // 		}

		if (isset($this->request->post['payment_method'])) {
			$data['payment_method'] = $this->request->post['payment_method'];
		} elseif (!empty($pay_info)) {
			$data['payment_method'] = $pay_info['payment_method'];
		} else {
			$data['payment_method'] = 'paypal';
		}

		$this->load->model('vendor/vendor');
		$vendor_infos = $this->model_vendor_vendor->getVendor($this->request->get['vendor_id']);
		
		
		$data['paypal'] 				= $vendor_infos['paypal'];
		$data['bank_name'] 				= $vendor_infos['bank_name'];
		$data['bank_branch_number'] 	= $vendor_infos['bank_branch_number'];
		$data['bank_swift_code'] 		= $vendor_infos['bank_swift_code'];
		$data['bank_account_name'] 		= $vendor_infos['bank_account_name'];
		$data['bank_account_number'] 	= $vendor_infos['bank_account_number'];
		$data['vendor'] 				= $vendor_infos['vname'];
		

        //  added logic for the display the order list into the payment form as well as minus the corier rate from the vendor amount
		$complete_orders_data = $this->model_vendor_income->getVendorCompleteOrderData($this->request->get['vendor_id']);
		$paid_order_courier_rate_data =$this->model_vendor_income->getVendorCompleteOrderDataWithPaidStatus($this->request->get['vendor_id']);
		
		//added the code on the 30-05-2025
		$rto_and_return_data = $this->model_vendor_income->getRTOAndReturnOrders($this->request->get['vendor_id']);
		$paid_rto_and_return_data =$this->model_vendor_income->getRTOAndReturnPaidOrders($this->request->get['vendor_id']);
		
		// Extract only 'total deduction' from the RTO & Return Delivered data
		$rto_and_reverse_courier_rate_total = 0;

		foreach ($rto_and_return_data as $row) {
			$rto_and_reverse_courier_rate_total += isset($row['total_deduction']) ? (float)$row['total_deduction'] : 0;
		}

		// for paid order data
		$paid_rto_and_reverse_courier_rate_total = 0;
		foreach ($paid_rto_and_return_data as $row) {
			$paid_rto_and_reverse_courier_rate_total += isset($row['total_deduction']) ? (float)$row['total_deduction'] : 0;
		}
		// ----------------------------------------------------------------------
		
		$courier_rate_total = 0;
		$product_courier_total = 0;

		foreach ($complete_orders_data as $row) {
			$courier_rate_total += isset($row['total_deduction']) ? (float)$row['total_deduction'] : 0;
			$product_courier_total += isset($row['product_courier_charges']) ? (float)$row['product_courier_charges'] : 0;
		}

		// Extract only 'total deduction' from the data of paid order_ids on 15-05-2025
        // $paid_courier_rates = array_column($courier_rate_data, 'total_deduction');
        
        // added new logic to deduct the product courier charges
        $paid_product_courier_total = 0;
		$paid_courier_rate_total = 0; // Initialize the total

		foreach ($paid_order_courier_rate_data as $row) {
    		$paid_courier_rate_total += isset($row['total_deduction']) ? (float)$row['total_deduction'] : 0;
			$paid_product_courier_total += isset($row['product_courier_charges']) ? (float)$row['product_courier_charges'] : 0;
		}

		// combine all courier charges as total deduction  15-05-2025
    	$total_of_total_deduction = $courier_rate_total + $paid_rto_and_reverse_courier_rate_total + $rto_and_reverse_courier_rate_total + $paid_courier_rate_total;
		
		
		$data['total'] = $this->model_vendor_income->getVendorTotal($this->request->get['vendor_id']);

		$data['totalcommission'] = $this->model_vendor_income->getTotalAmount($this->request->get['vendor_id']);
		
		$data['totalamount'] = $data['total']-$data['totalcommission'];
		
		//added logic to minus the corier rate on  15-05-2025
		$data['totalamount'] = $data['totalamount'] - $total_of_total_deduction;
        
        
        $total_of_product_courier_total = $product_courier_total + $paid_product_courier_total;        
        
        
		// added the logic here to add the product courier charges on 15-05-2025
		$data['totalamount'] = $data['totalamount'] + $total_of_product_courier_total;
		
		$data['total']  = 	$data['total'] + $total_of_product_courier_total; 

		$data['payamount'] = $this->model_vendor_income->getAmount($this->request->get['vendor_id']);

		$filter_data = array();
		
		$shipingamount =$this->model_vendor_income->getTotalShipping($filter_data,$this->request->get['vendor_id']);
			
		$data['remaining_amount'] = $data['totalamount'] - $data['payamount'] + $shipingamount;
		
		$data['cancel'] = $this->url->link('vendor/income', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['user_token'] = $this->session->data['user_token'];	
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

        // Fetch each order's contribution to vendor total on 15-05-2025
		$data['orders'][] = array();

		// move out from the top to here following log
		if (isset($this->request->post['amount'])) {
			$data['amount'] = $this->request->post['amount'];
		} elseif (isset($data['remaining_amount'])) {
			$data['amount'] = $data['remaining_amount'];
		} else {
			$data['amount'] = '';
		}
		
		// added this line into the code 01-05-2025
		$complete_orders_data = array_merge($complete_orders_data, $rto_and_return_data);
		
		foreach ($complete_orders_data as $order) {
				$data['orders'][] = array(
				'order_id'    => $order['order_id'],
				'price' =>$this->currency->format($order['total'], $this->config->get('config_currency'), $this->config->get('currency_value')),
				'commission' => 0, // * (6 / 100),
				'product_name' => $order['name'],
				'quantity' => $order['quantity'],
				'courier_rate' => $order['courier_rate'] ? $order['courier_rate'] : 0,
				'rto_charges' => $order['rto_charges'] ? $order['rto_charges'] : 0,
				'reverse_courier_charges' => $order['reverse_courier_charges'] ? $order['reverse_courier_charges'] : 0,
				'net_amount' => $this->currency->format($order['net_settlement_amount'], $this->config->get('config_currency'), $this->config->get('currency_value')),
				'total_deduction' => $order['total_deduction'] ? $order['total_deduction'] : 0 ,
				'payment_status' => $order['payment_status'],
				'product_courier_charges' => $order['product_courier_charges'] ? $order['product_courier_charges'] : 0
			);
		}
        //---------------------------------------------------------------------------
		
		$this->response->setOutput($this->load->view('vendor/payment_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'vendor/income')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if ($this->request->post['remaining_amount']< $this->request->post['amount']) {
			$this->error['amount'] = $this->language->get('error_amount');
		}
		
		return !$this->error;
	}
	 		
	public function autocomplete(){
		if (isset($this->request->get['filter_name'])) {
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
				'start' => ($page - 1) * $this->config->get('config_limit_admin'),
				'limit' => $this->config->get('config_limit_admin')
			);
			$accounts = $this->model_vendor_vendor->getVendors($filter_data);
			foreach ($accounts as $account) {

				$json[] = array(
					'vendor_id'  => $account['vendor_id'],
					'firstname'   => strip_tags(html_entity_decode($account['firstname'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}
		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['firstname'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function getPaymentMethod(){
		$this->load->language('vendor/income');
		$this->load->model('vendor/income');
		$this->load->model('vendor/vendor');
		
		$data['entry_payment'] 	= $this->language->get('entry_payment');
		$data['text_bank']  	= $this->language->get('text_bank');
		$data['text_paypal']  	= $this->language->get('text_paypal');
		$data['entry_bankname']  = $this->language->get('entry_bankname');
		$data['entry_bnumber']  = $this->language->get('entry_bnumber');
		$data['entry_swiftcode'] = $this->language->get('entry_swiftcode');
		$data['entry_aname']  	= $this->language->get('entry_aname');
		$data['entry_anumber']  = $this->language->get('entry_anumber');
		$data['entry_Emailid']  = $this->language->get('entry_Emailid');
		$data['entry_method']  	= $this->language->get('entry_method');
		
		
		$vendor_info=$this->model_vendor_vendor->getVendor($this->request->get['vendor_id']);

		if (isset($this->request->post['payment_method'])) {
			$data['payment_method'] = $this->request->post['payment_method'];
		} elseif (!empty($vendor_info['payment_method'])) {
			$data['payment_method'] = $vendor_info['payment_method'];
		} else {
			$data['payment_method'] = 'paypal';
		}

		if (isset($this->request->post['bank_name'])) {
			$data['bank_name'] = $this->request->post['bank_name'];
		} elseif (!empty($vendor_info['bank_name'])) {
			$data['bank_name'] = $vendor_info['bank_name'];
		} else {
			$data['bank_name'] = '';
		}
		
		$this->response->setOutput($this->load->view('vendor/bank_detail', $data));
	}	
	
	public function sendPaymentMailToVendor($data) {
		$this->load->language('vendor/income');

		$mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

		$subject = sprintf($this->language->get('text_subject'), $data['pay_id']);

		$data['text_greeting']  = $this->language->get('text_greeting') . ' ' . $data['firstname'] . ',';
		$data['text_start'] = sprintf($this->language->get('text_start'), $data['amount'], $data['payment_method']);

		// Replace placeholders
		$info = $this->language->get('text_info');
		$info = str_replace('[Transaction ID]', $data['pay_id'], $info);
		$info = str_replace('[date_added]', $data['date_added'], $info);
		$data['info'] = $info;

		$data['text_review'] = $this->language->get('text_review') . '<br>';
		$data['text_thanks'] = $this->language->get('text_thanks') . '<br>';
		$data['text_encourage'] = $this->language->get('text_encourage') . '<br>';
		$data['text_regards'] = $this->language->get('text_regards');

		// Render the Twig template
		$html_message = $this->load->view('vendor/income_mail_template', $data);  // Render the email body

		$mail->setTo($data['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender($this->config->get('config_name'));
		$mail->setSubject($subject);
		$mail->setHtml($html_message);
		$mail->send();
	}
	
}
?>