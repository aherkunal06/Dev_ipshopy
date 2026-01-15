<?php
class ControllerVendorMybalance extends Controller {
	public function index() {
		$this->load->language('vendor/mybalance');
		$this->load->model('vendor/vendor');
		$this->load->model('vendor/mybalance');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');
		$data['balance'] = $this->url->link('vendor/income');

		$filter_data=array(
			'vendor_id' 	=> $this->vendor->getId(),
			
		);

		
		$data['total'] = $this->model_vendor_mybalance->getVendorTotal($filter_data);
		$data['totalcommission'] = $this->model_vendor_mybalance->getTotalAmount($filter_data);		
		$data['totalamount'] = $data['total'];
		$data['payamount'] = $this->model_vendor_mybalance->getAmount($filter_data);		
		$seller_info = $this->model_vendor_mybalance->getVendorOrder($this->vendor->getId());
		
		/*############13 02 2021 Remove code################*/
		if(!empty($seller_info['tmdshippingcost'])){
			$tmdshippingcost = $seller_info['tmdshippingcost'];
		} else{
			$tmdshippingcost =0;
		}
		
		
		/* update commission */
		$totalcommissions_info = $this->model_vendor_mybalance->getTotalCommissionamount($filter_data,$this->vendor->getId());
		if(!empty($totalcommissions_info)){
			$totalcommissions = $totalcommissions_info;
		} else {
			$totalcommissions =0;	
		}
		
		/*############13 02 2021 Remove code################*/
		$remaining_amounts = $data['totalamount']-$data['payamount']+$tmdshippingcost-$totalcommissions;
		
		/* update commission */
		
		 $data['remaining_amount'] = $this->currency->format($remaining_amounts,$this->session->data['currency']);
		
		return $this->load->view('vendor/mybalance', $data);
	}
}
