<?php
class ControllerVendorCommission extends Controller
{
	private $error = array();

	public function index()
	{
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}
		$this->load->language('vendor/commission');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/commission');
		$this->load->model('vendor/product');
		$this->load->model('vendor/order');

		$this->getList();
	}

	protected function getList()
	{
	    
	   // added by sagar 14-02-2025 
	    $this->load->model('tool/image');
		$this->load->model('vendor/vendor');
		$this->load->model('vendor/commission');
// 		end

        // added changes on the 28-04-2025
		if (isset($this->request->get['filter_order_id'])) {
			$filter_order_id = $this->request->get['filter_order_id'];
		} else {
			$filter_order_id = '';
		}
		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}
		// ------------===============-------------
        // --------------------------------------------------
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
		
		$data['cancel'] = $this->url->link('vendor/dashboard');

		$url = '';
        // added changes on 28-04-2025---------------------------
        // added by shubham -----------=========-
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . urlencode(html_entity_decode($this->request->get['filter_order_id'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . urlencode(html_entity_decode($this->request->get['filter_status'], ENT_QUOTES, 'UTF-8'));
		}
		
		// =========---------------===================-
        // -------------------------------------------------------------

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
			'href' => $this->url->link('vendor/commission')
		);

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}
		
		// added by sagar 14-02-2025 
		$filter_order_id = isset($this->request->get['filter_order_id']) ? $this->request->get['filter_order_id'] : '';
		$filter_product_name = isset($this->request->get['filter_product_name']) ? $this->request->get['filter_product_name'] : '';
		$filter_date_added = isset($this->request->get['filter_date_added']) ? $this->request->get['filter_date_added'] : '';
		$filter_status = isset($this->request->get['filter_status']) ? $this->request->get['filter_status'] : '';
		$page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
		// end


		$data['commissions'] = array();

		$filter_data = array(
			'vendor_id' => $this->vendor->getId(),
			'sort'      => $sort,
			'order'     => $order,
// 			added by sagar on 14-02-2025 
//             'filter_order_id' => $filter_order_id,
// 'filter_product_name' => $filter_product_name,
// 'filter_date_added' => $filter_date_added,
// 'filter_status' => $filter_status,
// added changes on the 28-04-2025
// added by shubham at 28-04-2025
        'filter_order_id' => $filter_order_id,
        'filter_status' => $filter_status,
        'filter_payment_status' => isset($this->request->get['filter_payment_status']) ? $this->request->get['filter_payment_status'] : '',

        'start' => ($page - 1) * $this->config->get('config_limit_admin'),
        'limit' => $this->config->get('config_limit_admin')
		);

		$this->load->model('vendor/vendor');
		$this->load->model('vendor/commission');

		$commission_total = $this->model_vendor_commission->getTotalCommissionReport($filter_data);
		$reports = $this->model_vendor_commission->getCommissionReports($filter_data);

		foreach ($reports as $report) {
			$sellers = $this->model_vendor_vendor->getVendor($report['vendor_id']);
			if (isset($sellers['firstname'])) {
				$sellername = $sellers['firstname'];
			} else {
				$sellername = '';
			}

			$currency_info = $this->model_vendor_commission->getOrderCurrency($report['order_id']);

			if (isset($currency_info['currency_code'])) {
				$currency = $currency_info['currency_code'];
			} else {
				$currency = $this->config->get('config_currency');
			}

			if (!empty($report['tax'])) {
				/*############ 13 02 2021 update ############*/
				$price1 = $report['total'] + $report['tax'] * $report['quantity'];
				$price = $this->currency->format($price1, $currency);
			} else {
				$price = $this->currency->format($report['price'], $currency);
			}

			// foreach ($totals as $total) {
			// 	$data['totals'][] = array(
			// 		'title' => $total['title'],
			// 		'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
			// 	);
			// }
// 			commented on the date 15-05-2025
// 			$totals = $this->model_vendor_product->getVendorOrderTotals($report['order_id']);
// 			$data['total_value'] = '';

// 			foreach ($totals as $total) {
// 				if ($total['code'] === 'total') {  // Check if the code is 'total'
// 					$data['total_value'] = $this->currency->format($total['value'], $this->session->data['currency']);
// 					// $data['total_value'] = $total['value'];
// 					break;  // Stop the loop once the total is found
// 				}
// 			}




			$vendor_id = $this->model_vendor_commission->getVendorId($report['order_id']);
    	    // updated the logic on the 15-05-2025
    		$rates = $this->model_vendor_commission->getCourierRateByOrderId($report['order_id']);
			$product_courier_charges = $this->model_vendor_commission->getCourierChargesByOrderId($report['order_id']);
			$courier_rate = $rates['courier_rate'];
			$rto_charges = $rates['rto_charge'];
			$reverse_courier_charges = $rates['reverse_courier_charge'];
			$payment_status =  $this->model_vendor_commission->getPaymentStatusByOrderId($report['order_id']);
			
			$payment_details = $this->model_vendor_commission->getVendorPaymentDetails($report['order_id']);
			$reference_number = isset($payment_details['reference_number']) ? $payment_details['reference_number'] : 'N/A';
			$payment_date = isset($payment_details['payment_date']) ? $payment_details['payment_date'] : 'N/A';
			$carrier_id = isset($rates['carrier_id']) ? $rates['carrier_id'] : 0;

    		$status_name = $this->model_vendor_commission->getStatusById($report['order_status_id']);
			// $totalDeductionAmount = $report['commissionper'] + 30 + $courier_rate;   // static fix rates 
			$totalDeductionAmount = $report['commissionper'] + $report['commissionfix'] + $rto_charges + $reverse_courier_charges +  $courier_rate;   // static fix rates 
			
			// added the code to get the hsn_code and show on 31-05-2025
			$hsn_code =$this->model_vendor_commission->getHSNCodeByProductId($report['product_id']);
			
			// Check if RTO or reverse courier charges exist (means RTO/return case) update the code on 29-05-2025
			$is_return_case = ($rto_charges > 0 || $reverse_courier_charges > 0);

			// Net settlement calculation
			if ($is_return_case) {
				// If it's a return, treat net settlement as negative
				$netSettlementAmount = -1 * $totalDeductionAmount;
			} else {
				$netSettlementAmount = ($price1 + $product_courier_charges) - $totalDeductionAmount;
			}
			
            // $netSettlementAmount = ($price1 +$product_courier_charges) - $totalDeductionAmount;
            //-----------------------------------------------------------------
            
            
		
			// Continue with the rest of your code, now $new_previous_deduction is always defined
			$data['commissions'][] = array(
				'order_id' => $report['order_id'],
				'sellername'	 => $sellername,
				'name'			 => $report['name'],
				'model'			 => $report['model'],
				'quantity'		 => $report['quantity'],
				/* 07-03-2019 update code */
				'price'			 =>  $this->currency->format($price1, $currency),
				'totalcommission' => $this->currency->format($report['totalcommission'], $currency),
				/* 07-03-2019 update code */
				'commissionper'	 => $report['commissionper'] . '%',
				'commissionfix'	 => $report['commissionfix'],
				'date_added'	 => $report['date_added'],
				'courier_rates'    => $this->currency->format($courier_rate, $currency),
				'order_status_id' => $status_name,
				'total_deduction' => $this->currency->format(round($totalDeductionAmount), $currency),
				'net_settlement_amount' =>  $this->currency->format(round($netSettlementAmount), $currency),
                'rto_charges' => $this->currency->format($rto_charges,$currency),
				'reverse_courier_charges' =>  $this->currency->format($reverse_courier_charges, $currency),
				'payment_status' => ucfirst($payment_status),
				'product_courier_charges' =>  $this->currency->format($product_courier_charges, $currency),
				'reference_number' => $reference_number,
				'payment_date' => $payment_date,
				'hsn_code' => $hsn_code

			);

            //if($status_name == 'Complete'){
				// Check if an entry for this order_id already exists
			$exists = $this->model_vendor_commission->checkOrderExists($report['order_id']);
			if (!$exists) {
				$this->model_vendor_commission->insertSettlementValues($report['order_id'], $vendor_id, $courier_rate, $carrier_id, $reverse_courier_charges, $rto_charges, $totalDeductionAmount, $netSettlementAmount,$product_courier_charges);
			}else{
				$this->model_vendor_commission->updateSettlementValues($report['order_id'], $vendor_id, $courier_rate, $carrier_id, $reverse_courier_charges, $rto_charges, $totalDeductionAmount, $netSettlementAmount,$product_courier_charges);
			} 
			

			// $this->model_vendor_order->updateDeductionCharges($report['order_id'], $reverse_courier_charges, $rto_charges, $totalDeductionAmount, $payableAmount, $new_previous_deduction);



		}

		$data['heading_title'] 		= $this->language->get('heading_title');

		$data['text_list'] 			= $this->language->get('text_list');
		$data['text_no_results'] 	= $this->language->get('text_no_results');
		$data['text_confirm'] 		= $this->language->get('text_confirm');
		$data['text_enabled'] 		= $this->language->get('text_enabled');
		$data['text_disabled'] 		= $this->language->get('text_disabled');

		$data['column_vendor']  	= $this->language->get('column_vendor');
		$data['column_name']  		= $this->language->get('column_name');
		$data['column_model']  		= $this->language->get('column_model');
		$data['column_quantity']    = $this->language->get('column_quantity');
		$data['column_price']  		= $this->language->get('column_price');
		$data['column_commission']  = $this->language->get('column_commission');
		$data['column_commissionfixed'] = $this->language->get('column_commissionfixed');
		$data['column_commissiontotal'] = $this->language->get('column_commissiontotal');
		$data['column_date']  		= $this->language->get('column_date');

		// added later 
		$data['column_courier_charges'] = $this->language->get('column_courier_charges');

		$data['entry_product'] 		= $this->language->get('entry_product');
		$data['entry_author'] 		= $this->language->get('entry_author');
		$data['entry_rating'] 		= $this->language->get('entry_rating');
		$data['entry_status'] 		= $this->language->get('entry_status');
		$data['entry_date_added'] 	= $this->language->get('entry_date_added');


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
        // added on the 28-04-2025----------------------------------------
        
	
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . urlencode(html_entity_decode($this->request->get['filter_order_id'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . urlencode(html_entity_decode($this->request->get['filter_status'], ENT_QUOTES, 'UTF-8'));
		}
		
		// ----=================-------
        // ---------------------------------------------------------------

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_vendor']		 = $this->url->link('vendor/commission', '&sort=vendor' . $url, true);
		$data['sort_name']   		 = $this->url->link('vendor/commission', '&sort=name' . $url, true);
		$data['sort_model']			 = $this->url->link('vendor/commission', '&sort=model' . $url, true);
		$data['sort_quantity']   	 = $this->url->link('vendor/commission', '&sort=quantity' . $url, true);
		$data['sort_price']   		 = $this->url->link('vendor/commission', '&sort=price' . $url, true);
		$data['sort_commission']   	 = $this->url->link('vendor/commission', '&sort=commission' . $url, true);
		$data['sort_commissionfixed'] = $this->url->link('vendor/commission', '&sort=commissionfixed' . $url, true);
		$data['sort_commissiontotal'] = $this->url->link('vendor/commission', '&sort=commissiontotal' . $url, true);
		$data['sort_date']   	   	 = $this->url->link('vendor/commission', '&sort=date' . $url, true);
        // added by 28-04-2025
		$data['filter_order_id'] = $filter_order_id;
        $data['filter_status'] = $filter_status;
        $data['filter_status'] = $filter_status;
		 $data['filter_payment_status'] = $filter_payment_status;

		// --------------=============
		// added later 

		$data['sort_courier_charges'] = $this->url->link('vendor/commission', '&sort=courier_charges' . $url, true);




		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination 	   = new Pagination();
		$pagination->total = $commission_total;
		$pagination->page  = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url   = $this->url->link('vendor/commission', $url . 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($commission_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($commission_total - $this->config->get('config_limit_admin'))) ? $commission_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $commission_total, ceil($commission_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer'] = $this->load->controller('vendor/footer');

		$this->response->setOutput($this->load->view('vendor/commission', $data));
	}
}
