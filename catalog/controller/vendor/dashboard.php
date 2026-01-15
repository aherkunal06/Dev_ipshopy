<?php
class ControllerVendorDashboard extends Controller {
	private $error = array();

	public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}
		/* 09 11 2019 */
		$vendorfolder=DIR_IMAGE . 'catalog/multivendor/'.$this->vendor->getId();
		if (!file_exists($vendorfolder)) {
		@mkdir($vendorfolder, 0777);
		}		
		/* 09 11 2019 */
		
		$this->load->language('vendor/dashboard');
		$this->load->model('vendor/vendor');
		$this->load->model('vendor/notification');
		
		$this->document->setTitle($this->language->get('heading_title1'));
				
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/dashboard', '', true)
		);
				
		$data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('account/login', '', true));
		$data['heading_title'] 		= $this->language->get('heading_title');
		$data['text_yes'] 			= $this->language->get('text_yes');
		$data['text_no'] 			= $this->language->get('text_no');
		$data['text_select'] 		= $this->language->get('text_select');
		$data['text_none'] 			= $this->language->get('text_none');
		$data['text_loading'] 		= $this->language->get('text_loading');
		
		$data['button_continue'] 	= $this->language->get('button_continue');
		$data['button_upload'] 		= $this->language->get('button_upload');
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		$data['total'] = $this->load->controller('vendor/total');
		$data['totalshippedorders'] = $this->load->controller('vendor/totalshippedorders');
		$data['totalorder'] = $this->load->controller('vendor/totalorder');
		$data['totalproduct'] = $this->load->controller('vendor/totalproduct');
		$data['latestorder'] = $this->load->controller('vendor/latestorder');
		$data['todays_order'] = $this->load->controller('vendor/todaysorder');
		$data['totalcancelledorder'] = $this->load->controller('vendor/totalcancelledorder');
		$data['totalcompletedorder'] = $this->load->controller('vendor/totalcompletedorder');
        // added on 10-03-2025
        $data['totaloutfordeliveryorders'] = $this->load->controller('vendor/totaloutfordeliveryorders');
		$data['totaldeliveredorders'] = $this->load->controller('vendor/totaldeliveredorders');
		$data['totalundeliveredorders'] = $this->load->controller('vendor/totalundeliveredorders');
		$data['totalrtointransitorders'] = $this->load->controller('vendor/total_rtointransit_orders');
		$data['totalrtodeliveredorders'] = $this->load->controller('vendor/total_rtodelivered_orders');
		$data['totalintransitorders'] = $this->load->controller('vendor/total_intransit_orders');
		$data['totallostshipmentorders'] = $this->load->controller('vendor/totallostshipmentorders');
		$data['totalreturnorders'] = $this->load->controller('vendor/totalreturnorders');
		$data['totalbreachedorders'] = $this->load->controller('vendor/totalbreachedorders');
		$data['totalpickupscheduledorders'] = $this->load->controller('vendor/totalpickupscheduled');

		
		// $data['order_total_all'] = $this->url->link('vendor/all_order','',true);
		$data['viewallorder'] = $this->url->link('vendor/all_order','',true);
		$data['orders'] = $this->url->link('vendor/todays_order', '',true);	
		$data['viewcancelledorder'] = $this->url->link('vendor/cancelled_orders', '', true);	
        $data['viewcompleteorder'] = $this->url->link('vendor/complete_orders', '', true);	
		$data['viewprocessingorder'] = $this->url->link('vendor/processing_orders', '', true);
		$data['viewshippedorder'] = $this->url->link('vendor/shipped_orders', '', true);
		$data['viewpickup_scheduledorder'] = $this->url->link('vendor/pickup_scheduled_orders', '', true);
		$data['viewin_transitorder'] = $this->url->link('vendor/in_transit_orders', '', true); 
		$data['view_out_for_delivery_orders'] = $this->url->link('vendor/out_for_delivery_orders', '', true);  
		$data['view_delivered_orders'] = $this->url->link('vendor/delivered_orders', '', true); 
		$data['view_undelivered_orders'] = $this->url->link('vendor/undelivered_orders', '', true);   
		$data['view_rto_delivered_orders'] = $this->url->link('vendor/rto_delivered_orders', '', true); 
		$data['view_breached_orders'] = $this->url->link('vendor/breached_orders', '', true); 
		$data['view_return_orders'] = $this->url->link('vendor/return_orders', '', true); 
		$data['view_rto_in_transit_orders'] = $this->url->link('vendor/rto_in_transit_orders', '', true); 
		$data['view_lost_shipments_orders'] = $this->url->link('vendor/lost_shipments_orders', '', true); 

		$data['totalcompleteordersale'] = $this->load->controller('vendor/totalcompleteordersale');
		$data['product'] = $this->url->link('vendor/product', '',true);


		// 09 06 2018 ///
		$data['mybalance'] = $this->load->controller('vendor/mybalance');
		// 09 06 2018 ///
		
		 $filter_data = array(
            'vendor_id' => $this->vendor->getId()
        );
        
        
   $data['vendor_title_name'] =  $this->model_vendor_vendor->getVendorFirstName($filter_data);
      
/// Seller Notification Start //		
		$data['sellernotifi']=array();
		$sellernotifis = $this->model_vendor_notification->getSellerMessages();
	
		foreach($sellernotifis as $sellernotifi){
			$data['sellernotifi'][]=array(
				'notification_id' => $sellernotifi['notification_id'],
				'message' 		  => html_entity_decode($sellernotifi['message']),
			);
			
		}
	
				
		$data['notifications']=array();
		$notinfos = $this->model_vendor_notification->getSellerNotification();
		foreach($notinfos as $notinfo){
			$data['notifications'][]=array(
				'notification_id' => $notinfo['notification_id'],
				'message' 		  => html_entity_decode($notinfo['message']),
			);
		}
/// Seller Notification End //	
		
		$data['column_left'] 	= $this->load->controller('vendor/column_left');
		$data['footer'] 		= $this->load->controller('vendor/footer');
		$data['header'] 		= $this->load->controller('vendor/header');
		
		
		$this->response->setOutput($this->load->view('vendor/dashboard', $data));
	}
		
}
