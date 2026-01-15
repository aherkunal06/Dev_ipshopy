<?php

class ControllerVendorCommissionreport  extends Controller {
	private $error = array();
	public function index() {
		$this->load->language('vendor/commission_report');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('vendor/commission_report');
		$data['download_excel_url'] = $this->url->link('vendor/commission_report/downloadSelectedExcel', '', true);
		$this->getList();
	}
 
	public function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
		 	$filter_name = '';
		}
		/* 11 02 2020 */
		if (isset($this->request->get['filter_vendor'])) {
			$filter_vendor = $this->request->get['filter_vendor'];
		} else {
			$filter_vendor = '';
		}
		/* 11 02 2020 */
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
			$order = 'DESC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		// added account changes on 27-05-2025
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

		if (isset($this->request->get['filter_payment_status'])) {
			$filter_payment_status = $this->request->get['filter_payment_status'];
		} else {
			$filter_payment_status = '';
		}
		
		// -=================-----------------
		
		$url = '';

		// added account changes on 27-05-2025
		if(isset($this->request->get['filter_order_id'])){
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if(isset($this->request->get['filter_status'])){
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_payment_status'])) {
			$url .= '&filter_payment_status=' . $this->request->get['filter_payment_status'];
		}
		
		// ===========================
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		/* 11 02 2020 */
		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}
		/* 11 02 2020 */
		
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
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/commission_report', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);
		
		$data['commissionreports'] = array();

		$filter_data = array(
			/* 11 02 2020 */
			'filter_vendor' => $filter_vendor,
			/* 11 02 2020 */
			'filter_name'  => $filter_name,
			'filter_from'    => $filter_from,
			'filter_to'      => $filter_to,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin'),
// 			// added account changes on 27-05-2025
			'filter_order_id' =>$filter_order_id,
			'filter_status' =>$filter_status,
			'filter_payment_status' => $filter_payment_status,  // <-- added here
			// =====----------==========
		);
		
		$this->load->model('vendor/vendor');
		$report_total = $this->model_vendor_commission_report->getTotalCommissionReport($filter_data);
		$reports = $this->model_vendor_commission_report->getCommissionReports($filter_data);
	
	 	$commi_total=0;
		foreach($reports as $report){
			
			$sellers = $this->model_vendor_vendor->getVendor($report['vendor_id']);
			/* 05 02 2020 update vname firstname pe */
			if(isset($sellers['vname'])){
				$sellername = $sellers['vname'];
			} else {
				$sellername ='';
			}
			
		 	$currency_info = $this->model_vendor_commission_report->getOrderCurrency($report['order_id']);
		
			if(isset($currency_info['currency_code'])) {
				$currency = $currency_info['currency_code'];
			} else {
				$currency=$this->config->get('config_currency');
			}
			
			if(!empty($report['tax'])){
				/*############ 13 02 2021 update code ############*/		
				$price1 = $report['total'] + $report['tax']*$report['quantity'];
				$price = $this->currency->format($price1,$currency);
			} else {
				$price = $this->currency->format($report['price'],$currency);
			}

            //added account changes on 27-05-2025 ---------------------------------------------------------------


			$vendor_id = $this->model_vendor_commission_report->getVendorId($report['order_id']);
			$rates = $this->model_vendor_commission_report->getCourierRateByOrderId($report['order_id']);
			$product_courier_charges = $this->model_vendor_commission_report->getCourierChargesByOrderId($report['order_id']);
// 			// $courier_rate = $rates['courier_rate'] + $productPriceFivePercent;
			$courier_rate = $rates['courier_rate'];
			$rto_charges = $rates['rto_charge'];
			$reverse_courier_charges = $rates['reverse_courier_charge'];
			// added the chages to show the courier name on 04-06-2025
			$courier_name = $rates['courier_name'];
            //---------------------------------------------------------------
			$carrier_id = isset($rates['carrier_id']) ? $rates['carrier_id'] : 0;

			$payment_details = $this->model_vendor_commission_report->getVendorPaymentDetails($report['order_id']);
			$reference_number = isset($payment_details['reference_number']) ? $payment_details['reference_number'] : 'N/A';
			$payment_date = isset($payment_details['payment_date']) ? $payment_details['payment_date'] : 'N/A';


			$status_name = $this->model_vendor_commission_report->getStatusById($report['order_status_id']);
			$totalDeductionAmount = $report['commissionper'] + $report['commissionfix'] + $rto_charges + $reverse_courier_charges +  $courier_rate;   
			
			// added account changes on 27-05-2025
			$payment_status =  $this->model_vendor_commission_report->getPaymentStatusByOrderId($report['order_id']);

			// added the code to get the hsn_code and show on 31-05-2025
			$hsn_code =$this->model_vendor_commission_report->getHSNCodeByProductId($report['product_id']);
			
			// Check if RTO or reverse courier charges exist (means RTO/return case) update the code on 29-05-2025
			$is_return_case = ($rto_charges > 0 || $reverse_courier_charges > 0);

			// Net settlement calculation
			if ($is_return_case) {
				// If it's a return, treat net settlement as negative
				$netSettlementAmount = -1 * abs($totalDeductionAmount);
			} else {
				$netSettlementAmount = ($price1 + $product_courier_charges) - $totalDeductionAmount;
			}
			//------------------------------------------------------------------------------------------
            // 			$netSettlementAmount = $price1 -  $totalDeductionAmount;
            // 			$netSettlementAmount += $product_courier_charges;

			$data['commissionreports'][] = array(
				'name'			=>$report['name'],
				'model'			=>$report['model'],
				'quantity'		=>$report['quantity'],					
				'sellername'	=>$sellername,
				'totalcommission'=> $this->currency->format($report['totalcommission'],$currency),
				'date_added'	=>$report['date_added'],
				'commissionper'	=>$report['commissionper'],
				'commissionfix'	=> $report['commissionfix'],
				// added account changes on 27-05-2025 ------------------------
				'price'			 => $this->currency->format(round($price1),$currency),
				'order_id'=>$report['order_id'],
				'status' => $status_name,
				'total_deduction' => $this->currency->format(round($totalDeductionAmount),$currency),
				'courier_rate' => $this->currency->format(round($courier_rate), $currency),
				'net_settlement_amount' => $this->currency->format(round($netSettlementAmount), $currency),
				'rto_charges' => $this->currency->format($rto_charges, $currency),
				'reverse_courier_charges'=> $this->currency->format($reverse_courier_charges, $currency),
				'payment_status' => ucfirst($payment_status),
				'product_courier_charges' => $this->currency->format($product_courier_charges, $currency),
				'reference_number' => $reference_number,
				'payment_date' => $payment_date,
				'hsn_code' => $hsn_code,
				'courier_name' =>$courier_name
				// ----------------------------------------------------
			);
			
			//  added account changes on 27-05-2025 ------------------------
			$exists = $this->model_vendor_commission_report->checkOrderExists($report['order_id']);
			if (!$exists) {
				$this->model_vendor_commission_report->insertSettlementValues($report['order_id'], $vendor_id, $courier_rate, $carrier_id, $reverse_courier_charges, $rto_charges, $totalDeductionAmount, $netSettlementAmount,$product_courier_charges);
			} 
            else{
				$this->model_vendor_commission_report->updateSettlementValues($report['order_id'], $vendor_id, $courier_rate, $carrier_id, $reverse_courier_charges, $rto_charges, $totalDeductionAmount, $netSettlementAmount,$product_courier_charges);
			}
			
			// ------------------------------------------------------------------------------------------------------------------
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
		/* 11 02 2020 */
		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}
		/* 11 02 2020 */
		
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
	 
		$data['sort_seller']    	= $this->url->link('vendor/commission_report', 'user_token=' . $this->session->data['user_token'] . '&sort=seller' . $url, true);
		$data['sort_name']  		= $this->url->link('vendor/commission_report', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_model']  		= $this->url->link('vendor/commission_report', 'user_token=' . $this->session->data['user_token'] . '&sort=model' . $url, true);
		$data['sort_qty']  		    = $this->url->link('vendor/commission_report', 'user_token=' . $this->session->data['user_token'] . '&sort=qty' . $url, true);
		$data['sort_price']  		= $this->url->link('vendor/commission_report', 'user_token=' . $this->session->data['user_token'] . '&sort=price' . $url, true);
		$data['sort_date']  		= $this->url->link('vendor/commission_report', 'user_token=' . $this->session->data['user_token'] . '&sort=date' . $url, true);
		$data['sort_percentage']  	= $this->url->link('vendor/commission_report', 'user_token=' . $this->session->data['user_token'] . '&sort=percentage' . $url, true);
		$data['sort_fixed']  	    = $this->url->link('vendor/commission_report', 'user_token=' . $this->session->data['user_token'] . '&sort=fixed' . $url, true);
		
		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		/* 11 02 2020 */
		if (isset($this->request->get['filter_vendor'])) {
			$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}
		/* 11 02 2020 */
		
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
		$pagination->url   	= $this->url->link('vendor/commission_report', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($report_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($report_total - $this->config->get('config_limit_admin'))) ? $report_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $report_total, ceil($report_total / $this->config->get('config_limit_admin')));
		
		$data['filter_name']	= $filter_name;
		$data['filter_vendor']	= $filter_vendor;
		$data['filter_from']	= $filter_from;
		$data['filter_to']		= $filter_to;

		$this->load->model('vendor/vendor');
		if(isset($data['filter_name'])) {
			$vendor_info = $this->model_vendor_vendor->getVendor($data['filter_name']);
		}
		/* 2020 vname */
		if(isset($vendor_info['vname'])) {
			$data['sellernme'] = $vendor_info['vname'];
		} else {
			$data['sellernme'] ='';
		}

		$data['sort']		= $sort;
		$data['order']		= $order;
				
		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');
		

		$this->response->setOutput($this->load->view('vendor/commission_report', $data));
	}
		
	// added changes reagdring to the reverse charges 26-04-2025
	public function updateReverseCourier() {
		$json = [];

		if (isset($this->request->post['order_id']) && isset($this->request->post['product_id']) && isset($this->request->post['reverse_courier'])) {
			$order_id = (int)$this->request->post['order_id'];
			$product_id = (int)$this->request->post['product_id'];
			$reverse_courier = (float)$this->request->post['reverse_courier'];

			$this->load->model('vendor/commission_report');

			$this->model_vendor_commission_report->updateReverseCourierCharge($order_id, $reverse_courier);

			$json['success'] = true;
		} else {
			$json['error'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	


    public function downloadSelectedExcel() {
        $this->load->model('vendor/commission');
        $this->load->model('vendor/commission_report');
        $this->load->model('vendor/vendor');
    
        if (!$this->user->hasPermission('modify', 'vendor/commission_report')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('vendor/commission_report', 'user_token=' . $this->session->data['user_token'], true));
        }
    
        if (!empty($this->request->post['selected'])) {
            $order_ids = array_map('intval', $this->request->post['selected']);
            $commissions = $this->model_vendor_commission_report->getCommissionsByOrderIds($order_ids);
    
            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment;filename="commission_report_' . date('Y-m-d_H-i-s') . '.xls"');
            header('Cache-Control: max-age=0');
    
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
    
            echo "<table border='1'>";
            echo "<tr>
                <th>Order ID</th><th>Seller Name</th><th>Product Name</th><th>Model</th><th>HSN Code</th><th>Quantity</th>
                <th>Price</th><th>Product Courier Charges</th><th>Status</th><th>Commission %</th><th>Platform Fees</th>
                <th>Forward Courier Charges</th><th>Reverse Courier Charges</th><th>RTO Charges</th>
                <th>Total Deduction</th><th>Net Settlement Amount</th><th>Courier Name</th><th>Payment Status</th><th>Payment Date</th>
                <th>Reference Number</th>
            </tr>";
    
            foreach ($commissions as $report) {
                $seller = $this->model_vendor_vendor->getVendor($report['vendor_id']);
                $firstname = isset($seller['firstname']) ? $seller['firstname'] : '';
                $lastname = isset($seller['lastname']) ? $seller['lastname'] : '';
                $sellername = trim($firstname . ' ' . $lastname);
    
                $currency_info = $this->model_vendor_commission_report->getOrderCurrency($report['order_id']);
                $currency = isset($currency_info['currency_code']) ? $currency_info['currency_code'] : $this->config->get('config_currency');
    
                $price1 = (!empty($report['tax'])) ? ($report['total'] + ($report['tax'] * $report['quantity'])) : $report['price'];
    
                $productPriceFivePercent = $price1 * 0.05;
    
                // Get courier charges
                $rates = $this->model_vendor_commission_report->getCourierRateByOrderId($report['order_id']);
                $courier_rate = isset($rates['courier_rate']) ? $rates['courier_rate'] : 0;
                $rto_charges = isset($rates['rto_charge']) ? $rates['rto_charge'] : 0;
                $reverse_courier_charges = isset($rates['reverse_courier_charge']) ? $rates['reverse_courier_charge'] : 0;
                $product_courier_charges = isset($rates['product_courier_charges']) ? $rates['product_courier_charges'] : 0;
                $courier_name = isset($rates['courier_name']) ? $rates['courier_name'] : '';
    
                // Payment details from report
                $payment_status = (strtolower(trim($report['payment_status'])) === 'paid') ? 'Paid' : 'Unpaid';
                $payment_date = isset($report['payment_date']) && $report['payment_date'] !== '0000-00-00'
                    ? date('d-m-Y', strtotime($report['payment_date']))
                    : '';
                $reference_number = isset($report['reference_number']) ? $report['reference_number'] : '';
    
                $totalDeductionAmount = $report['commissionper'] + $report['commissionfix'] + $courier_rate + $rto_charges + $reverse_courier_charges;
                $netSettlementAmount = $price1 - $totalDeductionAmount;
    
                $status_name = $this->model_vendor_commission_report->getStatusById($report['order_status_id']);
    
                echo "<tr>
                    <td>{$report['order_id']}</td>
                    <td>{$sellername}</td>
                    <td>{$report['name']}</td>
                    <td>{$report['model']}</td>
                    <td>{$report['hsn_code']}</td>
                    <td>{$report['quantity']}</td>
                    <td>" . $this->currency->format(round($price1), $currency) . "</td>
                    <td>" . $this->currency->format($product_courier_charges, $currency) . "</td>
                    <td>{$status_name}</td>
                    <td>{$report['commissionper']}%</td>
                    <td>0</td>
                    <td>" . $this->currency->format(round($courier_rate), $currency) . "</td>
                    <td>" . $this->currency->format($reverse_courier_charges, $currency) . "</td>
                    <td>" . $this->currency->format($rto_charges, $currency) . "</td>
                    <td>" . $this->currency->format(round($totalDeductionAmount), $currency) . "</td>
                    <td>" . $this->currency->format(round($netSettlementAmount), $currency) . "</td>
                    <td>{$courier_name}</td>
                    <td>{$payment_status}</td>
                    <td>{$payment_date}</td>
                    <td>{$report['reference_number']}</td>
                </tr>";
            }
    
            echo "</table>";
            exit;
        } else {
            echo "No orders selected.";
            exit;
        }
    }
    



}
?>
