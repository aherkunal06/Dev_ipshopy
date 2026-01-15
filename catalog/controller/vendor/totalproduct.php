<?php
class ControllerVendorTotalProduct extends Controller {
	public function index() {
		$this->load->language('vendor/totalproduct');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');
		
		$data['totalproducts'] = $this->model_vendor_vendor->getTotalProducts($this->vendor->getId());
		/* update */
		$data['prohref'] = $this->url->link('vendor/product');
		/* update */
		
		
		return $this->load->view('vendor/totalproduct', $data);
	}
	
}
